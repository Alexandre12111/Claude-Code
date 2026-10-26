#!/bin/bash
cd "$(dirname "$0")"
export NODE_PATH=/opt/node22/lib/node_modules
B=(0 15.3 30.6 45.9 61.2)
for i in 0 1 2 3; do
  node render.cjs --video --from ${B[$i]} --to ${B[$((i+1))]} --fps 60 --crf 14 --out ../out/parts/p$i.mp4 > ../out/parts/p$i.log 2>&1 &
done
wait
printf "file 'p0.mp4'\nfile 'p1.mp4'\nfile 'p2.mp4'\nfile 'p3.mp4'\n" > ../out/parts/list.txt
ffmpeg -y -v error -f concat -safe 0 -i ../out/parts/list.txt -c copy ../out/video_silent.mp4 && echo CONCAT_OK
