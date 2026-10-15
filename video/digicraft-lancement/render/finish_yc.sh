#!/bin/bash
# Incruste chaque version YouCraft dès que son image renommée est prête (file render_yc_queue.sh).
cd "$(dirname "$0")/.."
for L in "$@"; do
  until grep -q "^${L}_OK\|^${L}_FAIL" out/render_yc.log; do sleep 20; done
  grep -q "^${L}_OK" out/render_yc.log || { echo "${L}_RENDER_FAIL"; continue; }
  python3 audio/subs_markets.py $L && python3 render/burn_markets.py $L || echo "${L}_BURN_FAIL"
done
echo FINISH_DONE
