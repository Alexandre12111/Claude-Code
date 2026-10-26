#!/bin/bash
# Attend la fin de la file de voix puis régénère les langues dont le texte a été resserré.
cd "$(dirname "$0")/../audio"
while pgrep -f "python3 gen_vo_markets.py cafr" >/dev/null; do sleep 20; done
python3 gen_vo_markets.py "$@"
