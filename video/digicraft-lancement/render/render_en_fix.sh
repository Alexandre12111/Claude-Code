#!/bin/bash
# Rendu version anglaise : 120 i/s puis fusion de 2 images -> 60 i/s avec flou de mouvement.
cd "$(dirname "$0")"
export NODE_PATH=/opt/node22/lib/node_modules
mkdir -p ../out/parts_en
B=(0 14.55 29.1 43.65 58.25)
for i in 1; do
  node render.cjs --page src5/index.html --video --from ${B[$i]} --to ${B[$((i+1))]} --fps 120 --crf 12 --preset veryfast --out ../out/parts_en/p$i.mp4 > ../out/parts_en/p$i.log 2>&1 &
done
wait
printf "file 'p0.mp4'\nfile 'p1.mp4'\nfile 'p2.mp4'\nfile 'p3.mp4'\n" > ../out/parts_en/list.txt
ffmpeg -y -v error -f concat -safe 0 -i ../out/parts_en/list.txt -vf "tmix=frames=2:weights='1 1',fps=60" -c:v libx264 -preset medium -crf 16 -pix_fmt yuv420p -movflags +faststart ../out/video_en_silent.mp4 && echo EN_OK
