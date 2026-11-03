#!/bin/bash
# Vidéo personnalisée pour le groupe KMC (src_kmc/index.html), voix clonée d’Alexandre : rendu à 60 i/s en 4 morceaux parallèles, puis voix off.
# Prérequis (dans audio/) : VOICE_REF_DIR=... tts_cons_alex.py script_kmc vo_kmc_takes 11,23,37, puis build_kmc.py, music_kmc.py, sfx_kmc.py, mix_kmc.py
cd "$(dirname "$0")"
export NODE_PATH=/opt/node22/lib/node_modules
END=$(python3 -c "import json,re;print(json.loads(re.search(r'window\.CT = (\{.*\});', open('../src_kmc/kmc_timeline.js').read()).group(1))['END'])")
mkdir -p ../out/parts_kmc
for i in 0 1 2 3; do
  if tail -1 ../out/parts_kmc/p$i.log 2>/dev/null | grep -q '^done'; then continue; fi
  A=$(python3 -c "print(round($END*$i/4,3))"); B=$(python3 -c "print(round($END*($i+1)/4,3))")
  node render.cjs --page src_kmc/index.html --video --from $A --to $B --fps 60 --crf 14 --preset veryfast --out ../out/parts_kmc/p$i.mp4 > ../out/parts_kmc/p$i.log 2>&1 &
done
wait
printf "file 'p0.mp4'\nfile 'p1.mp4'\nfile 'p2.mp4'\nfile 'p3.mp4'\n" > ../out/parts_kmc/list.txt
ffmpeg -y -v error -f concat -safe 0 -i ../out/parts_kmc/list.txt -c:v libx264 -preset medium -crf 17 -pix_fmt yuv420p -movflags +faststart ../out/video_kmc_silent.mp4 && echo kmc_OK
cd ..
ffmpeg -y -v error -i out/video_kmc_silent.mp4 -i audio/stems/mix_kmc.wav -map 0:v -map 1:a -c:v copy -c:a aac -b:a 192k -shortest -movflags +faststart out/YouCraft_KMC_FR_voix_Alexandre.mp4
ls -la out/YouCraft_KMC_FR_*.mp4 && echo ALL_DONE
