import json, subprocess, numpy as np
from scipy.signal import butter, sosfilt, sosfiltfilt
SR=48000; TL=json.load(open('timeline.json')); N=int(TL['total']*SR); VOICE='fr-FR-RemyMultilingualNeural'
rng=np.random.default_rng(7); t=np.arange(N)/SR
def dec(f):
    raw=subprocess.check_output(['ffmpeg','-v','0','-i',f,'-f','f32le','-ac','1','-ar',str(SR),'-']); return np.frombuffer(raw,np.float32).astype(np.float64)
# ---- voice
voice=np.zeros(N)
for i,sc in enumerate(TL['scenes']):
    v=dec(f'audio/{VOICE}_{i}.mp3'); s=int(sc['start']*SR); e=min(N,s+len(v)); voice[s:e]+=v[:e-s]
voice/=np.max(np.abs(voice))+1e-9; voice*=0.89
# light voice EQ: gentle low cut + presence
voice=sosfilt(butter(2,90,'hp',fs=SR,output='sos'),voice)
pres=sosfilt(butter(2,[2500,6000],'bp',fs=SR,output='sos'),voice); voice=voice+0.18*pres
# ---- music (I–V–vi–IV in D major, 96 bpm)
bpm=96; beat=60/bpm; bar=4*beat
def hz(m): return 440*2**((m-69)/12)
prog=[[62,66,69],[57,61,64],[59,62,66],[55,59,62]]  # D, A, Bm, G
bass=[38,33,35,31]
music=np.zeros(N)
def env(n,a,r):
    e=np.ones(n); ai=int(a*SR); ri=int(r*SR)
    if ai: e[:ai]=np.linspace(0,1,ai)
    if ri: e[-ri:]*=np.linspace(1,0,ri)
    return e
def add(sig,start):
    s=int(start*SR); e=min(N,s+len(sig))
    if s<N: music[s:e]+=sig[:e-s]
end_t=TL['total']; resolve=TL['scenes'][5]['start']+3.9
nb=int(np.ceil(resolve/bar))
for b in range(nb):
    t0=b*bar; ch=prog[b%4]; dur=bar+0.6; n=int(dur*SR); tt=np.arange(n)/SR
    pad=sum(sum(a*np.sin(2*np.pi*hz(m)*k*tt*(1+d)) for k,a in [(1,1),(2,.32),(3,.12)]) for m in ch+[ch[0]+12] for d in (-.0015,.0015))
    add(pad*env(n,.6,.9)*0.010,t0)
    bt=np.arange(int(1.4*SR))/SR
    for q in range(4):  # soft bass pulse
        add(np.sin(2*np.pi*hz(bass[b%4])*bt)*np.exp(-bt*3.2)*0.05*(1 if q%2==0 else .6),t0+q*beat)
    arp=[ch[0]+12,ch[1]+12,ch[2]+12,ch[0]+24,ch[2]+12,ch[1]+12,ch[0]+24,ch[2]+12]
    pt=np.arange(int(.9*SR))/SR
    for k,m in enumerate(arp):  # pluck 8ths
        f=hz(m); pl=(np.sin(2*np.pi*f*pt)+.25*np.sin(4*np.pi*f*pt))*np.exp(-pt*7)
        add(pl*0.022*(1.0 if k%2==0 else .7),t0+k*beat/2)
    kt=np.arange(int(.35*SR))/SR
    for q in (0,2):
        f=50+70*np.exp(-kt*28); add(np.sin(2*np.pi*np.cumsum(f)/SR)*np.exp(-kt*11)*0.10,t0+q*beat)
    ht=np.arange(int(.08*SR))/SR
    for q in range(8):
        hn=sosfilt(butter(2,7000,'hp',fs=SR,output='sos'),rng.standard_normal(len(ht)))*np.exp(-ht*60)
        add(hn*(0.010 if q%2 else 0.006),t0+q*beat/2+0.0)
# final resolve chord (D add9) under the end card
n=int((end_t-resolve+.2)*SR); tt=np.arange(n)/SR
fin=sum(np.sin(2*np.pi*hz(m)*tt)*a for m,a in [(50,1),(62,.8),(66,.6),(69,.6),(76,.35)])
add(fin*env(n,.05,1.2)*np.exp(-tt*.35)*0.05,resolve)
# chime on logo
ct=np.arange(int(2.5*SR))/SR
chime=sum(np.sin(2*np.pi*f*ct)*a*np.exp(-ct*d) for f,a,d in [(1174.7,1,2.2),(1760,.5,3),(2349,.25,4)])
add(chime*0.05,resolve+.05)
music*=np.clip(t/1.2,0,1)*np.clip((end_t-t)/1.0,0,1)
# ducking under voice
ve=np.abs(voice); k=int(.12*SR); ve=np.convolve(ve,np.ones(k)/k,'same'); ve=ve/ (ve.max()+1e-9)
duck=1-0.55*np.clip(ve*6,0,1); duck=np.convolve(duck,np.ones(int(.25*SR))/int(.25*SR),'same')
music*=duck
# ---- pen scratch
pen=json.load(open('pen.json')); fps=TL['fps']
sp=np.array([p['speed'] if p['draw'] else 0 for p in pen]); dr=np.array([1.0 if p['draw'] else 0 for p in pen])
amp=np.interp(t,np.arange(len(sp))/fps,np.clip(sp/28,0,1)*.7+dr*.3)
amp=np.convolve(amp,np.ones(int(.02*SR))/int(.02*SR),'same')
grain=np.interp(t,np.arange(0,TL['total'],.012),0.55+0.45*rng.random(int(np.ceil(TL['total']/.012))))
noise=rng.standard_normal(N)
scr=sosfilt(butter(2,[1800,5200],'bp',fs=SR,output='sos'),noise)+0.35*sosfilt(butter(2,[600,1400],'bp',fs=SR,output='sos'),noise)
scratch=scr*amp*grain*0.045
# ---- whooshes on camera moves
wh=np.zeros(N)
def whoosh(c,d,g):
    s=int((c-d/2)*SR); n=int(d*SR); x=rng.standard_normal(n); out=np.zeros(n); seg=n//24
    for j in range(24):
        fc=500+3500*np.sin(np.pi*(j+.5)/24); a=j*seg; b=n if j==23 else (j+1)*seg
        out[a:b]=sosfilt(butter(2,[fc*.6,fc*1.4],'bp',fs=SR,output='sos'),x)[a:b]
    out*=np.sin(np.pi*np.arange(n)/n)**2*g
    e=min(N,s+n); wh[s:e]+=out[:e-s]
for k in range(1,6): whoosh(TL['scenes'][k]['start']-.08,.8,.05)
whoosh(TL['scenes'][5]['start']+3.95,1.0,.045)
mix=voice+music+scratch+wh
mix/=np.max(np.abs(mix))*1.05
import wave
pcm=(np.clip(mix,-1,1)*32767).astype(np.int16)
with wave.open('mix.wav','wb') as w: w.setnchannels(1); w.setsampwidth(2); w.setframerate(SR); w.writeframes(pcm.tobytes())
print('ok', len(pcm)/SR)
