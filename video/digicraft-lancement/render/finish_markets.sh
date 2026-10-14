#!/bin/bash
# Pour chaque marché : attend le rendu et la voix, contrôle la diction, mixe, incruste les sous-titres, pousse.
cd "$(dirname "$0")/.."
declare -A NAME=( [uk]=UK_EN [us]=US_EN [caen]=Canada_EN [cafr]=Canada_FR [de]=DE [es]=ES [it]=IT [nl]=NL [pl]=PL [sv]=SE )
declare -A VID=( [uk]=uken [us]=usen [caen]=caen [cafr]=cafr [de]=de [es]=es [it]=it [nl]=nl [pl]=pl [sv]=sv )
for C in "$@"; do
  until [ -f out/video_lt_${VID[$C]}_silent.mp4 ] && [ -f audio/vo_mk/$C/meta.json ] && ! pgrep -f "parts_lt_${VID[$C]}/" >/dev/null; do sleep 30; done
  (cd audio && python3 asr_check_markets.py $C && python3 mix_markets.py $C) > out/finish_$C.log 2>&1
  nice -n 5 python3 render/burn_markets.py $C >> out/finish_$C.log 2>&1 || { echo "FAIL $C"; continue; }
  F=out/DigiCraft_CognitX_${NAME[$C]}_58s_voiceover_subtitles.mp4
  git add $F out/subs/$C.ass out/subs/$C.srt >/dev/null 2>&1
  git commit -qm "Lancement CognitX ${NAME[$C]} : voix off et sous-titres

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_01ANa5WCLDa281vmw14n3Srh" >/dev/null 2>&1
  for k in 1 2 3; do git push -q -u origin claude/admiring-goodall-xxzy4c >/dev/null 2>&1 && break || git pull -q --no-rebase --no-edit origin claude/admiring-goodall-xxzy4c >/dev/null 2>&1; done
  echo "DONE $C $F"
done
echo FINISHED
