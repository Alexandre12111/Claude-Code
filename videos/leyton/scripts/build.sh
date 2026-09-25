#!/usr/bin/env bash
# Régénère la vidéo de présentation du site Leyton de bout en bout (environ 10 minutes).
# Prérequis : Node 18+, Python 3.9+, puis `npm install` et `pip install pillow numpy imageio-ffmpeg`.
set -euo pipefail
cd "$(dirname "$0")/.."

FPS=60
FFMPEG="${FFMPEG:-$(command -v ffmpeg || python3 -c 'import imageio_ffmpeg; print(imageio_ffmpeg.get_ffmpeg_exe())')}"
export FFMPEG
BASE=https://leyton.com/wp-content/blogs.dir

# Lance des commandes en parallèle et échoue si l'une d'elles échoue.
parallel() {
  local pids=()
  for cmd in "$@"; do bash -c "$cmd" & pids+=($!); done
  for pid in "${pids[@]}"; do wait "$pid"; done
}

echo "1/5 Médias et charte Leyton"
mkdir -p work/media work/brand
curl -fsSL -o work/media/hero-video-optimized-1.mp4 "$BASE/2/files/2026/06/hero-video-optimized-1.mp4"
curl -fsSL -o work/media/Leyton-Animation-Hq.mp4 "$BASE/9/files/2026/06/Leyton-Animation-Hq.mp4"
curl -fsSL -o work/brand/logo.svg "$BASE/2/files/2026/01/logo.svg"
for f in Group-27154 Group-27155 Group-27156 boxicons_pie-chart Frame-2071858276; do
  curl -fsSL -o "work/brand/$f.svg" "$BASE/2/files/2026/05/$f.svg"
done
for f in Nohemi-Regular-BF6438cc579d934 Nohemi-SemiBold-BF6438cc57db2ff Nohemi-Bold-BF6438cc577b524; do
  curl -fsSL -o "work/brand/$f.woff" "$BASE/2/files/2026/04/$f.woff"
done
for v in "hero-video-optimized-1:hero-video:hero-last" "Leyton-Animation-Hq:anim-mobile:mobile-last"; do
  IFS=: read -r src webm last <<< "$v"
  "$FFMPEG" -hide_banner -loglevel error -y -i "work/media/$src.mp4" -c:v libvpx-vp9 -b:v 0 -crf 24 -row-mt 1 -deadline good -cpu-used 4 -an "work/media/$webm.webm"
  "$FFMPEG" -hide_banner -loglevel error -y -sseof -0.05 -i "work/media/$src.mp4" -frames:v 1 "work/media/$last.png"
done

echo "2/5 Captures de leyton.com/fr"
parallel \
  "node scripts/capture.js home home 1440 900 2" \
  "node scripts/capture.js a-propos apropos 1440 900 2" \
  "node scripts/capture.js home m-home 390 844 3"
parallel \
  "node scripts/capture.js financement-innovation m-fin 390 844 3" \
  "node scripts/capture.js a-propos m-apropos 390 844 3" \
  "for p in financement-innovation fiscalite-et-performance performance-achats performance-rh performance-environnementale formations; do node scripts/capture.js \$p x-\$p 1440 900 2 top; done"
for n in home apropos m-home m-fin m-apropos; do echo "  $n"; python3 scripts/stitch.py "$n" | grep -E "ATTENTION|assemblée"; done

echo "3/5 Ressources de la composition"
python3 scripts/prepare_assets.py > /dev/null

echo "4/5 Rendu image par image"
rm -rf work/frames
N=$(python3 -c "import math, sys; print(math.ceil(float(sys.argv[1]) * $FPS))" "$(node scripts/render.js info)")
parallel \
  "node scripts/render.js frames $FPS 0 $((N / 3)) work/frames" \
  "node scripts/render.js frames $FPS $((N / 3)) $((2 * N / 3)) work/frames" \
  "node scripts/render.js frames $FPS $((2 * N / 3)) $N work/frames"

echo "5/5 Encodage H.264"
"$FFMPEG" -hide_banner -loglevel error -y -framerate $FPS -i work/frames/f_%05d.jpg \
  -vf "scale=in_color_matrix=bt601:in_range=pc:out_color_matrix=bt709:out_range=tv,format=yuv420p" \
  -c:v libx264 -preset slow -crf 20 -tune animation -x264-params aq-mode=3 -profile:v high -level 4.2 -g 120 \
  -colorspace bt709 -color_primaries bt709 -color_trc bt709 -color_range tv -movflags +faststart \
  -metadata title="Leyton, présentation du site leyton.com" -an leyton-presentation-site.mp4
"$FFMPEG" -hide_banner -loglevel error -y -ss 9.9 -i leyton-presentation-site.mp4 -frames:v 1 -q:v 2 leyton-presentation-site-miniature.jpg
echo "Vidéo prête : leyton-presentation-site.mp4"
