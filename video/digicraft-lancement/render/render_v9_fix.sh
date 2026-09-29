#!/bin/bash
# Rendu V9 : 120 i/s puis fusion de 2 images -> 60 i/s avec flou de mouvement.
cd "$(dirname "$0")"
export NODE_PATH=/opt/node22/lib/node_modules
mkdir -p ../out/parts9
B=(0 13.7 27.35 41 54.65)
for i in 1; do
  node render.cjs --page src4/index.html --video --from ${B[$i]} --to ${B[$((i+1))]} --fps 120 --crf 12 --preset veryfast --out ../out/parts9/p$i.mp4 > ../out/parts9/p$i.log 2>&1 &
done
wait
printf "file 'p0.mp4'\nfile 'p1.mp4'\nfile 'p2.mp4'\nfile 'p3.mp4'\n" > ../out/parts9/list.txt
ffmpeg -y -v error -f concat -safe 0 -i ../out/parts9/list.txt -vf "tmix=frames=2:weights='1 1',fps=60" -c:v libx264 -preset medium -crf 16 -pix_fmt yuv420p -movflags +faststart ../out/video9_silent.mp4 && echo V9_OK
