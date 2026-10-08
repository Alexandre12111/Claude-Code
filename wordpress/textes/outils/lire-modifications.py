#!/usr/bin/env python3
"""Relit le document Word rempli par le client : liste des modifications par référence.

Usage : python3 -I lire-modifications.py document-rempli.docx [textes-references.json]
Sortie : une ligne par modification (référence, texte actuel allemand, demande du client),
puis un fichier JSON « modifications.json » à côté du document.
L'italique saisi dans Word est rendu par <em>…</em>, comme sur le site.
"""
import json, re, sys, zipfile
from xml.etree import ElementTree as ET

W = '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}'

def texte_cellule(tc):
    paragraphes = []
    for p in tc.iter(W + 'p'):
        morceaux = []
        for r in p.iter(W + 'r'):
            rpr = r.find(W + 'rPr')
            ital = rpr is not None and rpr.find(W + 'i') is not None and rpr.find(W + 'i').get(W + 'val', 'true') not in ('0', 'false')
            t = ''.join((x.text or '') if x.tag == W + 't' else ('<br>' if x.tag in (W + 'br', W + 'cr') else '') for x in r)
            if t:
                morceaux.append(('<em>%s</em>' % t) if ital and t.strip() else t)
        paragraphes.append(''.join(morceaux).replace('</em><em>', ''))
    return '<br>'.join(x for x in paragraphes if x.strip()).strip()

def lire(chemin):
    with zipfile.ZipFile(chemin) as z:
        racine = ET.fromstring(z.read('word/document.xml'))
    modifs, autres = [], []
    for tbl in racine.iter(W + 'tbl'):
        lignes = tbl.findall(W + 'tr')
        if not lignes:
            continue
        entete = [texte_cellule(c) for c in lignes[0].findall(W + 'tc')]
        for tr in lignes[1:]:
            cellules = [texte_cellule(c) for c in tr.findall(W + 'tc')]
            if not cellules:
                continue
            if entete and entete[0] == 'N°' and len(cellules) >= 3 and cellules[2]:
                autres.append({'page_ref': cellules[1], 'demande': cellules[2]})
            elif re.match(r'^[A-Z]-\d{3}$', cellules[0]) and entete[-1] == 'Modification' and cellules[-1]:
                col = lambda nom: cellules[entete.index(nom)] if nom in entete and entete.index(nom) < len(cellules) else ''
                demande = cellules[-1]
                langue = re.match(r'^\s*(DE|FR|EN)\s*:\s*', demande, re.I)  # « EN : … » = correction de cette langue seulement
                modifs.append({'ref': cellules[0], 'actuel_de': col('Deutsch (actuel)'), 'actuel_fr': col('Français (actuel)'), 'actuel_en': col('English (actuel)'),
                               'langue': langue.group(1).lower() if langue else 'toutes', 'modification': demande[langue.end():] if langue else demande})
    return modifs, autres

if __name__ == '__main__':
    modifs, autres = lire(sys.argv[1])
    refs = {}
    if len(sys.argv) > 2:
        d = json.load(open(sys.argv[2], encoding='utf-8'))
        for pg in d['pages']:
            for s in pg['sections']:
                for l in s['lignes']:
                    refs[l['ref']] = {'page': pg['slug'], 'chemin': l['chemin'], 'bloc': l['bloc'], 'cle': l['cle'], 'de': l['de'], 'fr': l['fr'], 'en': l.get('en', '')}
    for m in modifs:
        m.update({'cible': refs.get(m['ref'])} if refs else {})
        print('%s [%s] | %s\n      -> %s' % (m['ref'], m['langue'], re.sub('<[^>]+>', '', m['actuel_de'])[:80], m['modification']))
    for a in autres:
        print('AUTRE | %s | %s' % (a['page_ref'], a['demande']))
    sortie = re.sub(r'\.docx$', '', sys.argv[1]) + '-modifications.json'
    json.dump({'modifications': modifs, 'autres': autres}, open(sortie, 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
    print('\n%d modifications, %d autres demandes -> %s' % (len(modifs), len(autres), sortie))
