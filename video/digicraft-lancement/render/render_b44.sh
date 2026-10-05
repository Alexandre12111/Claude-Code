#!/bin/bash
# Rendu charte CognitX : 120 i/s puis fusion de 2 images -> 60 i/s avec flou de mouvement.
cd "$(dirname "$0")"
export NODE_PATH=/opt/node22/lib/node_modules
mkdir -p ../out/parts_b44
B=(0 13.5 27 40.5 54)
for i in 0 1 2 3; do
  node render.cjs --page src_b44/index.html --video --from ${B[$i]} --to ${B[$((i+1))]} --fps 120 --crf 12 --preset veryfast --out ../out/parts_b44/p$i.mp4 > ../out/parts_b44/p$i.log 2>&1 &
done
wait
printf "file 'p0.mp4'\nfile 'p1.mp4'\nfile 'p2.mp4'\nfile 'p3.mp4'\n" > ../out/parts_b44/list.txt
ffmpeg -y -v error -f concat -safe 0 -i ../out/parts_b44/list.txt -vf "tmix=frames=2:weights='1 1',fps=60" -c:v libx264 -preset medium -crf 16 -pix_fmt yuv420p -movflags +faststart ../out/video_b44_silent.mp4 && echo b44_OK
