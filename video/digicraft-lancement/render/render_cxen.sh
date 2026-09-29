#!/bin/bash
# Rendu version anglaise : 120 i/s puis fusion de 2 images -> 60 i/s avec flou de mouvement.
cd "$(dirname "$0")"
export NODE_PATH=/opt/node22/lib/node_modules
mkdir -p ../out/parts_cxen
B=(0 14.55 29.1 43.65 58.25)
for i in 0 1 2 3; do
  node render.cjs --page src7/index.html --video --from ${B[$i]} --to ${B[$((i+1))]} --fps 120 --crf 12 --preset veryfast --out ../out/parts_cxen/p$i.mp4 > ../out/parts_cxen/p$i.log 2>&1 &
done
wait
printf "file 'p0.mp4'\nfile 'p1.mp4'\nfile 'p2.mp4'\nfile 'p3.mp4'\n" > ../out/parts_cxen/list.txt
ffmpeg -y -v error -f concat -safe 0 -i ../out/parts_cxen/list.txt -vf "tmix=frames=2:weights='1 1',fps=60" -c:v libx264 -preset medium -crf 16 -pix_fmt yuv420p -movflags +faststart ../out/video_cxen_silent.mp4 && echo CXEN_OK
