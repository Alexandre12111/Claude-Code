"""Versions Amérique du Nord (Canada, États-Unis) en FR et EN : hébergement local au lieu de l'Europe,
montants en dollars. Timing V9 (54,65 s) pour la version musique seule.
Usage : python3 make_na.py"""
import os, shutil

ROOT = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..')
FR = {
    'ca': ('Hébergé au Canada', 'Vos données restent au Canada.', 'Marge Ouest canadien'),
    'us': ('Hébergé aux États-Unis', 'Vos données restent aux États-Unis.', 'Marge côte Ouest'),
}
EN = {
    'ca': ('Hosted in Canada', 'Your data stays in Canada.', 'Western Canada margin'),
    'us': ('Hosted in the United States', 'Your data stays in the United States.', 'West Coast margin'),
}


def build(src, dst, reps):
    d = os.path.join(ROOT, dst)
    shutil.rmtree(d, ignore_errors=True)
    shutil.copytree(os.path.join(ROOT, src), d)
    shutil.copy(os.path.join(ROOT, 'src6', 'timeline.js'), os.path.join(d, 'timeline.js'))
    for f, a, b in reps:
        p = os.path.join(d, f)
        s = open(p, encoding='utf-8').read()
        assert a in s, (dst, f, a)
        open(p, 'w', encoding='utf-8').write(s.replace(a, b))
    print(dst, 'ok')


for m, (t, sub, marge) in FR.items():
    build('src6', f'src_{m}fr', [
        ('p11_serenite.js', "[41.1, 'eu', 'Hébergé en Europe', 'Vos données restent dans l’Union européenne.']", f"[41.1, 'map-pin-check', '{t}', '{sub}']"),
        ('p11_serenite.js', 'M€', 'M$'),
        ('p06_demo.js', 'Marge Europe du Sud', marge),
        ('p06_demo.js', 'M€', 'M$'),
    ])
for m, (t, sub, marge) in EN.items():
    build('src7', f'src_{m}en', [
        ('p11_serenite.js', "[41.1, 'eu', 'Hosted in Europe', 'Your data stays in the European Union.']", f"[41.1, 'map-pin-check', '{t}', '{sub}']"),
        ('p11_serenite.js', '€', '$'),
        ('p06_demo.js', 'Southern Europe margin', marge),
        ('p06_demo.js', '€', '$'),
    ])
