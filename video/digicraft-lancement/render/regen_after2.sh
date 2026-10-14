#!/bin/bash
# Attend la fin des autres générations de voix puis régénère les langues indiquées.
cd "$(dirname "$0")/../audio"
while pgrep -f "regen_after.sh es it" >/dev/null || pgrep -f "python3 gen_vo_markets.py" >/dev/null; do sleep 20; done
python3 gen_vo_markets.py "$@"
