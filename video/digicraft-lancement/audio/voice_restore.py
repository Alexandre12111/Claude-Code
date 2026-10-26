"""Restauration de la voix client : prise AAC 16 kHz 26 kbit/s -> rendu type micro studio 48 kHz.

1. Débruitage IA DPDFNet 48 kHz (sherpa-onnx).
2. Égalisation par correspondance vers une courbe de voix off pro (LTASS de Byrne + présence).
3. Reconstruction des aigus 8-16 kHz par réplication spectrale (principe SBR du HE-AAC).
4. De-esser, compression en deux étages, limiteur.
Usage : python3 voice_restore.py source.m4a sortie.wav [dossier_modeles]
"""
import os, subprocess, sys
import numpy as np
import soundfile as sf
from scipy import signal
from scipy.ndimage import uniform_filter1d, minimum_filter1d

SR = 48000
SRC, OUT = sys.argv[1], sys.argv[2]
MODELS = sys.argv[3] if len(sys.argv) > 3 else os.path.expanduser('~/enh_models')


def decode(path):
    tmp = OUT + '.src.wav'
    subprocess.run(['ffmpeg', '-y', '-v', 'error', '-i', path, '-ac', '1', '-ar', str(SR), '-c:a', 'pcm_f32le', tmp], check=True)
    x, _ = sf.read(tmp, dtype='float32')
    os.remove(tmp)
    return x


def denoise(x):
    import sherpa_onnx
    mc = sherpa_onnx.OfflineSpeechDenoiserModelConfig(
        dpdfnet=sherpa_onnx.OfflineSpeechDenoiserDpdfNetModelConfig(model=os.path.join(MODELS, 'dpdfnet2_48khz_hr.onnx')), num_threads=4)
    d = sherpa_onnx.OfflineSpeechDenoiser(sherpa_onnx.OfflineSpeechDenoiserConfig(model=mc))
    r = d(x, SR)
    y = np.zeros_like(x)
    a = np.asarray(r.samples, dtype=np.float64)
    y[:min(len(x), len(a))] = a[:len(x)]
    return y


def speech_mask(x, thr_db=-40):
    env = uniform_filter1d(np.abs(x), int(0.03 * SR))
    return env > np.max(env) * 10 ** (thr_db / 20)


def match_eq(x):
    """FIR à phase linéaire : spectre moyen de la parole -> courbe cible voix off (±dB bornés)."""
    m = speech_mask(x)
    f, p = signal.welch(x[m], SR, nperseg=8192)
    lt = 10 * np.log10(p + 1e-20)
    # Courbe cible (dB relatifs) : LTASS masculine, légère présence 3-5 kHz, air doux au dessus de 10 kHz.
    pts_f = [60, 120, 250, 500, 1000, 2000, 3500, 5000, 7000, 10000, 14000, 20000]
    pts_d = [-8, -2, 0, -1.5, -6.5, -11, -12.5, -14, -17, -21, -27, -40]
    tgt = np.interp(np.log10(np.maximum(f, 20)), np.log10(pts_f), pts_d)
    band = (f > 150) & (f < 600)
    ref = np.mean(lt[band] - tgt[band])
    corr = tgt + ref - lt
    # Lissage en 1/3 d'octave et bornes : pas d'EQ au dessus de 7,6 kHz (zone reconstruite ensuite).
    lf = np.log2(np.maximum(f, 20))
    sm = np.array([corr[np.abs(lf - v) < 1 / 6].mean() for v in lf])
    sm = np.clip(sm, -6, np.where(f > 3000, 13, 8))
    sm[f > 7600] = sm[np.searchsorted(f, 7600)]
    sm[f < 60] = np.minimum(sm[f < 60], 0)
    h = signal.firwin2(2049, f / (SR / 2), 10 ** (sm / 20))
    return signal.fftconvolve(x, h, mode='same'), f, sm


def bandwidth_extend(x):
    """Réplique 3,6-7,2 kHz en trois patchs (7,2 / 10,8 / 14,4 kHz) avec fondus croisés, enveloppe par trame."""
    n, hop = 2048, 256
    win = signal.windows.hann(n, sym=False)
    fq, tt, Z = signal.stft(x, SR, window=win, nperseg=n, noverlap=n - hop)
    df = fq[1]
    k = lambda hz: int(round(hz / df))
    s0, s1 = k(3600), k(7200)
    width = s1 - s0
    ov = k(500)
    src = Z[s0 - ov:s1]
    ref = np.sqrt(np.mean(np.abs(Z[k(5000):k(7000)]) ** 2, axis=0))
    out = np.zeros_like(Z)
    for patch, (g0, g1) in enumerate(((-5.0, -9.0), (-10.0, -16.0), (-17.0, -26.0))):
        a0 = s1 + patch * width - ov
        a1 = min(a0 + width + ov, Z.shape[0])
        seg = src[: a1 - a0]
        norm = np.sqrt(np.mean(np.abs(seg[ov:]) ** 2, axis=0)) + 1e-12
        tilt = 10 ** (np.linspace(g0, g1, a1 - a0) / 20)[:, None]
        # fondu en cosinus sur le recouvrement bas du patch
        fade = np.ones(a1 - a0)
        fade[:ov] = np.sin(np.linspace(0, np.pi / 2, ov)) ** 2
        out[a0:a1] += seg / norm * ref * tilt * fade[:, None]
    # le signal d'origine s'éteint vers 7,5 kHz : on lui laisse la place en dessous et on raccorde en fondu
    w = np.clip((fq - 6700) / 500, 0, 1) * np.clip((18500 - fq) / 3000, 0, 1)
    out *= w[:, None]
    Z2 = Z * np.clip((7700 - fq) / 500, 0, 1)[:, None] + out
    fr_db = 20 * np.log10(np.sqrt(np.mean(np.abs(Z) ** 2, axis=0)) + 1e-12)
    gate = np.clip((fr_db - (fr_db.max() - 45)) / 10, 0, 1)
    Z2[k(7000):] *= uniform_filter1d(gate, 5)[None, :]
    _, y = signal.istft(Z2, SR, window=win, nperseg=n, noverlap=n - hop)
    y = y[: len(x)]
    return np.pad(y, (0, len(x) - len(y)))


def dyn_gain(x, thr_db, ratio, att, rel, band=None, dec=48):
    s = x if band is None else signal.sosfiltfilt(signal.butter(4, band, 'bandpass', fs=SR, output='sos'), x)
    env = np.sqrt(uniform_filter1d(s ** 2, dec))[::dec]
    # suiveur sur l'enveloppe décimée (sr/dec)
    sr_d = SR / dec
    ca, cr = np.exp(-1 / (att * sr_d)), np.exp(-1 / (rel * sr_d))
    e = np.empty_like(env); v = 0.0
    for i in range(len(env)):
        c = ca if env[i] > v else cr
        v = c * v + (1 - c) * env[i]
        e[i] = v
    ed = 20 * np.log10(e + 1e-9)
    over = np.maximum(ed - thr_db, 0)
    g = -over * (1 - 1 / ratio)
    g = np.interp(np.arange(len(x)), np.arange(len(g)) * dec + dec / 2, g)
    return 10 ** (g / 20)


def de_ess(x):
    sib = signal.sosfiltfilt(signal.butter(4, [5000, 11000], 'bandpass', fs=SR, output='sos'), x)
    full = uniform_filter1d(np.abs(x), 480) + 1e-9
    ratio = uniform_filter1d(np.abs(sib), 480) / full
    red = np.clip((ratio - 0.5) / 0.4, 0, 1) * 0.5  # jusqu'à -8 dB sur la bande sifflante
    red = uniform_filter1d(red, 240)
    return x - sib * red


def process(x):
    x = denoise(x)
    x = signal.sosfiltfilt(signal.butter(4, 70, 'high', fs=SR, output='sos'), x)
    x, f, curve = match_eq(x)
    x = bandwidth_extend(x)
    x = de_ess(x)
    rms = lambda v: np.sqrt(np.mean(v[speech_mask(v)] ** 2))
    x = x / rms(x) * 0.1
    # étage 1 : compresseur rapide 3:1 (contrôle des pics), étage 2 : nivellement lent 2:1
    x = x * dyn_gain(x, -24, 3.0, 0.005, 0.08)
    x = x * dyn_gain(x, -26, 2.0, 0.05, 0.4)
    x = x / rms(x) * 0.12
    # limiteur à anticipation (crête -1 dBFS)
    ceil = 10 ** (-1 / 20)
    look = int(0.003 * SR)
    g = np.minimum(1, ceil / np.maximum(np.abs(x), 1e-9))
    g = uniform_filter1d(minimum_filter1d(g, 2 * look + 1), 2 * look + 1)
    return x * g, f, curve


if __name__ == '__main__':
    x = decode(SRC)
    y, f, curve = process(x)
    sf.write(OUT, y.astype(np.float32), SR, subtype='FLOAT')
    for hz in (100, 200, 400, 1000, 2000, 3000, 5000, 7000):
        print(f'EQ {hz} Hz : {curve[np.searchsorted(f, hz)]:+.1f} dB')
    print('ok', OUT, round(len(y) / SR, 2), 's')
