#!/bin/bash
# Rendu charte CognitX : 120 i/s puis fusion de 2 images -> 60 i/s avec flou de mouvement.
cd "$(dirname "$0")"
export NODE_PATH=/opt/node22/lib/node_modules
mkdir -p ../out/parts_b50
B=(0 13.5 27 40.5 54)
for i in 0 1 2 3; do
  node render.cjs --page src_b50/index.html --video --from ${B[$i]} --to ${B[$((i+1))]} --fps 120 --crf 12 --preset veryfast --out ../out/parts_b50/p$i.mp4 > ../out/parts_b50/p$i.log 2>&1 &
done
wait
printf "file 'p0.mp4'\nfile 'p1.mp4'\nfile 'p2.mp4'\nfile 'p3.mp4'\n" > ../out/parts_b50/list.txt
ffmpeg -y -v error -f concat -safe 0 -i ../out/parts_b50/list.txt -vf "tmix=frames=2:weights='1 1',fps=60" -c:v libx264 -preset medium -crf 16 -pix_fmt yuv420p -movflags +faststart ../out/video_b50_silent.mp4 && echo b50_OK
cd ..
for v in "voiceover mix_b50" "music mix_b50_musique"; do set -- $v
  ffmpeg -y -v error -i out/video_b50_silent.mp4 -i audio/stems/mix_b48${2#mix_b50}.wav -map 0:v -map 1:a -c:v libx264 -preset slow -crf 23 -pix_fmt yuv420p -c:a aac -b:a 192k -shortest -movflags +faststart out/DigiCraft_BringIdeasToLife_EN_54s_${1}_v7.mp4
done
ls -la out/DigiCraft_BringIdeasToLife_EN_54s_*_v7.mp4 && echo ALL_DONE
