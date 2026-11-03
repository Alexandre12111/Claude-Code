#!/bin/bash
# Vidéo consultants avec la voix clonée d’Alexandre (src_cons/index_alex.html, environ 3 min) : rendu à 60 i/s en 4 morceaux parallèles, puis voix off et sous-titres incrustés.
# Prérequis (dans audio/) : VOICE_REF_DIR=... tts_cons_alex.py, puis VOICE_SUF=_alex pour build_cons.py, music_cons.py, sfx_cons.py, mix_cons.py
cd "$(dirname "$0")"
export NODE_PATH=/opt/node22/lib/node_modules
END=$(python3 -c "import json,re;print(json.loads(re.search(r'window\.CT = (\{.*\});', open('../src_cons/cons_timeline_alex.js').read()).group(1))['END'])")
mkdir -p ../out/parts_cons_alex
for i in 0 1 2 3; do
  if tail -1 ../out/parts_cons_alex/p$i.log 2>/dev/null | grep -q '^done'; then continue; fi
  A=$(python3 -c "print(round($END*$i/4,3))"); B=$(python3 -c "print(round($END*($i+1)/4,3))")
  node render.cjs --page src_cons/index_alex.html --video --from $A --to $B --fps 60 --crf 14 --preset veryfast --out ../out/parts_cons_alex/p$i.mp4 > ../out/parts_cons_alex/p$i.log 2>&1 &
done
wait
printf "file 'p0.mp4'\nfile 'p1.mp4'\nfile 'p2.mp4'\nfile 'p3.mp4'\n" > ../out/parts_cons_alex/list.txt
ffmpeg -y -v error -f concat -safe 0 -i ../out/parts_cons_alex/list.txt -c:v libx264 -preset medium -crf 17 -pix_fmt yuv420p -movflags +faststart ../out/video_cons_alex_silent.mp4 && echo cons_OK
cd ..
ffmpeg -y -v error -i out/video_cons_alex_silent.mp4 -i audio/stems/mix_cons_alex.wav -map 0:v -map 1:a -c:v copy -c:a aac -b:a 192k -shortest -movflags +faststart out/YouCraft_Consultants_FR_voix_Alexandre_soustitres.mp4
ls -la out/YouCraft_Consultants_FR_*.mp4 && echo ALL_DONE
