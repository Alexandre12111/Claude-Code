"""Fonds V7 : photos très floutées, adoucies et éclairées (esprit Base44), halo de marque et studio sombre."""
import sys
import numpy as np
from PIL import Image, ImageEnhance, ImageFilter
R, O = sys.argv[1], sys.argv[2]
W, H = 2304, 1296
yy, xx = np.mgrid[0:H, 0:W].astype(np.float32)


def cover(im, fy=0.5):
    r = max(W / im.width, H / im.height); im = im.resize((round(im.width * r), round(im.height * r)), Image.LANCZOS)
    x = (im.width - W) // 2; y = int((im.height - H) * fy); return im.crop((x, y, x + W, y + H))


def blur(im, rad):
    # Flou large calculé en basse définition puis lissé : rendu crémeux sans artefacts.
    k = 4
    return im.resize((W // k, H // k), Image.LANCZOS).filter(ImageFilter.GaussianBlur(rad / k)).resize((W, H), Image.BICUBIC).filter(ImageFilter.GaussianBlur(3))


def radial(cx, cy, rx, ry):
    return np.clip(1 - np.sqrt(((xx - cx) / rx) ** 2 + ((yy - cy) / ry) ** 2), 0, 1) ** 2


def screen(a, col, m):
    c = np.array(col, np.float32)[None, None, :] / 255
    return 1 - (1 - a) * (1 - c * m[..., None])


def soft(name, fy=0.5, rad=60, lift=0.10, glow=(255, 255, 255), gm=0.35, sat=0.95, gy=0.45):
    im = cover(Image.open(f'{R}/{name}.jpg').convert('RGB'), fy)
    im = ImageEnhance.Color(blur(im, rad)).enhance(sat)
    a = np.asarray(im, np.float32) / 255
    a = lift + a * (1 - lift)                                  # noirs relevés : fond moins présent
    a = screen(a, glow, gm * radial(W / 2, H * gy, W * 0.62, H * 0.75))   # lumière douce derrière l'interface
    return Image.fromarray((np.clip(a, 0, 1) * 255).astype(np.uint8))


def dither(a):
    rng = np.random.default_rng(7)
    return Image.fromarray(np.clip(a * 255 + rng.uniform(-0.6, 0.6, a.shape), 0, 255).astype(np.uint8))


def lerpc(stops, t):
    t = np.clip(t, 0, 1); out = np.zeros(t.shape + (3,), np.float32)
    for (t0, c0), (t1, c1) in zip(stops, stops[1:]):
        m = (t >= t0) & (t <= t1); u = ((t - t0) / (t1 - t0))[m][:, None]
        out[m] = np.array(c0) * (1 - u) + np.array(c1) * u
    return out / 255


def horizon():
    """Halo de marque façon « Just by thinking » : ciel marine et soleil bleu violet qui monte, sans bande dure."""
    a = lerpc([(0, (5, 14, 40)), (0.5, (20, 32, 96)), (1, (44, 50, 150))], yy / H)
    a = screen(a, (118, 96, 255), 0.85 * radial(W / 2, H * 0.95, W * 0.8, H * 0.78))
    a = screen(a, (110, 185, 255), 0.75 * radial(W / 2, H * 0.95, W * 0.48, H * 0.42))
    a = screen(a, (255, 255, 255), 0.92 * radial(W / 2, H * 0.96, W * 0.24, H * 0.2))
    im = Image.fromarray((np.clip(a, 0, 1) * 255).astype(np.uint8)).filter(ImageFilter.GaussianBlur(12))
    return dither(np.asarray(im, np.float32) / 255)


def studio():
    """Studio sombre façon keynote : marine profond, douche de lumière en haut, reflet au sol."""
    a = lerpc([(0, (8, 22, 48)), (0.55, (4, 12, 28)), (1, (2, 6, 16))], yy / H)
    a = screen(a, (70, 140, 210), 0.55 * radial(W / 2, -H * 0.05, W * 0.55, H * 0.85))
    a = screen(a, (110, 100, 240), 0.25 * radial(W * 0.5, H * 0.95, W * 0.5, H * 0.25))
    return dither(a)


if __name__ == '__main__':
    soft('skyline_dusk', 0.55, glow=(235, 242, 255), gm=0.4).save(f'{O}/soft_skyline.jpg', quality=92)
    soft('city_dusk', 0.5, lift=0.12, glow=(255, 236, 228), gm=0.42).save(f'{O}/soft_citydusk.jpg', quality=92)
    soft('city_night', 0.6, lift=0.08, glow=(200, 225, 255), gm=0.38).save(f'{O}/soft_citynight.jpg', quality=92)
    soft('boardroom', 0.5, rad=18, lift=0.04, gm=0.12, sat=1.0).save(f'{O}/dof_boardroom.jpg', quality=92)
    soft('city_dusk', 0.5, rad=22, lift=0.0, gm=0.0, sat=1.05).save(f'{O}/bokeh_citydusk.jpg', quality=92)
    soft('skyline_dusk', 0.55, rad=48, lift=0.06, gm=0.2).save(f'{O}/blur_skyline.jpg', quality=92)
    horizon().save(f'{O}/horizon_glow.jpg', quality=94)
    studio().save(f'{O}/studio_navy.jpg', quality=94)
