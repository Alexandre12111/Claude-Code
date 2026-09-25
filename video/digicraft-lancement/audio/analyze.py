import sys, numpy as np, soundfile as sf
from scipy import signal
from PIL import Image, ImageDraw
x,sr=sf.read(sys.argv[1]); m=x.mean(1) if x.ndim>1 else x
f,t,S=signal.spectrogram(m,sr,nperseg=4096,noverlap=3072)
S=10*np.log10(S+1e-12); fm=f<=12000; S=S[fm][::-1]
S=np.clip((S-S.max()+90)/90,0,1)
W,H=1600,500
img=Image.fromarray((S*255).astype(np.uint8)).resize((W,H))
img=img.convert('RGB'); d=ImageDraw.Draw(img)
# RMS envelope
hop=sr//20; rms=[20*np.log10(np.sqrt(np.mean(m[i:i+hop]**2))+1e-9) for i in range(0,len(m)-hop,hop)]
for i in range(len(rms)-1):
    x0=i*W/len(rms); x1=(i+1)*W/len(rms)
    y0=H-(rms[i]+60)/60*H; y1=H-(rms[i+1]+60)/60*H
    d.line([x0,y0,x1,y1],fill=(255,80,80),width=2)
dur=len(m)/sr
for s in range(0,int(dur),5):
    xx=s*W/dur; d.line([xx,0,xx,10],fill=(255,255,0)); d.text((xx+2,12),str(s),fill=(255,255,0))
img.save(sys.argv[2])
print('peak',np.abs(x).max(),'rms dB',20*np.log10(np.sqrt(np.mean(m**2))))
