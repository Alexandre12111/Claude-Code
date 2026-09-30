#!/bin/bash
# Reprise du morceau 29,1 à 43,65 s en deux moitiés parallèles, puis assemblage 120 i/s -> 60 i/s.
cd "$(dirname "$0")"
export NODE_PATH=/opt/node22/lib/node_modules
node render.cjs --page src9/index.html --video --from 29.1 --to 36.4 --fps 120 --crf 12 --preset veryfast --out ../out/parts_cxfr2/p2a.mp4 > ../out/parts_cxfr2/p2a.log 2>&1 &
node render.cjs --page src9/index.html --video --from 36.4 --to 43.65 --fps 120 --crf 12 --preset veryfast --out ../out/parts_cxfr2/p2b.mp4 > ../out/parts_cxfr2/p2b.log 2>&1 &
wait
printf "file 'p0.mp4'\nfile 'p1.mp4'\nfile 'p2a.mp4'\nfile 'p2b.mp4'\nfile 'p3.mp4'\n" > ../out/parts_cxfr2/list.txt
ffmpeg -y -v error -f concat -safe 0 -i ../out/parts_cxfr2/list.txt -vf "tmix=frames=2:weights='1 1',fps=60" -c:v libx264 -preset medium -crf 16 -pix_fmt yuv420p -movflags +faststart ../out/video_cxfr2_silent.mp4 && echo CXFR2_OK
