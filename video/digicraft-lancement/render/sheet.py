import sys, glob
from PIL import Image, ImageDraw
fs=sorted(glob.glob(sys.argv[1]+'/*.png')); out=sys.argv[2]; cols=int(sys.argv[3]) if len(sys.argv)>3 else 3
w,h=640,360
rows=(len(fs)+cols-1)//cols
sh=Image.new('RGB',(cols*w,rows*(h+4)),(40,40,40)); d=ImageDraw.Draw(sh)
for i,f in enumerate(fs):
    im=Image.open(f).convert('RGB').resize((w,h),Image.LANCZOS)
    x=(i%cols)*w; y=(i//cols)*(h+4); sh.paste(im,(x,y)); d.rectangle([x,y,x+80,y+16],fill=(0,0,0)); d.text((x+3,y+2),f.split('t_')[-1][:-4],fill=(255,255,0))
sh.save(out)
