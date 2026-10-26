"""Fonds « photographiques » procéduraux façon film de référence, aux couleurs DigiCraft :
montagnes au lever du jour, collines dans la brume, mer de nuages, horizon à l'aube, flous photo pour la rafale de demandes."""
import os
import numpy as np
from PIL import Image
from scipy.ndimage import zoom, gaussian_filter

OUT = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'assets', 'img', 'b46')
os.makedirs(OUT, exist_ok=True)
W, H = 2304, 1296
rng = np.random.default_rng(7)


def hexc(h):
    h = h.lstrip('#')
    return np.array([int(h[i:i + 2], 16) for i in (0, 2, 4)], float) / 255


def fbm2(h, w, base=4, oct=6, pers=0.52, seed=0):
    r = np.random.default_rng(seed)
    out = np.zeros((h, w)); amp = 1; tot = 0
    for o in range(oct):
        gh, gw = base * 2 ** o + 2, int(base * 2 ** o * w / h) + 2
        g = r.random((gh, gw))
        z = zoom(g, (h / (gh - 1) * 1.02, w / (gw - 1) * 1.02), order=3)[:h, :w]
        out += amp * z; tot += amp; amp *= pers
    out /= tot
    return (out - out.min()) / (out.max() - out.min())


def fbm1(n, base=3, oct=7, pers=0.55, seed=0):
    r = np.random.default_rng(seed)
    out = np.zeros(n); amp = 1; tot = 0
    for o in range(oct):
        k = base * 2 ** o + 2
        g = r.random(k)
        out += amp * np.interp(np.linspace(0, k - 1, n), np.arange(k), g); tot += amp; amp *= pers
    return out / tot


def lerp(a, b, t):
    return a + (b - a) * t


def sky(top, mid, hor, h_hor=0.6, sun=(0.62, 0.58), sun_col='#FFE9DC', sun_r=0.35, sun_a=0.9):
    y = np.linspace(0, 1, H)[:, None, None]
    t1 = np.clip(y / h_hor, 0, 1)
    t1 = np.clip(t1, 0, 1)
    c = np.where(t1 < 0.55, lerp(hexc(top), hexc(mid), (t1 / 0.55) ** 1.2), lerp(hexc(mid), hexc(hor), np.clip((t1 - 0.55) / 0.45, 0, 1) ** 0.9))
    img = np.broadcast_to(c, (H, W, 3)).copy()
    yy, xx = np.mgrid[0:H, 0:W]
    d = np.sqrt(((xx / W - sun[0]) * 1.6) ** 2 + (yy / H - sun[1]) ** 2)
    g = np.exp(-(d / sun_r) ** 2)[..., None]
    img = img + (hexc(sun_col) - img) * g * sun_a
    core = np.exp(-(d / (sun_r * 0.12)) ** 2)[..., None]
    return img + (1 - img) * core * 0.9, d


def finish(img, name, blur=0.6, grain=0.012, vig=0.22):
    img = gaussian_filter(img, (blur, blur, 0))
    yy, xx = np.mgrid[0:H, 0:W]
    v = ((xx / W - 0.5) ** 2 + (yy / H - 0.5) ** 2) ** 0.5
    img = img * (1 - vig * np.clip(v / 0.7, 0, 1) ** 2)[..., None]
    img = img + rng.normal(0, grain, img.shape)
    Image.fromarray((np.clip(img, 0, 1) * 255).astype(np.uint8)).save(os.path.join(OUT, name), quality=92)
    print(name)


def ridges(img, layers, d_sun, sun_x=0.62, fog='#C9B8FF', rim='#FFE3D3'):
    yy = np.arange(H)[:, None]
    x = np.arange(W)
    tex = fbm2(H, W, base=6, oct=6, seed=3)
    for i, (y0, amp, base, col, fa, seed) in enumerate(layers):
        r = fbm1(W, base=base, seed=seed)
        r2 = fbm1(W, base=base * 4, oct=4, seed=seed + 50) * 0.07
        ry = H * y0 - amp * H * (r + r2)
        mask = yy >= ry[None, :]
        depth = np.clip((yy - ry[None, :]) / (H * 0.25), 0, 1)
        slope = np.gradient(ry)
        light = np.clip(-slope * 0.06 * (1 if True else 0), -1, 1)
        c = hexc(col)[None, None, :] * (0.85 + 0.25 * tex[..., None])
        # Liseré lumineux sur les crêtes, plus fort près du soleil.
        near = np.exp(-((x / W - sun_x) / 0.35) ** 2)[None, :, None]
        edge = np.exp(-((yy - ry[None, :]) / (H * 0.02)) ** 2)[..., None] * np.clip((yy - ry[None, :]) / 3 + 1, 0, 1)[..., None]
        c = c + (hexc(rim) - c) * edge * (0.08 + 0.22 * near) * (0.5 + 0.5 * np.clip(light[None, :, None] + 0.5, 0, 1))
        c = c * (1 - 0.25 * depth[..., None]) + hexc(fog) * 0.0
        img = np.where(mask[..., None], c, img)
        # Brume au pied de chaque couche.
        fogm = np.exp(-((yy - (ry[None, :] + H * 0.09)) / (H * 0.07)) ** 2)[..., None]
        img = img + (hexc(fog) - img) * fogm * fa * (0.6 + 0.4 * fbm2(H, W, base=3, oct=4, seed=seed + 9)[..., None])
    return img


# 1. Montagnes au lever du jour (bleu nuit, lilas, pêche).
img, d = sky('#0B1E3F', '#5B57C9', '#F4C7D8', h_hor=0.62, sun=(0.64, 0.52), sun_r=0.42)
img = ridges(img, [
    (0.60, 0.20, 3, '#8C86D8', 0.55, 11), (0.68, 0.22, 3, '#5D5BB8', 0.5, 12), (0.76, 0.22, 4, '#3A3F95', 0.45, 13),
    (0.86, 0.24, 4, '#232A6B', 0.35, 14), (0.98, 0.26, 5, '#121A45', 0.2, 15)], d, sun_x=0.64)
finish(img, 'mountains_dawn.jpg')

# 2. Collines dans la brume (sarcelle et bleu glacier).
img, d = sky('#0E3550', '#3D86A8', '#D6EEF5', h_hor=0.6, sun=(0.3, 0.45), sun_col='#F2FBFF', sun_r=0.5, sun_a=0.75)
img = ridges(img, [
    (0.62, 0.10, 2, '#7FB8C9', 0.7, 21), (0.70, 0.12, 2, '#4E95A8', 0.65, 22), (0.79, 0.13, 2, '#2E7488', 0.6, 23),
    (0.89, 0.14, 3, '#1A5466', 0.5, 24), (1.0, 0.15, 3, '#0D3644', 0.3, 25)], d, sun_x=0.3, fog='#E3F4F8', rim='#F2FBFF')
finish(img, 'hills_mist.jpg')

# 3. Mer de nuages (violet, bleu, aube chaude).
img, d = sky('#1A2766', '#7A6BE0', '#FFD9C7', h_hor=0.5, sun=(0.5, 0.47), sun_col='#FFF1E6', sun_r=0.38)
hz = int(H * 0.5)
from scipy.ndimage import map_coordinates
yy, xx = np.mgrid[0:H, 0:W]
noise = gaussian_filter(fbm2(1400, 2800, base=4, oct=6, seed=31), 1.5)
dep = np.clip((yy - hz) / (H - hz), 0, 1)
z = 1 / (dep + 0.15)
rows = z * 190
cols = 1400 + (xx - W / 2) / W * z * 400
dens = map_coordinates(noise, [rows.ravel(), cols.ravel()], order=1, mode='reflect').reshape(H, W)
dens = gaussian_filter(dens, 1.2)
cl = np.clip((dens - 0.40) / 0.22, 0, 1)
lit = lerp(hexc('#4B48B4'), hexc('#FFE6DA'), np.clip(dens[..., None] * 1.5 - 0.35, 0, 1))
under = lerp(hexc('#5551B8'), hexc('#2A2F80'), dep[..., None] ** 0.7)
below = under + (lit - under) * cl[..., None]
hzfog = np.exp(-((yy - hz) / (H * 0.09)) ** 2)[..., None]
below = below + (hexc('#FFE9DE') - below) * hzfog * 0.75
img = np.where((yy > hz)[..., None], below, img)
img = img + (hexc('#FFE9DE') - img) * np.exp(-((yy - hz) / (H * 0.03)) ** 2)[..., None] * 0.6
finish(img, 'clouds_sea.jpg', blur=1.0)

import sys
if 'all' not in sys.argv: raise SystemExit
# 4. Horizon à l'aube (pour « Just by describing it »).
img, d = sky('#071833', '#2F3E9E', '#C9B8FF', h_hor=0.85, sun=(0.5, 0.95), sun_col='#FFF0E8', sun_r=0.55, sun_a=1.0)
streak = fbm2(H, W, base=2, oct=5, seed=41)
streak = gaussian_filter(streak, (2, 60))
yy = np.arange(H)[:, None]
band = np.exp(-((yy / H - 0.72) / 0.12) ** 2)
img = img + (hexc('#FFD9E6') - img) * (np.clip(streak - 0.5, 0, 1) * 1.6 * band)[..., None]
finish(img, 'dawn_horizon.jpg', blur=1.2)

# 5. Flous « photo » pour la rafale de demandes.
PAL = {
    'blur_water': ['#062C45', '#0F6E8C', '#5FC6E0', '#E6F7FB'],
    'blur_lavender': ['#2A1C5E', '#6D5DF6', '#C9A7F5', '#FBE7F3'],
    'blur_forest': ['#04252A', '#0B5F5C', '#3FB59A', '#D8F5E6'],
    'blur_dusk': ['#0B1638', '#3B3A9A', '#E89BB8', '#FFE7D6'],
    'blur_sky': ['#0A3358', '#2E8BC0', '#9FD3F0', '#FFFFFF'],
}
for k, (name, cols) in enumerate(PAL.items()):
    f = fbm2(H, W, base=2, oct=5, seed=60 + k)
    f = gaussian_filter(f, 30)
    f = (f - f.min()) / (f.max() - f.min())
    stops = np.array([hexc(c) for c in cols])
    t = f * (len(cols) - 1)
    i0 = np.clip(t.astype(int), 0, len(cols) - 2)
    img = lerp(stops[i0], stops[i0 + 1], (t - i0)[..., None])
    r = np.random.default_rng(90 + k)
    yy, xx = np.mgrid[0:H, 0:W]
    for _ in range(18):
        cx, cy, rr = r.random() * W, r.random() * H, 30 + r.random() * 110
        disc = np.clip(1 - np.sqrt((xx - cx) ** 2 + (yy - cy) ** 2) / rr, 0, 1) ** 0.4
        img = img + (hexc(cols[3]) - img) * (disc * (0.15 + r.random() * 0.3))[..., None]
    img = gaussian_filter(img, (14, 14, 0))
    finish(img, name + '.jpg', blur=0.5, vig=0.3)
