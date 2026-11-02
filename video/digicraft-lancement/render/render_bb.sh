#!/bin/bash
# « Bring your ideas to life » à la charte YouCraft (src_bb) : 120 i/s puis fusion de 2 images -> 60 i/s avec flou de mouvement.
# Prérequis : python3 ../brand_yc/apply_brand_bb.py src_b50 src_bb (depuis la racine du projet) et python3 audio/mix_bb.py
cd "$(dirname "$0")"
export NODE_PATH=/opt/node22/lib/node_modules
mkdir -p ../out/parts_bb
B=(0 13.5 27 40.5 54)
for i in 0 1 2 3; do
  if tail -1 ../out/parts_bb/p$i.log 2>/dev/null | grep -q '^done'; then continue; fi
  node render.cjs --page src_bb/index.html --video --from ${B[$i]} --to ${B[$((i+1))]} --fps 120 --crf 12 --preset veryfast --out ../out/parts_bb/p$i.mp4 > ../out/parts_bb/p$i.log 2>&1 &
done
wait
printf "file 'p0.mp4'\nfile 'p1.mp4'\nfile 'p2.mp4'\nfile 'p3.mp4'\n" > ../out/parts_bb/list.txt
ffmpeg -y -v error -f concat -safe 0 -i ../out/parts_bb/list.txt -vf "tmix=frames=2:weights='1 1',fps=60" -c:v libx264 -preset medium -crf 16 -pix_fmt yuv420p -movflags +faststart ../out/video_bb_silent.mp4 && echo bb_OK
cd ..
for v in "voiceover mix_bb" "music mix_bb_musique"; do set -- $v
  ffmpeg -y -v error -i out/video_bb_silent.mp4 -i audio/stems/$2.wav -map 0:v -map 1:a -c:v libx264 -preset slow -crf 23 -pix_fmt yuv420p -c:a aac -b:a 192k -shortest -movflags +faststart out/YouCraft_BringIdeasToLife_EN_54s_$1.mp4
done
ls -la out/YouCraft_BringIdeasToLife_EN_54s_*.mp4 && echo ALL_DONE
