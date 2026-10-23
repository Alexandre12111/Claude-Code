#!/bin/bash
# Rendu complet des versions à la nouvelle charte YouCraft (minutage long, 58,25 s), une langue après l autre.
cd "$(dirname "$0")"
export NODE_PATH=/opt/node22/lib/node_modules
B=(0 14.6 29.1 43.65 58.25)
for L in "$@"; do
  mkdir -p ../out/parts_yb_$L
  for i in 0 1 2 3; do
    node render.cjs --page src_yb_$L/index.html --video --from ${B[$i]} --to ${B[$((i+1))]} --fps 120 --crf 12 --preset veryfast --out ../out/parts_yb_$L/p$i.mp4 > ../out/parts_yb_$L/p$i.log 2>&1 &
  done
  wait
  printf "file 'p0.mp4'\nfile 'p1.mp4'\nfile 'p2.mp4'\nfile 'p3.mp4'\n" > ../out/parts_yb_$L/list.txt
  ffmpeg -y -v error -f concat -safe 0 -i ../out/parts_yb_$L/list.txt -vf "tmix=frames=2:weights='1 1',fps=60" -c:v libx264 -preset medium -crf 16 -pix_fmt yuv420p -movflags +faststart ../out/video_yb_${L}_silent.mp4 && echo "${L}_OK"
done
echo ALL_DONE
