"""Prépare les ressources de la composition (composition/assets) à partir des captures et de la charte Leyton."""
import os
import shutil
import subprocess
from pathlib import Path

import numpy as np
from PIL import Image, ImageChops, ImageDraw

ROOT = Path(__file__).resolve().parent.parent
WORK = ROOT / 'work'
CAPS = WORK / 'caps'
MEDIA = WORK / 'media'
BRAND = WORK / 'brand'
NODE = ROOT / 'node_modules'
A = ROOT / 'composition' / 'assets'
FFMPEG = os.environ.get('FFMPEG') or shutil.which('ffmpeg')
if not FFMPEG:
    import imageio_ffmpeg
    FFMPEG = imageio_ffmpeg.get_ffmpeg_exe()

(A / 'intro').mkdir(parents=True, exist_ok=True)
(A / 'fonts').mkdir(parents=True, exist_ok=True)


def jpg(im, path, q=92):
    im.convert('RGB').save(path, quality=q, subsampling=0, optimize=True)
    print(path.name, im.size, path.stat().st_size // 1024, 'Ko')


def with_hero_frame(page, frame_path, box, fit, k):
    """Recompose la dernière image de la vidéo du hero (l'anneau Leyton) à l'endroit où le site
    la dessine, en mode produit : la vidéo est masquée pendant les captures."""
    frame = Image.open(frame_path).convert('RGB').point(lambda v: 255 if v >= 250 else v)
    bx, by, bw, bh = [v * k for v in box]
    s = (max if fit == 'cover' else min)(bw / frame.width, bh / frame.height)
    dw, dh = frame.width * s, frame.height * s
    fr = frame.resize((round(dw), round(dh)), Image.LANCZOS)
    top = page.crop((0, 0, page.width, int(by + bh) + 2))
    layer = Image.new('RGB', top.size, (255, 255, 255))
    layer.paste(fr, (round(bx + (bw - dw) / 2), round(by + (bh - dh) / 2)))
    mask = Image.new('L', top.size, 0)
    ImageDraw.Draw(mask).rectangle([bx, max(0, by), bx + bw, by + bh], fill=255)
    layer = Image.composite(layer, Image.new('RGB', top.size, (255, 255, 255)), mask)
    page.paste(ImageChops.multiply(top, layer), (0, 0))
    return page


# Boîte de la vidéo (px CSS) et ajustement object-fit, relevés sur le site
HERO = {
    'home': (MEDIA / 'hero-last.png', (5, 5, 1430, 897.17), 'cover', 2),
    'm-home': (MEDIA / 'mobile-last.png', (0, -19.5, 390, 787.83), 'contain', 3),
}

# Pages bureau : 1440 px CSS de large (capturées en 2x, réduction Lanczos)
for name in ['home', 'apropos']:
    im = Image.open(CAPS / f'{name}-full.png').convert('RGB')
    if name in HERO:
        im = with_hero_frame(im, *HERO[name])
    jpg(im.resize((1440, round(im.height * 1440 / im.width)), Image.LANCZOS), A / f'{name}.jpg')

# Premiers écrans des six pages expertises
for slug in ['financement-innovation', 'fiscalite-et-performance', 'performance-achats',
             'performance-rh', 'performance-environnementale', 'formations']:
    jpg(Image.open(CAPS / f'x-{slug}' / '000.png').resize((1440, 900), Image.LANCZOS), A / f'x-{slug}.jpg')

# Pages mobiles : 5 200 premiers px CSS, conservés en 2x (780 px de large)
for name in ['m-home', 'm-fin', 'm-apropos']:
    im = Image.open(CAPS / f'{name}-full.png').convert('RGB')
    if name in HERO:
        im = with_hero_frame(im, *HERO[name])
    im = im.crop((0, 0, im.width, min(im.height, 5200 * 3)))
    jpg(im.resize((780, round(im.height * 780 / im.width)), Image.LANCZOS), A / f'{name}.jpg')

# Intro : vidéo hero du site, de 3,9 s à la fin, en 60 i/s
subprocess.run([FFMPEG, '-hide_banner', '-loglevel', 'error', '-y', '-ss', '3.9', '-i', str(MEDIA / 'hero-video-optimized-1.mp4'),
                '-q:v', '2', str(A / 'intro' / 'f_%04d.jpg')], check=True)
print('images intro', len(os.listdir(A / 'intro')))
shutil.copy(MEDIA / 'hero-last.png', A / 'ring.png')

# Grain statique très léger contre les paliers des dégradés
rng = np.random.default_rng(7)
Image.fromarray(np.clip(rng.normal(128, 30, (512, 512)), 0, 255).astype(np.uint8), 'L').convert('RGB').save(A / 'grain.png')

# Polices, icônes, logo, GSAP
for f in os.listdir(BRAND):
    if f.startswith('Nohemi'):
        shutil.copy(BRAND / f, A / 'fonts' / (f.split('-BF')[0] + '.woff'))
    elif f.endswith('.svg'):
        shutil.copy(BRAND / f, A / f)
for wgt in (400, 500, 600, 700):
    shutil.copy(NODE / f'@fontsource/montserrat/files/montserrat-latin-{wgt}-normal.woff2', A / 'fonts')
shutil.copy(NODE / 'gsap/dist/gsap.min.js', A / 'gsap.min.js')
print(sorted(os.listdir(A)))
