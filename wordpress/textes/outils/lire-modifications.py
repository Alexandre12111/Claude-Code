#!/usr/bin/env python3
"""Relit le document Word rempli par le client et liste les textes modifiés.

Usage : python3 -I lire-modifications.py document-rempli.docx textes-references.json

Deux formes de document sont reconnues :
* document des pages (colonnes « Deutsch », « Français », « English ») : le client corrige
  directement dans la case de la langue ; chaque case est comparée au texte d'origine ;
* document complet (colonne « Modification ») : la demande écrite dans cette colonne.

Sortie : la liste des modifications, puis « document-rempli-modifications.json » à côté du
document. L'italique saisi dans Word devient <em>…</em>, un retour à la ligne <br>, comme sur le site.
"""
import html, json, re, sys, zipfile
from xml.etree import ElementTree as ET

W = '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}'
LANGUES = {'Deutsch': 'de', 'Français': 'fr', 'English': 'en'}


def texte_cellule(tc):
    paragraphes = []
    for p in tc.iter(W + 'p'):
        morceaux = []
        for r in p.iter(W + 'r'):
            rpr = r.find(W + 'rPr')
            i = rpr.find(W + 'i') if rpr is not None else None
            ital = i is not None and i.get(W + 'val', 'true') not in ('0', 'false')
            t = ''.join(html.escape(x.text or '', quote=False) if x.tag == W + 't' else ('<br>' if x.tag in (W + 'br', W + 'cr') else '') for x in r)
            if t:
                morceaux.append(('<em>%s</em>' % t) if ital and t.strip() else t)
        paragraphes.append(''.join(morceaux).replace('</em><em>', ''))
    return '<br>'.join(x for x in paragraphes if x.strip()).strip()


def normal(s):
    """Forme de comparaison : italique gardé, espaces et retours à la ligne neutralisés."""
    s = re.sub(r'<br\s*/?>', ' ', s or '')
    s = re.sub(r'<(/?)em>', lambda m: '*', s)
    s = re.sub(r'<[^>]+>', '', s)
    s = html.unescape(s).replace(' ', ' ').replace('’', "'").replace('‘', "'")
    s = s.replace('*  *', ' ').replace('**', '')
    return re.sub(r'\s+', ' ', s).strip()


def lire(chemin, refs):
    with zipfile.ZipFile(chemin) as z:
        racine = ET.fromstring(z.read('word/document.xml'))
    modifs, autres = [], []
    for tbl in racine.iter(W + 'tbl'):
        lignes = tbl.findall(W + 'tr')
        if not lignes:
            continue
        entete = [re.sub(r'<[^>]+>', '', texte_cellule(c)) for c in lignes[0].findall(W + 'tc')]
        for tr in lignes[1:]:
            cellules = [texte_cellule(c) for c in tr.findall(W + 'tc')]
            if not cellules:
                continue
            ref = re.sub(r'<[^>]+>', '', cellules[0])
            if entete and entete[0] == 'N°' and len(cellules) >= 3 and cellules[2]:
                autres.append({'page_ref': cellules[1], 'demande': cellules[2]})
            elif re.match(r'^[A-Z]-\d{3}$', ref) and 'Modification' not in entete:
                # Correction directe dans la case de chaque langue.
                origine = refs.get(ref)
                if not origine:
                    continue
                changees = {}
                for nom, l in LANGUES.items():
                    if nom in entete and entete.index(nom) < len(cellules):
                        nouveau = cellules[entete.index(nom)]
                        if normal(nouveau) != normal(origine.get(l, '')):
                            changees[l] = nouveau
                if changees:
                    modifs.append({'ref': ref, 'cible': origine, 'changements': changees,
                                   'a_adapter': [l for l in LANGUES.values() if l not in changees]})
            elif re.match(r'^[A-Z]-\d{3}$', ref) and entete[-1] == 'Modification' and cellules[-1]:
                modifs.append({'ref': ref, 'cible': refs.get(ref), 'changements': {'toutes': cellules[-1]}, 'a_adapter': []})
    return modifs, autres


if __name__ == '__main__':
    if len(sys.argv) < 3:
        sys.exit(__doc__)
    d = json.load(open(sys.argv[2], encoding='utf-8'))
    refs = {}
    for pg in d['pages']:
        for s in pg['sections']:
            for l in s['lignes']:
                refs[l['ref']] = {'page': pg['slug'], 'chemin': l['chemin'], 'bloc': l['bloc'], 'cle': l['cle'],
                                  'de': l['de'], 'fr': l['fr'], 'en': l.get('en', '')}
    modifs, autres = lire(sys.argv[1], refs)
    for m in modifs:
        print('%s · %s' % (m['ref'], (m['cible'] or {}).get('cle', '')))
        for l, texte in m['changements'].items():
            avant = normal((m['cible'] or {}).get(l, ''))[:90] if l != 'toutes' else ''
            print('   %s : %s\n        -> %s' % (l.upper(), avant, texte))
        if m['a_adapter']:
            print('   à adapter : %s' % ', '.join(x.upper() for x in m['a_adapter']))
    for a in autres:
        print('AUTRE | %s | %s' % (a['page_ref'], a['demande']))
    sortie = re.sub(r'\.docx$', '', sys.argv[1]) + '-modifications.json'
    json.dump({'modifications': modifs, 'autres': autres}, open(sortie, 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
    print('\n%d textes modifiés, %d autres demandes -> %s' % (len(modifs), len(autres), sortie))
