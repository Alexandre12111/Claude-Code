#!/bin/bash
# Charte YouCraft : sous-titres placés sur l'image rebrandée puis incrustation, dès que chaque rendu est prêt.
cd "$(dirname "$0")/.."
export YC_BRAND=1
for L in "$@"; do
  until grep -q "^${L}_OK" out/render_yb.log || ! pgrep -f render_yb_queue.sh > /dev/null; do sleep 20; done
  grep -q "^${L}_OK" out/render_yb.log || { echo "${L}_RENDER_FAIL"; continue; }
  (cd audio && python3 subs_markets.py $L) && python3 render/burn_markets.py $L || echo "${L}_BURN_FAIL"
done
echo FINISH_DONE
