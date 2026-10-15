#!/bin/bash
# Réincruste les sous-titres v2 dans les 12 vidéos puis pousse chaque fichier.
cd "$(dirname "$0")/.."
declare -A NAME=( [fr]=FR [en]=EN [uk]=UK_EN [us]=US_EN [caen]=Canada_EN [cafr]=Canada_FR [de]=DE [es]=ES [it]=IT [nl]=NL [pl]=PL [sv]=SE )
for C in "$@"; do
  python3 render/burn_markets.py $C > out/reburn_$C.log 2>&1 || { echo "FAIL $C"; continue; }
  F=out/DigiCraft_CognitX_${NAME[$C]}_58s_voiceover_subtitles.mp4
  git add $F out/subs/$C.ass out/subs/$C.srt >/dev/null 2>&1
  git commit -qm "Lancement CognitX ${NAME[$C]} : sous-titres v2 (bandeau unique arrondi)

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_01ANa5WCLDa281vmw14n3Srh" >/dev/null 2>&1
  for k in 1 2 3; do git push -q -u origin claude/admiring-goodall-xxzy4c >/dev/null 2>&1 && break || git pull -q --no-rebase --no-edit origin claude/admiring-goodall-xxzy4c >/dev/null 2>&1; done
  echo "DONE $C $F"
done
echo FINISHED
