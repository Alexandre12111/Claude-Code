#!/bin/bash
# YouCraft : ne recalcule que les passages où le nom apparaît à l'écran (logo d'ouverture, démo et titres,
# signature finale) puis les raccorde à la vidéo muette d'origine, image pour image (60 i/s).
# Chaque passage démarre 0,05 s plus tôt pour que sa première image garde le flou de mouvement d'origine.
cd "$(dirname "$0")"
export NODE_PATH=/opt/node22/lib/node_modules
declare -A ORIG=([fr]=video_cxfr2_silent [en]=video_cxen_silent [uk]=video_lt_uken_silent [us]=video_lt_usen_silent [caen]=video_lt_caen_silent [cafr]=video_lt_cafr_silent [de]=video_lt_de_silent [es]=video_lt_es_silent [it]=video_lt_it_silent [nl]=video_lt_nl_silent [pl]=video_lt_pl_silent [sv]=video_lt_sv_silent)
W=("0 4.0" "19.15 25.4" "25.35 31.6" "31.55 37.8" "53.15 58.25")
for L in "$@"; do
  P=../out/parts_yc_$L; mkdir -p $P
  for i in 0 1 2 3 4; do
    set -- ${W[$i]}
    node render.cjs --page src_yc_$L/index.html --video --from $1 --to $2 --fps 120 --crf 12 --preset veryfast --out $P/w$i.mp4 > $P/w$i.log 2>&1 &
  done
  wait
  M="tmix=frames=2:weights='1 1',fps=60"
  ffmpeg -y -v error -i ../out/${ORIG[$L]}.mp4 -i $P/w0.mp4 -i $P/w1.mp4 -i $P/w2.mp4 -i $P/w3.mp4 -i $P/w4.mp4 -filter_complex "\
[0:v]split=2[s1][s2];\
[s1]trim=start=4.0:end=19.2,setpts=PTS-STARTPTS[o1];[s2]trim=start=37.8:end=53.2,setpts=PTS-STARTPTS[o2];\
[1:v]$M,setpts=PTS-STARTPTS[w0];\
[2:v]$M,trim=start=0.05,setpts=PTS-STARTPTS[w1];[3:v]$M,trim=start=0.05,setpts=PTS-STARTPTS[w2];\
[4:v]$M,trim=start=0.05,setpts=PTS-STARTPTS[w3];[5:v]$M,trim=start=0.05,setpts=PTS-STARTPTS[w4];\
[w0][o1][w1][w2][w3][o2][w4]concat=n=7:v=1:a=0,fps=60[v]" -map "[v]" -c:v libx264 -preset medium -crf 16 -pix_fmt yuv420p -movflags +faststart ../out/video_yc_${L}_silent.mp4 \
    && echo "${L}_OK $(ffprobe -v error -count_frames -select_streams v -show_entries stream=nb_read_frames -of csv=p=0 ../out/video_yc_${L}_silent.mp4) images" || echo "${L}_FAIL"
done
echo ALL_DONE
