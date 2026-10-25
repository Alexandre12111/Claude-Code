"""Articles du blog « Conseils » : génère le contenu (blocs Gutenberg) et les réglages SEO.

    python3 wordpress/outils/articles.py
écrit aurea/contenu/articles/<slug>.html et aurea/contenu/articles.json (articles livrés avec le thème)

    python3 wordpress/outils/articles.py --pack pack-2026-11 slug:2026-11-03T09:00 autre-slug:2026-11-10T09:00
fabrique wordpress/packs/pack-2026-11.zip, à importer dans Aurea Studio → Importer des articles.
Une date future programme l'article ; sans date, il est publié à l'import.

Images : photos Unsplash (licence Unsplash, usage commercial libre) copiées dans aurea/assets/conseils/.
Dans le contenu, {{img_id:…}} et {{img_url:…}} sont remplacés à l'installation par l'image importée
dans la médiathèque (inc/articles-livres.php).
"""
import json
import os
import re
import sys

ICI = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, ICI)
from etudes import e, liens, p, h, ul, bref  # noqa: E402
from articles_lot1 import LOT1  # noqa: E402
from articles_lot2 import LOT2  # noqa: E402
from articles_pack1 import PACK1  # noqa: E402

ARTICLES = LOT1 + LOT2      # livrés avec le thème
PACKS = PACK1               # livrés en packs programmés
THEME = os.path.join(ICI, '..', 'aurea')


def ol(items):
    li = ''.join(f'<!-- wp:list-item -->\n<li>{liens(x)}</li>\n<!-- /wp:list-item -->' for x in items)
    return f'<!-- wp:list {{"ordered":true}} -->\n<ol>{li}</ol>\n<!-- /wp:list -->'


def tableau(entete, lignes, legende=''):
    th = ''.join(f'<th>{e(x)}</th>' for x in entete)
    tr = ''.join('<tr>' + ''.join(f'<td>{liens(c)}</td>' for c in l) + '</tr>' for l in lignes)
    cap = f'<figcaption class="wp-element-caption">{e(legende)}</figcaption>' if legende else ''
    return ('<!-- wp:table {"className":"tableau"} -->\n'
            f'<figure class="wp-block-table tableau"><table><thead><tr>{th}</tr></thead><tbody>{tr}</tbody></table>{cap}</figure>\n<!-- /wp:table -->')


def encart(titre, texte):
    return ('<!-- wp:group {"className":"encart-bloc","layout":{"type":"constrained"}} -->\n<div class="wp-block-group encart-bloc">'
            f'{p("<strong>" + e(titre) + "</strong> " + texte)}</div>\n<!-- /wp:group -->')


def image(fichier, legende):
    f = 'conseils/' + fichier
    return ('<!-- wp:image {"id":{{img_id:' + f + '}},"sizeSlug":"large","linkDestination":"none"} -->\n'
            '<figure class="wp-block-image size-large"><img src="{{img_url:' + f + '}}" alt="{{img_alt:' + f + '}}" class="wp-image-{{img_id:' + f + '}}"/>'
            f'<figcaption class="wp-element-caption">{e(legende)} · Photo Unsplash</figcaption></figure>\n<!-- /wp:image -->')


def faq(items):
    out = h('Questions fréquentes') + '\n\n'
    for q, r in items:
        out += (f'<!-- wp:details {{"className":"faq-bloc"}} -->\n<details class="wp-block-details faq-bloc"><summary>{e(q)}</summary>'
                f'{p(r)}</details>\n<!-- /wp:details -->\n\n')
    return out


def appel(titre, texte, bouton, cible):
    return ('<!-- wp:group {"className":"appel-bloc","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->\n'
            '<div class="wp-block-group appel-bloc"><!-- wp:paragraph -->\n'
            f'<p><strong>{e(titre)}</strong><br>{e(texte)}</p>\n<!-- /wp:paragraph -->\n\n'
            '<!-- wp:buttons -->\n<div class="wp-block-buttons"><!-- wp:button -->\n'
            f'<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="{{{{url:{cible}}}}}">{e(bouton)}</a></div>\n'
            '<!-- /wp:button --></div>\n<!-- /wp:buttons --></div>\n<!-- /wp:group -->')


def sources(items):
    li = [f'[{lab}]({url})' for lab, url in items]
    return h('Sources et liens utiles', 3) + '\n\n' + ul(li)


def bloc(b):
    t = b[0]
    if t == 'p':
        return p(b[1])
    if t == 'h3':
        return h(b[1], 3)
    if t == 'ul':
        return ul(b[1])
    if t == 'ol':
        return ol(b[1])
    if t == 'table':
        return tableau(*b[1:])
    if t == 'encart':
        return encart(b[1], b[2])
    if t == 'img':
        return image(b[1], b[2])
    raise ValueError(t)


def rendre(a):
    out = [p(a['intro'], 'intro'), bref(a['bref'][0], a['bref'][1])]
    for titre, blocs in a['sections']:
        out.append(h(titre))
        out += [bloc(b) for b in blocs]
    out.append(faq(a['faq']))
    out.append(appel(*a['appel']))
    if a.get('sources'):
        out.append(sources(a['sources']))
    return '\n\n'.join(out) + '\n'


def mots(c):
    return len(re.findall(r'\w+', re.sub(r'<[^>]+>|\{\{[^}]+\}\}', ' ', c)))


def main():
    os.makedirs(os.path.join(THEME, 'contenu', 'articles'), exist_ok=True)
    reglages, alertes = {}, []
    for a in ARTICLES:
        c = rendre(a)
        for tiret in (' - ', ' – ', ' — '):
            if tiret in c or tiret in a['titre_seo'] or tiret in a['description']:
                alertes.append(f'{a["slug"]} : tiret au milieu d’une phrase')
        imgs = {('conseils/' + a['image'][0]): a['image'][1]}
        for s in a['sections']:
            for b in s[1]:
                if b[0] == 'img':
                    imgs['conseils/' + b[1]] = b[3]
        for f in imgs:
            if not os.path.exists(os.path.join(THEME, 'assets', f)):
                alertes.append(f'{a["slug"]} : image manquante {f}')
        with open(os.path.join(THEME, 'contenu', 'articles', a['slug'] + '.html'), 'w') as fh:
            fh.write(c)
        reglages[a['slug']] = {
            'titre': a['titre'], 'categorie': a['categorie'], 'mot_cle': a['mot_cle'],
            'titre_seo': a['titre_seo'], 'description': a['description'], 'extrait': a['extrait'],
            'image': 'conseils/' + a['image'][0], 'images': imgs, 'ordre': a['ordre'],
        }
        lt, ld, n = len(a['titre_seo']), len(a['description']), mots(c)
        mc = a['mot_cle'].lower()
        if not 50 <= lt <= 62:
            alertes.append(f'{a["slug"]} : titre SEO de {lt} caractères')
        if not 135 <= ld <= 162:
            alertes.append(f'{a["slug"]} : description de {ld} caractères')
        for zone in ('titre_seo', 'description', 'intro', 'titre'):
            if mc not in a[zone].lower():
                alertes.append(f'{a["slug"]} : mot clé absent de {zone}')
        print(f'{a["slug"]:32} {n:5} mots  titre {lt}  meta {ld}  images {len(imgs)}')
    with open(os.path.join(THEME, 'contenu', 'articles.json'), 'w') as fh:
        json.dump(reglages, fh, ensure_ascii=False, indent=1)
    for x in alertes:
        print('ALERTE', x)


def verifier(a, c):
    alertes = []
    for tiret in (' - ', ' – ', ' — '):
        if tiret in c or tiret in a['titre_seo'] or tiret in a['description']:
            alertes.append(f'{a["slug"]} : tiret au milieu d’une phrase')
    lt, ld = len(a['titre_seo']), len(a['description'])
    if not 50 <= lt <= 62:
        alertes.append(f'{a["slug"]} : titre SEO de {lt} caractères')
    if not 135 <= ld <= 162:
        alertes.append(f'{a["slug"]} : description de {ld} caractères')
    for zone in ('titre_seo', 'description', 'intro', 'titre'):
        if a['mot_cle'].lower() not in a[zone].lower():
            alertes.append(f'{a["slug"]} : mot clé absent de {zone}')
    if mots(c) < 1200:
        alertes.append(f'{a["slug"]} : {mots(c)} mots seulement')
    return alertes


def pack(nom, choix):
    """Pack d'articles autonome : articles.json, articles/<slug>.html, images/<fichier>."""
    import zipfile
    par_slug = {a['slug']: a for a in ARTICLES + PACKS}
    alertes = []
    sortie = os.path.join(ICI, '..', 'packs')
    os.makedirs(sortie, exist_ok=True)
    chemin = os.path.join(sortie, nom + '.zip')
    reglages = {}
    with zipfile.ZipFile(chemin, 'w', zipfile.ZIP_DEFLATED) as z:
        for i, item in enumerate(choix, 1):
            slug, _, date = item.partition(':')
            a = par_slug[slug]
            imgs = {'conseils/' + a['image'][0]: a['image'][1]}
            for s_ in a['sections']:
                for b in s_[1]:
                    if b[0] == 'img':
                        imgs['conseils/' + b[1]] = b[3]
            for f in imgs:
                z.write(os.path.join(THEME, 'assets', f), 'images/' + os.path.basename(f))
            c = rendre(a)
            alertes += verifier(a, c)
            for f in imgs:
                if not os.path.exists(os.path.join(THEME, 'assets', f)):
                    alertes.append(f'{slug} : image manquante {f}')
            print(f'  {slug:32} {mots(c):5} mots  titre {len(a["titre_seo"])}  meta {len(a["description"])}  {date or "publié à l’import"}')
            z.writestr('articles/' + slug + '.html', c)
            reglages[slug] = {
                'titre': a['titre'], 'categorie': a['categorie'], 'mot_cle': a['mot_cle'],
                'titre_seo': a['titre_seo'], 'description': a['description'], 'extrait': a['extrait'],
                'image': 'conseils/' + a['image'][0], 'images': imgs, 'ordre': i, 'date': date.replace('T', ' '),
            }
        z.writestr('articles.json', json.dumps(reglages, ensure_ascii=False, indent=1))
    print(f'{chemin} : {len(reglages)} articles, {os.path.getsize(chemin) // 1024} Ko')
    for x in alertes:
        print('ALERTE', x)


if __name__ == '__main__':
    if len(sys.argv) > 2 and sys.argv[1] == '--pack':
        pack(sys.argv[2], sys.argv[3:])
    else:
        main()
