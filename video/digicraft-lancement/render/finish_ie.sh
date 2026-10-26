#!/bin/bash
# Version Irlande (IE_EN) à la charte YouCraft : image src_yb_ie (euros, région « Munster »), même voix off et mêmes
# sous-titres que la version EN (texte identique, données hébergées en Europe). La piste son est reprise telle quelle
# du master EN validé, sans réencodage. Prérequis : bash render_yb_queue.sh ie
cd "$(dirname "$0")/.."
ffmpeg -y -v error -i out/video_yb_ie_silent.mp4 -i out/YouCraft_NewBrand_EN_58s_voiceover_subtitles.mp4 \
  -map 0:v -map 1:a -vf "subtitles=out/subs_yb/ie.ass:fontsdir=assets/fonts/ttf_yc" \
  -c:v libx264 -preset slow -crf 21 -pix_fmt yuv420p -c:a copy -shortest -movflags +faststart \
  out/YouCraft_NewBrand_IE_EN_58s_voiceover_subtitles.mp4 && echo "ie ok"
