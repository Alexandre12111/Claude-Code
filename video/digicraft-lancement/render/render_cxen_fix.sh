#!/bin/bash
# Reprise EN CognitX (intuitivité) : passages 14,55 à 29,1 s et 43,65 à 58,25 s en quatre morceaux parallèles.
cd "$(dirname "$0")"
export NODE_PATH=/opt/node22/lib/node_modules
R() { node render.cjs --page src7/index.html --video --from $1 --to $2 --fps 120 --crf 12 --preset veryfast --out ../out/parts_cxen/$3.mp4 > ../out/parts_cxen/$3.log 2>&1 & }
R 14.55 21.8 p1a; R 21.8 29.1 p1b; R 43.65 50.95 p3a; R 50.95 58.25 p3b
wait
printf "file 'p0.mp4'\nfile 'p1a.mp4'\nfile 'p1b.mp4'\nfile 'p2.mp4'\nfile 'p3a.mp4'\nfile 'p3b.mp4'\n" > ../out/parts_cxen/list.txt
ffmpeg -y -v error -f concat -safe 0 -i ../out/parts_cxen/list.txt -vf "tmix=frames=2:weights='1 1',fps=60" -c:v libx264 -preset medium -crf 16 -pix_fmt yuv420p -movflags +faststart ../out/video_cxen_silent.mp4 && echo CXEN_OK
