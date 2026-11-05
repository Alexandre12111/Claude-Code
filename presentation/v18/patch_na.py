"""Versions Amérique du Nord de la présentation YouCraft, construites sur la v1.9 :
anglais en langue principale (ouverture toujours en anglais), diapositive de conclusion remise en dernière position,
dollars américains, hébergement aux États-Unis ou au Canada, mentions de conformité et de taxes adaptées.
Usage : MARKET=us python3 patch_na.py  ou  MARKET=ca python3 patch_na.py"""
import json, os, re

HERE = os.path.dirname(os.path.abspath(__file__))
M = os.environ.get('MARKET', 'us')
assert M in ('us', 'ca')
src = open(os.path.join(HERE, '..', 'youcraft-presentation_1_9.html'), encoding='utf-8').read()
up = open(os.path.join(HERE, 'source_upload.html'), encoding='utf-8').read()

# Les images intégrées (base64) sont mises de côté pendant les remplacements.
B64 = []
def stash(m):
    B64.append(m.group(0)); return f'@@B64_{len(B64) - 1}@@'
src = re.sub(r'data:[a-z/+.-]+;base64,[A-Za-z0-9+/=]+', stash, src)


def rep(a, b, mn=1):
    global src
    c = src.count(a)
    assert c >= mn, (c, a[:100])
    src = src.replace(a, b)


US = M == 'us'
WHERE = 'the US' if US else 'Canada'
WHERE_FR = 'au Canada'

# 1. Conclusion remise à la fin (après « Prochaines étapes »).
a = up.index('<section class="slide" id="s13">'); b = up.index('</section>', a) + len('</section>')
closing = re.sub(r'data:[a-z/+.-]+;base64,[A-Za-z0-9+/=]+', stash, up[a:b])
i = src.rindex('</section>', 0, src.index('<nav id="ui"')) + len('</section>')
src = src[:i] + '\n\n<!-- CLOSING -->\n' + closing + src[i:]

# 2. Ouverture en anglais, langues proposées : anglais seul (US) ou anglais et français (Canada).
rep('  var lang = "fr";\n', '  var lang = "en";\n')
rep('    var saved = localStorage.getItem(KEY);\n    if (saved && DICTS[saved]) lang = saved;\n', '    /* Version nord-américaine : ouverture toujours en anglais. */\n')
rep('<html lang="fr">', '<html lang="en">')
keep = ['en'] if US else ['en', 'fr']
for code in ('fr', 'es', 'sv', 'de'):
    if code not in keep:
        src = re.sub(r'\n    \{ code: "%s", native: "[^"]+", tag: "[A-Z]+" \},?' % code, '', src)
src = src.replace('{ code: "en", native: "English", tag: "EN" },\n  ];', '{ code: "en", native: "English", tag: "EN" }\n  ];')
if US:
    src = src.replace('</style>\n<style id="v19-css">', '</style>\n<style>#langswitch{display:none !important}</style>\n<style id="v19-css">', 1)

# 3. Textes : du plus précis au plus général.
FR = {}  # traductions françaises (Canada) des nouveaux textes anglais
def swap(old, new, fr=None, mn=1):
    rep(old, new, mn)
    if fr is not None:
        FR[new] = fr

PRICE_NOTE = 'Prices in US dollars, excl. applicable taxes.'
swap('Reference prices for France, excl. VAT.', PRICE_NOTE)
FR['* 200 credits ≈ 3 simple apps or 1 moderate app per month. Extra credits are available beyond the plan. ' + PRICE_NOTE] = \
    '* 200 crédits ≈ 3 applications simples ou 1 application modérée par mois. Des crédits additionnels sont disponibles au‑delà du plan. Prix en dollars américains, avant taxes applicables.'
swap('Deploy in the EU or on your own infrastructure', f'Deploy in {WHERE} or on your own infrastructure', 'Déployez ' + WHERE_FR + ' ou sur votre propre infrastructure')
swap('GDPR compliant, role-based access, audit logs', 'Role-based access, SSO and audit logs', 'Accès par rôles, SSO et journaux d’audit')
swap('Agent running on every new contract · open-source model hosted in Europe', f'Agent running on every new contract · open-source model hosted in {WHERE}',
     'Agent actif sur chaque nouveau contrat · modèle open source hébergé ' + WHERE_FR)
swap('Data hosted in Europe', f'Data hosted in {WHERE}', 'Données hébergées ' + WHERE_FR)
swap('hosted in Europe', f'hosted in {WHERE}', 'hébergé ' + WHERE_FR)
# Exemples de l'application juridique : réglementations locales.
if US:
    swap('Evidence for ISO 27001, GDPR and Sapin II audits', 'Evidence for ISO 27001, SOC 2 and SOX audits')
    swap('Missing GDPR clause', 'Missing data privacy clause'); swap('GDPR clause required', 'Data privacy clause required')
    swap('Governing law outside the EU', 'Governing law outside the US'); swap('EU and UN lists', 'OFAC and UN lists')
    swap("'GDPR', 'Art. 30'", "'CCPA', '§1798.100'"); swap("'GDPR', 'Art. 33'", "'CCPA', '§1798.150'"); swap("'Sapin II', 'Art. 17'", "'FCPA', '§78dd-1'")
else:
    swap('Evidence for ISO 27001, GDPR and Sapin II audits', 'Evidence for ISO 27001, PIPEDA and CFPOA audits', 'Preuves pour les audits ISO 27001, LPRPDE et LCAPE')
    swap('Missing GDPR clause', 'Missing privacy clause (PIPEDA)', 'Clause de confidentialité (LPRPDE) manquante')
    swap('GDPR clause required', 'Privacy clause (PIPEDA) required', 'Clause de confidentialité (LPRPDE) obligatoire')
    swap('Governing law outside the EU', 'Governing law outside Canada', 'Droit applicable hors du Canada')
    swap('EU and UN lists', 'Canadian and UN sanctions lists', 'Listes de sanctions canadiennes et de l’ONU')
    swap("'GDPR', 'Art. 30'", "'PIPEDA', 'Sch. 1, 4.1'"); swap("'GDPR', 'Art. 33'", "'PIPEDA', 's. 10.1'"); swap("'Sapin II', 'Art. 17'", "'CFPOA', 's. 3'")
# Régions du tableau de bord de direction.
R1, R2 = ('United States', 'Canada') if US else ('Canada', 'United States')
FR['United States'] = 'États‑Unis'; FR['Canada'] = 'Canada'
for a2, b2 in [("fr: ['France', 0.42], eu: ['Rest of Europe', 0.36]", f"fr: ['{R1}', 0.42], eu: ['{R2}', 0.36]"),
               ("items: [['France', .9, eur(820, 0, 'k')], ['Rest of Europe', .62,", f"items: [['{R1}', .9, eur(820, 0, 'k')], ['{R2}', .62,"),
               ("[['France', 49.9, 42, '#1E3BFF'], ['Rest of Europe', 42.8, 36,", f"[['{R1}', 49.9, 42, '#1E3BFF'], ['{R2}', 42.8, 36,")]:
    rep(a2, b2)
# Couverture : marges et comptes par pays deviennent des États (US) ou des provinces (Canada).
G = ['California', 'Texas', 'New York', 'Illinois'] if US else ['Ontario', 'Quebec', 'British Columbia', 'Alberta']
if not US:
    FR.update({'Quebec': 'Québec', 'British Columbia': 'Colombie‑Britannique', 'Ontario': 'Ontario', 'Alberta': 'Alberta'})
rep("var MG = [['France', 33.8], ['Germany', 32.1], ['Spain', 31.2], ['Italy', 28.6]];", "var MG = [['%s', 33.8], ['%s', 32.1], ['%s', 31.2], ['%s', 28.6]];" % tuple(G))
for c0, c1 in zip(['France', 'Germany', 'Spain', 'Italy'], G):
    rep("', '%s', " % c0 + {'France': '18.4', 'Germany': '14.9', 'Spain': '11.2', 'Italy': '9.7'}[c0], "', '%s', " % c1 + {'France': '18.4', 'Germany': '14.9', 'Spain': '11.2', 'Italy': '9.7'}[c0])
# Taxes, devise, orthographe.
swap('excl. VAT', 'excl. taxes')
src = src.replace('€', '$')
src = re.sub(r'(?<=[  ])HT\b', 'avant taxes', src)  # textes français (Canada)
src = src.replace("en: 'en-GB'", "en: 'en-US'" if US else "en: 'en-CA'").replace("fr: 'fr-FR'", "fr: 'fr-CA'")
if US:
    for a2, b2 in [('licences', 'licenses'), ('licence', 'license'), ('Organisation', 'Organization'), ('organisation', 'organization')]:
        src = src.replace(a2, b2)

# 4. Traductions françaises des nouveaux textes (Canada) et étiquette de version.
if not US:
    add = {'fr': FR}
    rep('<script>\n/* language layer (inlined) */',
        '<script>\n/* Canada : textes français */\n(function(){var A=' + json.dumps(add, ensure_ascii=False) + ';for(var l in A){window.__YC_DICTS__[l]=window.__YC_DICTS__[l]||{};for(var k in A[l])window.__YC_DICTS__[l][k]=A[l][k];}})();\n</script>\n<script>\n/* language layer (inlined) */')

src = re.sub(r'@@B64_(\d+)@@', lambda m: B64[int(m.group(1))], src)
out = os.path.join(HERE, '..', f'youcraft-presentation_1_9_{"US" if US else "Canada"}.html')
open(out, 'w', encoding='utf-8').write(src)
print(os.path.abspath(out), round(len(src) / 1024), 'Ko')
