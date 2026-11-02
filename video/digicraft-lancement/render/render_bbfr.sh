#!/bin/bash
# « Donnez vie à vos idées » : version française allongée (src_bbfr, 79 s). 120 i/s puis fusion de 2 images -> 60 i/s.
# Prérequis : python3 brand_yc/translate_bbfr.py src_bb src_bbfr, puis audio : tts_bbfr.py, music_bbfr.py, sfx_bbfr.py, mix_bbfr.py
cd "$(dirname "$0")"
export NODE_PATH=/opt/node22/lib/node_modules
mkdir -p ../out/parts_bbfr
B=(0 19.75 39.5 59.25 79)
for i in 0 1 2 3; do
  if tail -1 ../out/parts_bbfr/p$i.log 2>/dev/null | grep -q '^done'; then continue; fi
  node render.cjs --page src_bbfr/index.html --video --from ${B[$i]} --to ${B[$((i+1))]} --fps 120 --crf 12 --preset veryfast --out ../out/parts_bbfr/p$i.mp4 > ../out/parts_bbfr/p$i.log 2>&1 &
done
wait
printf "file 'p0.mp4'\nfile 'p1.mp4'\nfile 'p2.mp4'\nfile 'p3.mp4'\n" > ../out/parts_bbfr/list.txt
ffmpeg -y -v error -f concat -safe 0 -i ../out/parts_bbfr/list.txt -vf "tmix=frames=2:weights='1 1',fps=60" -c:v libx264 -preset medium -crf 16 -pix_fmt yuv420p -movflags +faststart ../out/video_bbfr_silent.mp4 && echo bbfr_OK
cd ..
for v in "voix_off mix_bbfr" "musique mix_bbfr_musique"; do set -- $v
  ffmpeg -y -v error -i out/video_bbfr_silent.mp4 -i audio/stems/$2.wav -map 0:v -map 1:a -c:v libx264 -preset slow -crf 23 -pix_fmt yuv420p -c:a aac -b:a 192k -shortest -movflags +faststart out/YouCraft_DonnezVieAVosIdees_FR_79s_$1.mp4
done
ls -la out/YouCraft_DonnezVieAVosIdees_FR_79s_*.mp4 && echo ALL_DONE
