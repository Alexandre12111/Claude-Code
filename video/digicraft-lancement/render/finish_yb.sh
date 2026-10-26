#!/bin/bash
# Charte YouCraft : sous-titres placés sur l'image rebrandée puis incrustation, dès que chaque rendu est prêt.
# Une version déjà incrustée après son rendu n'est pas refaite (reprise après redémarrage).
cd "$(dirname "$0")/.."
export YC_BRAND=1
declare -A NAME=([fr]=FR [en]=EN [uk]=UK_EN [us]=US_EN [caen]=Canada_EN [cafr]=Canada_FR [de]=DE [es]=ES [it]=IT [nl]=NL [pl]=PL [sv]=SE)
for L in "$@"; do
  until grep -q "^${L}_OK" out/render_yb.log || ! pgrep -f render_yb_queue.sh > /dev/null; do sleep 20; done
  grep -q "^${L}_OK" out/render_yb.log || { echo "${L}_RENDER_FAIL"; continue; }
  OUT=out/YouCraft_NewBrand_${NAME[$L]}_58s_voiceover_subtitles.mp4
  if [ -s "$OUT" ] && [ "$OUT" -nt out/video_yb_${L}_silent.mp4 ]; then echo "$L ok (déjà fait)"; continue; fi
  (cd audio && python3 subs_markets.py $L) && python3 render/burn_markets.py $L || echo "${L}_BURN_FAIL"
done
echo FINISH_DONE
