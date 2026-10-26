#!/usr/bin/env python3
"""Construit le fichier de corrections appliqué par le thème (inc/corrections-textes.php)
à partir du document Word relu par le client.

Usage : python3 -I construire-corrections.py modifications.json textes-references.json sortie.json

* modifications.json : sortie de lire-modifications.py ;
* les décisions prises sur les commentaires du client (sections masquées, champs vidés,
  horaires, cohérence des traductions…) sont écrites ci-dessous, une par une, avec leur raison.
"""
import json, re, sys

mods = json.load(open(sys.argv[1], encoding='utf-8'))['modifications']
refs_doc = json.load(open(sys.argv[2], encoding='utf-8'))
REF = {}
PAGE = {}
for p in refs_doc['pages']:
    PAGE[p['slug']] = p
    for s in p['sections']:
        for l in s['lignes']:
            REF[l['ref']] = dict(l, page=p['slug'])

LANGUES = ('de', 'fr', 'en')


def propre(texte, langue):
    """Texte saisi dans Word → texte du site : entités, guillemets typographiques en anglais."""
    t = texte.replace('&amp;', '&').strip()
    if langue == 'en':
        t = re.sub(r'"([^"<>]*)"', '“\\1”', t)
    return t


def petit(texte):
    """Astérisque de bas de réponse en petits caractères (demande du client, A-072 / A-078)."""
    return re.sub(r'<br>\s*(\*[^<]*)$', '<br><small>\\1</small>', texte)


# ------------------------------------------------------------------ décisions
# Changement du client non repris (avec la raison, rappelée dans le rapport).
IGNORER = {
    'H-013': 'La plaque affiche déjà le chiffre 150 : on garde « Jahre Schiesser / ans Schiesser / years of Schiesser » (règle donnée par le client).',
}

# Textes fixés par nos soins (cohérence, horaires dynamiques, traductions à adapter).
FIXES = {
    # Horaires : la phrase suit les Réglages maison (une seule source, jours fériés compris).
    ('A-074', 'de'): 'Wir sind täglich für Sie da: [schiesser_horaires_phrase]. An Feiertagen können die Zeiten abweichen: siehe Besuch, oder rufen Sie uns an.',
    ('A-074', 'fr'): 'Nous vous accueillons tous les jours : [schiesser_horaires_phrase]. Les horaires peuvent varier les jours fériés : voir Nous rendre visite, ou appelez-nous.',
    ('A-074', 'en'): 'We are open every day: [schiesser_horaires_phrase]. Hours may differ on public holidays: see Visit us, or give us a call.',
    ('K-051', 'de'): 'Wir sind täglich für Sie da: [schiesser_horaires_phrase]. Die Vitrine wird jeden Tag frisch bestückt, und oben wartet der Tea Room.',
    ('K-051', 'fr'): 'Nous vous accueillons tous les jours : [schiesser_horaires_phrase]. La vitrine est garnie chaque jour et le Tea Room vous attend à l’étage.',
    ('K-051', 'en'): 'We are open every day: [schiesser_horaires_phrase]. The display case is restocked every day, and the Tea Room awaits you upstairs.',
    # Traductions à adapter : le client a changé le sens dans une ou deux langues seulement.
    ('C-026', 'fr'): 'Glacer, tremper, décorer : l’étape la plus lente, et la plus importante.',
    ('K-040', 'fr'): 'Pour la plupart des gâteaux, quelques jours suffisent ; pour les grosses commandes ou les jours de fête, un peu plus. Appelez-nous, nous vous conseillons volontiers. Vous trouverez notre assortiment de gâteaux, de Läckerli et de pralinés à la Confiserie.',
    # Plus d’emballage personnalisé ni de logo (décision F-023) : la réponse de la FAQ Contact suit.
    ('K-046', 'de'): 'Ja, wir stellen Pralinés- und Läckerli-Boxen für Ihre Kundschaft oder Ihr Team zusammen, in der klassischen Schiesser-Verpackung. Siehe Firmengeschenke.',
    ('K-046', 'fr'): 'Oui, nous composons des boîtes de pralinés et de Läckerli pour vos clients ou votre équipe, dans l’emballage classique Schiesser. Voir Cadeaux d’entreprise.',
    ('K-046', 'en'): 'Yes, we put together praline and Läckerli boxes for your clients or your team, in the classic Schiesser packaging. See Corporate gifts.',
    # « Le plus ancien café de Suisse » retiré partout par le client sauf ici : même formulation que T-020.
    ('T-003', 'de'): 'Durch den Laden, die Treppe hinauf, und das Treiben des Marktplatzes bleibt unten. Hier sitzen Sie in einem der ältesten Kaffeehäuser der Schweiz.',
    ('T-003', 'fr'): 'On traverse la boutique, on monte l’escalier, et l’animation du Marktplatz reste en bas. Vous voici dans l’un des plus anciens cafés de Suisse.',
    ('T-003', 'en'): 'Through the shop, up the stairs, and the bustle of the Marktplatz stays below. Here you are sitting in one of Switzerland’s oldest coffee houses.',
    # Trajet depuis la gare : tram ≈ 8 min + marche ≈ 1 min (commentaires V-023 / V-025).
    ('V-023', 'de'): '9', ('V-023', 'fr'): '9', ('V-023', 'en'): '9',
    ('V-025', 'de'): '1', ('V-025', 'fr'): '1', ('V-025', 'en'): '1',
}
ORIGINE_FIXE = {'A-074': 'horaires', 'K-051': 'horaires', 'C-026': 'traduction', 'K-040': 'traduction', 'K-046': 'coherence', 'T-003': 'coherence', 'V-023': 'commentaire', 'V-025': 'commentaire'}

# Éléments vidés par le client : l’élément entier disparaît (sinon une ligne vide resterait).
SUPPRIMER = {'V-093': 'Rubrique « Sprachen » vide', 'K-033': 'Ligne « Für » vide', 'K-047': 'Question « Onlineshop » vide'}
VIDES_LIES = {'V-094', 'K-034', 'K-048'}  # autres champs du même élément

# Liens retirés volontairement par le client (commentaire A-078 : lien « Firmengeschenke » retiré pour l’instant).
SANS_LIENS = {'A-078'}

# Sections masquées (réglage « afficher à partir du » : rien n’est supprimé, réversible).
MASQUER = {'C-012': 'Grille des produits : en attente des photos et des prix validés', 'V-013': 'Affluence : pas alimentée par les heures d’affluence Google'}

# ------------------------------------------------------------------ textes
textes = []
vu = set()
for m in mods:
    ref = m['ref']
    if ref in IGNORER or ref in SUPPRIMER or ref in VIDES_LIES:
        continue
    for l, nouveau in m['changements'].items():
        if (ref, l) in FIXES:
            continue
        nouveau = propre(nouveau, l)
        if ref in ('A-072', 'A-078'):
            nouveau = petit(nouveau)
        textes.append({'ref': ref, 'langue': l, 'nouveau': nouveau, 'origine': 'client', 'sans_liens': ref in SANS_LIENS})
        vu.add((ref, l))
for (ref, l), nouveau in FIXES.items():
    textes.append({'ref': ref, 'langue': l, 'nouveau': nouveau, 'origine': ORIGINE_FIXE[ref]})

# Guillemets seuls changés (Word) : rien à faire.
def norm(s):
    s = re.sub(r'<br\s*/?>', ' ', s or '')
    s = re.sub(r'<[^>]+>', '', s).replace('&nbsp;', ' ').replace(' ', ' ').replace('&amp;', '&')
    s = re.sub(r'[“”„"«»]', '"', s).replace('’', "'").replace('‘', "'")
    return re.sub(r'\s+', ' ', s).strip()

sortie = []
for t in textes:
    r = REF[t['ref']]
    ancien = r[t['langue']]
    if norm(ancien) == norm(t['nouveau']) and '<small>' not in t['nouveau']:
        continue
    sortie.append({'ref': t['ref'], 'page': r['page'], 'chemin': r['chemin'], 'bloc': r['bloc'], 'cle': r['cle'],
                   'langue': t['langue'], 'ancien': ancien, 'nouveau': t['nouveau'], 'origine': t['origine'],
                   **({'sans_liens': True} if t.get('sans_liens') else {})})


def element(ref, raison):
    r = REF[ref]
    return {'ref': ref, 'page': r['page'], 'chemin': r['chemin'], 'bloc': r['bloc'], 'cle': r['cle'],
            'ancien': {l: r[l] for l in LANGUES}, 'raison': raison}


# ------------------------------------------------------------------ référencement (Rank Math)
SEO_NOUVEAU = {
    ('tea-room', 'de'): ('Tea Room Basel | Kaffeehaus seit 1870 – Confiserie Schiesser',
                         'Tea Room in Basel, eines der ältesten Kaffeehäuser der Schweiz: Kaffee, Tee, hausgemachte heisse Schokolade, Frühstück und Coupes im ersten Stock.',
                         ['Tea Room Basel', 'Kaffeehaus Basel', 'Frühstück Basel Marktplatz', 'historisches Kaffeehaus Basel', 'Café Marktplatz Basel']),
    ('tea-room', 'fr'): ('Tea Room à Bâle | Salon de thé sur le Marktplatz – Schiesser',
                         'Tea Room à Bâle, dans l’un des plus anciens cafés de Suisse : café, thé, chocolat chaud maison, petit-déjeuner et coupes glacées, à l’étage du Marktplatz.',
                         ['Tea Room Bâle', 'salon de thé Bâle', 'petit-déjeuner Bâle Marktplatz', 'café historique Bâle', 'café Bâle']),
    ('tea-room', 'en'): ('Tea Room in Basel | Historic Coffee House on the Marktplatz',
                         'Tea Room in Basel, in one of Switzerland’s oldest coffee houses: coffee, tea, homemade hot chocolate, breakfast and sundaes on the first floor.',
                         ['tea room Basel', 'coffee house Basel', 'breakfast Basel Marktplatz', 'historic coffee house Basel', 'café Marktplatz Basel']),
    ('geschichte', 'de'): (None,
                           'Seit 1870 am Basler Marktplatz: wie Rudolf Schiesser seine Confiserie eröffnete und Hans Schiesser das Haus mit Tea Room und Rathstübli neu bauen liess.',
                           None),
    ('geschichte', 'fr'): (None,
                           'L’histoire de la Confiserie Schiesser à Bâle : ses origines en 1870, la maison du Marktplatz, le Tea Room et le Rathstübli, en photos d’archives.',
                           ['histoire Confiserie Schiesser', 'confiserie Bâle 1870', 'café historique Bâle', 'histoire Marktplatz Bâle', 'Rudolf Schiesser']),
    ('geschichte', 'en'): (None,
                           'The story of Confiserie Schiesser in Basel: its roots in 1870, the house on the Marktplatz, the Tea Room and the Rathstübli, in photos from the archives.',
                           ['Confiserie Schiesser history', 'Basel confectionery 1870', 'historic coffee house Basel', 'Marktplatz Basel history', 'Rudolf Schiesser']),
}
SEO_ANCIEN = json.load(open(sys.argv[4], encoding='utf-8')) if len(sys.argv) > 4 else {}
seo = []
for (page, l), (titre, desc, mots) in SEO_NOUVEAU.items():
    if titre:
        assert 50 <= len(titre) <= 60, (page, l, len(titre), titre)
    assert 140 <= len(desc) <= 160, (page, l, len(desc), desc)
    vieux = SEO_ANCIEN.get(page, {}).get(l, {})
    for champ, valeur in (('rank_math_title', titre), ('rank_math_description', desc), ('rank_math_focus_keyword', ','.join(mots) if mots else None)):
        if valeur and vieux.get(champ):
            seo.append({'page': page, 'langue': l, 'champ': champ, 'ancien': vieux[champ], 'nouveau': valeur})

reglages = [{
    'cle': 'presentation',
    'ancien': 'Die Confiserie Schiesser wurde 1870 am Basler Marktplatz gegründet. Läckerli, Pralinen, Torten und Gebäck entstehen in der eigenen Backstube; im ersten Stock liegt der Tea Room, das älteste Kaffeehaus der Schweiz.',
    'nouveau': 'Die Confiserie Schiesser geht auf das Jahr 1870 zurück. Am Basler Marktplatz entstehen Läckerli, Pralinés, Torten und Gebäck in der eigenen Backstube; im ersten Stock liegt der Tea Room, eines der ältesten Kaffeehäuser der Schweiz.',
}]

resultat = {
    'version': '2026-10-v3',
    'date': '2026-10-20',  # ordre des lots dans « Mises à jour des pages »
    'titre': 'Corrections de textes du client (document v3, octobre 2026)',
    'textes': sortie,
    'supprimer': [element(r, x) for r, x in SUPPRIMER.items()],
    'masquer': [element(r, x) for r, x in MASQUER.items()],
    'comparateur_1889': {'page': 'geschichte', 'fichier': 'archiv-fassade-patisserie-leckerly', 'raison': 'Le comparateur garde la photo de 1889 (décision Schiesser, H-091).'},
    'seo': seo,
    'reglages': reglages,
    'ignores': [{'ref': r, 'raison': x} for r, x in IGNORER.items()],
}
json.dump(resultat, open(sys.argv[3], 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
par_origine = {}
for t in sortie:
    par_origine[t['origine']] = par_origine.get(t['origine'], 0) + 1
print('%d textes (%s), %d éléments supprimés, %d sections masquées, %d réglages Google, %d ignorés'
      % (len(sortie), ', '.join('%s %d' % kv for kv in sorted(par_origine.items())), len(resultat['supprimer']), len(resultat['masquer']), len(seo), len(IGNORER)))
