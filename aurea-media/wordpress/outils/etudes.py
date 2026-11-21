"""Études de cas des réalisations : génère le contenu des fiches (blocs Gutenberg) et leurs réglages SEO.

Sources : la page d'accueil de chaque site client (captures du thème, menus et accroches relevés à l'écran)
et les fiches Aurea Studio. Aucun chiffre de résultat, témoignage ou délai propre au client n'est inventé.

    python3 wordpress/outils/etudes.py
écrit aurea/contenu/etudes/<cle>.html et aurea/contenu/etudes.json
"""
import html
import json
import os
import re
import sys

ICI = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, ICI)
from etudes_donnees import ETUDES  # noqa: E402

THEME = os.path.join(ICI, '..', 'aurea', 'contenu')

SERVICES = {
    'site-vitrine': ('creation-site-vitrine', 'création de site vitrine'),
    'site-ecommerce': ('creation-site-ecommerce', 'création de site e-commerce'),
}


def e(t):
    return html.escape(t, quote=True)


def liens(texte):
    """[texte](cle) → lien vers une page du site : {{url:cle}}, ou realisation:cle pour une autre fiche."""
    def rempl(m):
        lab, cle = m.group(1), m.group(2)
        if cle.startswith('http'):
            return f'<a href="{cle}" target="_blank" rel="noopener">{lab}</a>'
        return f'<a href="{{{{url:{cle}}}}}">{lab}</a>'
    return re.sub(r'\[([^\]]+)\]\(([^)]+)\)', rempl, texte)


def p(t, classe=''):
    c = f' class="{classe}"' if classe else ''
    attr = f' {{"className":"{classe}"}}' if classe else ''
    return f'<!-- wp:paragraph{attr} -->\n<p{c}>{liens(t)}</p>\n<!-- /wp:paragraph -->'


def h(t, niveau=2):
    attr = '' if niveau == 2 else f' {{"level":{niveau}}}'
    return f'<!-- wp:heading{attr} -->\n<h{niveau} class="wp-block-heading">{e(t)}</h{niveau}>\n<!-- /wp:heading -->'


def ul(items):
    li = ''.join(f'<!-- wp:list-item -->\n<li>{liens(x)}</li>\n<!-- /wp:list-item -->' for x in items)
    return f'<!-- wp:list -->\n<ul>{li}</ul>\n<!-- /wp:list -->'


def bref(question, reponse):
    return ('<!-- wp:group {"className":"en-bref","layout":{"type":"constrained"}} -->\n<div class="wp-block-group en-bref">'
            '<!-- wp:heading {"className":"en-bref-titre"} -->\n'
            f'<h2 class="wp-block-heading en-bref-titre">{e(question)}</h2>\n<!-- /wp:heading -->\n\n{p(reponse)}</div>\n<!-- /wp:group -->')


def fiche_tableau(d):
    lignes = [
        ('Client', d['nom']),
        ('Activité', d['activite']),
        ('Lieu', d['lieu']),
        ('Type de projet', d['type_nom']),
        ('Fonctionnalités clés', d['fonctions']),
        ('Site en ligne', f'<a href="{d["url"]}" target="_blank" rel="noopener">{e(d["domaine"])}</a>'),
    ]
    corps = ''.join(f'<tr><th scope="row">{e(a)}</th><td>{b if a == "Site en ligne" else e(b)}</td></tr>' for a, b in lignes)
    return ('<!-- wp:table {"className":"tableau etude-fiche"} -->\n'
            f'<figure class="wp-block-table tableau etude-fiche"><table><tbody>{corps}</tbody></table>'
            f'<figcaption class="wp-element-caption">Fiche projet : {e(d["nom"])}</figcaption></figure>\n<!-- /wp:table -->')


def arbo(d):
    """Arborescence relevée dans le menu du site."""
    items = ''
    for x in d['menu']:
        if isinstance(x, tuple):
            nom, enfants = x
            sous = ''.join(f'<li>{e(c)}</li>' for c in enfants)
            items += f'<li><span>{e(nom)}</span><ul>{sous}</ul></li>'
        else:
            items += f'<li><span>{e(x)}</span></li>'
    cta = f'<p class="arbo-cta"><span>Appel à l’action permanent</span><b>{e(d["cta"])}</b></p>' if d.get('cta') else ''
    label = f'Arborescence du site {d["nom"]}'
    return ('<!-- wp:html -->\n'
            f'<figure class="etude-graph etude-arbo" role="img" aria-label="{e(label)}">'
            f'<div class="arbo-racine">{e(d.get("racine", "Accueil"))}</div><ul class="arbo-niveau">{items}</ul>{cta}'
            f'<figcaption>Arborescence du menu principal de {e(d["domaine"])}</figcaption></figure>\n<!-- /wp:html -->')


def palette(d):
    cases = ''.join(f'<div class="pal" style="--c:{c}"><i></i><span>{e(role)}</span></div>' for role, c in d['palette'])
    polices = f'<p class="pal-typo"><span>Typographie</span>{e(d["typo"])}</p>' if d.get('typo') else ''
    return ('<!-- wp:html -->\n'
            f'<figure class="etude-graph etude-palette" role="img" aria-label="{e("Palette et typographie du site " + d["nom"])}">'
            f'<div class="pal-cases">{cases}</div>{polices}'
            f'<figcaption>Palette et typographie, relevées sur la page d’accueil</figcaption></figure>\n<!-- /wp:html -->')


def parcours(d):
    etapes = ''.join(f'<li><b>{i}</b><span>{e(x)}</span></li>' for i, x in enumerate(d['parcours'], 1))
    return ('<!-- wp:html -->\n'
            f'<figure class="etude-graph etude-parcours" role="img" aria-label="{e("Parcours du visiteur sur le site " + d["nom"])}">'
            f'<ol>{etapes}</ol><figcaption>Le parcours du visiteur, de l’arrivée sur le site à la prise de contact</figcaption></figure>\n<!-- /wp:html -->')


def faq(items):
    blocs = ''
    for q, r in items:
        blocs += (f'<!-- wp:details {{"className":"faq-bloc"}} -->\n<details class="wp-block-details faq-bloc"><summary>{e(q)}</summary>'
                  f'{p(r)}</details>\n<!-- /wp:details -->\n\n')
    return h('Questions fréquentes') + '\n\n' + blocs


def appel(d):
    service, nom_service = SERVICES[d['type']]
    return ('<!-- wp:group {"className":"appel-bloc","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->\n'
            '<div class="wp-block-group appel-bloc"><!-- wp:paragraph -->\n'
            f'<p><strong>{e(d["appel"])}</strong><br>Devis gratuit et détaillé sous 24 h, sans engagement.</p>\n<!-- /wp:paragraph -->\n\n'
            '<!-- wp:buttons -->\n<div class="wp-block-buttons"><!-- wp:button -->\n'
            '<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="{{url:contact}}">Demander mon devis</a></div>\n'
            '<!-- /wp:button --></div>\n<!-- /wp:buttons --></div>\n<!-- /wp:group -->')


def rendre(d):
    b = [p(d['intro'], 'intro'), bref(d['bref'][0], d['bref'][1]), fiche_tableau(d)]
    b += [h(d['client'][0]), *[p(x) for x in d['client'][1]]]
    b += [h(d['enjeux'][0]), p(d['enjeux'][1]), ul(d['enjeux'][2])]
    b += [h('Ce que nous avons conçu'), p(d['concu'])]
    b += [h('Une arborescence courte et lisible', 3), p(d['arbo_texte']), arbo(d)]
    b += [h('Une direction artistique au service de la marque', 3), p(d['da_texte']), palette(d)]
    b += [h('Un parcours pensé pour la prise de contact', 3), p(d['parcours_texte']), parcours(d)]
    b += [h(d['points'][0]), ul(d['points'][1])]
    b += [h(d['conseil'][0]), *[p(x) for x in d['conseil'][1]]]
    b += [faq(d['faq']), appel(d)]
    return '\n\n'.join(b) + '\n'


def mots(texte):
    return len(re.findall(r'\w+', re.sub(r'<[^>]+>', ' ', texte)))


def main():
    os.makedirs(os.path.join(THEME, 'etudes'), exist_ok=True)
    reglages = {}
    alertes = []
    for d in ETUDES:
        d.setdefault('domaine', re.sub(r'^https?://(www\.)?|/$', '', d['url']))
        d.setdefault('type_nom', 'Site e-commerce' if d['type'] == 'site-ecommerce' else 'Site vitrine')
        contenu = rendre(d)
        if ' - ' in contenu or ' – ' in contenu or ' — ' in contenu:
            alertes.append(f'{d["cle"]} : tiret au milieu d’une phrase')
        with open(os.path.join(THEME, 'etudes', d['cle'] + '.html'), 'w') as f:
            f.write(contenu)
        reglages[d['cle']] = {
            'mot_cle': d['mot_cle'], 'titre': d['titre_seo'], 'description': d['description'],
            'extrait': d['description'], 'lieu': d['lieu'], 'accroche': d.get('accroche', ''),
        }
        n, lt, ld = mots(contenu), len(d['titre_seo']), len(d['description'])
        if not 50 <= lt <= 62:
            alertes.append(f'{d["cle"]} : titre SEO de {lt} caractères')
        if not 135 <= ld <= 162:
            alertes.append(f'{d["cle"]} : description de {ld} caractères')
        for zone in ('titre_seo', 'description'):
            if d['mot_cle'].lower() not in d[zone].lower():
                alertes.append(f'{d["cle"]} : mot clé absent de {zone}')
        if d['mot_cle'].lower() not in d['intro'].lower():
            alertes.append(f'{d["cle"]} : mot clé absent de l’introduction')
        print(f'{d["cle"]:20} {n:4} mots  titre {lt}  meta {ld}')
    with open(os.path.join(THEME, 'etudes.json'), 'w') as f:
        json.dump(reglages, f, ensure_ascii=False, indent=1)
    for a in alertes:
        print('ALERTE', a)


if __name__ == '__main__':
    main()
