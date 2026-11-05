"""Corrections v1.8 sur la présentation fournie (source_upload.html) :
1) Chiffres clés : 800+ utilisateurs, 500+ applications déployées, rapportés aux 3 000 collaborateurs Leyton.
2) Bénéfices : suppression du « 10x » (remplacé par « Des heures, pas des mois »).
3) Tarifs : reprise du modèle économique de la source (one-pager YouCraft : licence builder, utilisateurs d'application,
   crédits IA, engagement), sans la mention de frais supplémentaires.
Usage : python3 patch_v18.py  ->  ../youcraft-presentation_1_8.html"""
import json, os

HERE = os.path.dirname(os.path.abspath(__file__))
src = open(os.path.join(HERE, 'source_upload.html'), encoding='utf-8').read()


def rep(a, b, n=1):
    global src
    c = src.count(a)
    assert c == n, (c, a[:100])
    src = src.replace(a, b)


# 1. Chiffres clés.
rep('<div class="n" data-count="750" data-suffix="+">750+</div><span>Users</span>',
    '<div class="n" data-count="800" data-suffix="+">800+</div><span>Users at Leyton</span>')
rep('<div class="n" data-count="350" data-suffix="+">350+</div><span>Applications deployed</span></div></div>',
    '<div class="n" data-count="500" data-suffix="+">500+</div><span>Applications deployed</span></div></div>'
    '<div class="kf-ad"><div class="kf-bar"><i></i></div><p><b>800+</b> of the group’s <b>3,000</b> employees already use YouCraft</p></div>')
rep('<em>350+ applications deployed</em>', '<em>500+ applications deployed</em>')

# 2. Bénéfices sans « 10x ».
rep('<div class="bn-big"><span data-count="10" data-suffix="x">10x</span></div>\n        <b class="bn-t">faster from idea to value</b>\n        <p>From business idea to working solution in hours, not months</p>',
    '<div class="bn-big bn-hm">Hours,<br><span>not months.</span></div>\n        <b class="bn-t">From business idea to working solution</b>')

# 3. Tarifs repris de la source.
PL = [
    ('', 'Build', 'Starter', '€25', 'per builder / month', '100 AI credits / month', 'Up to 5, without authentication', 'Quarterly', ['Public apps only', 'Up to 10 apps and live previews']),
    (' hi', 'Control', 'Business', '€50', 'per builder / month', '300 AI credits / month', '10 included, then €10 / user', 'Quarterly', ['Private apps and SSO', 'No training on your data']),
    (' sv', 'Protect', 'Sovereign', '€100', 'per builder / month', '500 AI credits / month', '10 included, then €10 / user', 'Quarterly', ['Sovereign open-source LLM', 'Regional data residency']),
    (' en', 'Scale', 'Enterprise', 'Contract', 'from €12K per year', 'Pooled AI credits', 'Volume pricing', 'Annual, from €12K', ['Several teams, central governance', 'Any LLM, including sovereign']),
]
cards = []
for i, (cls, k, n, p, unit, cr, users, eng, feats) in enumerate(PL):
    flag = '<div class="pl-flag">Most popular</div>' if cls == ' hi' else ''
    cards.append(f'      <div class="pl2 rise{cls}" style="--d:{.15 + i * .15:.2f}s">{flag}<div class="pl-top"><span class="pl-k">{k}</span><h3>{n}</h3></div>'
                 f'<div class="pl-p"><b>{p}</b><span>{unit}</span></div><div class="pl-c"><svg class="ic"><use href="#i-spark"/></svg>{cr}</div>'
                 f'<dl class="pl-f"><div><dt>App users</dt><dd>{users}</dd></div><div><dt>Commitment</dt><dd>{eng}</dd></div></dl>'
                 f'<ul>{"".join(f"<li>{x}</li>" for x in feats)}</ul></div>')
a = src.index('<div class="pr-h">'); b = src.index('</section>', a)
src = src[:a] + ('<div class="pr-h"><h2 class="title rise">Simple, transparent pricing</h2><p class="lead rise" style="--d:.1s">Per builder licence and per month, excl. VAT</p></div>\n'
                 '    <div class="plans2">\n' + '\n'.join(cards) + '\n    </div>\n'
                 '    <div class="fine">All prices excl. VAT. Quarterly commitment for Starter, Business and Sovereign. Beyond €12K per year, Enterprise applies.</div>\n  </div>\n') + src[b:]

# 4. Version FR de travail : dernière diapositive (« Let's build what's next ») retirée.
#    Version EN (EN=1) : dernière diapositive conservée, ouverture toujours en anglais.
EN = os.environ.get('EN') == '1'
if not EN:
    a = src.index('<section class="slide" id="s13">'); b = src.index('</section>', a) + len('</section>')
    src = src[:a] + src[b:]
else:
    rep('    var saved = localStorage.getItem(KEY);\n    if (saved && DICTS[saved]) lang = saved;\n', '    /* Version anglaise : la présentation s\'ouvre toujours en anglais (le sélecteur reste disponible). */\n')

CSS = '''
/* v1.8 */
.kf-ad{position:relative;z-index:1;margin-top:26px;padding-top:22px;border-top:1px solid rgba(245,242,234,.22)}
.kf-bar{height:12px;border-radius:6px;background:rgba(11,11,15,.28);overflow:hidden}
.kf-bar i{display:block;height:100%;width:26.7%;border-radius:6px;background:#D7FF3F;box-shadow:0 0 16px rgba(215,255,63,.7);transform-origin:left;animation:bnx 1.4s cubic-bezier(.3,.8,.3,1) .9s backwards}
.kf-ad p{margin-top:12px;font-size:22px;line-height:1.35;color:rgba(245,242,234,.92)}
.kf-ad b{color:#D7FF3F;font-weight:700}
.bn-big.bn-hm{font-size:84px;line-height:.98;letter-spacing:-.045em}
.bn-big.bn-hm span{color:#D7FF3F}
.pl-f{margin:16px 0 0;display:flex;flex-direction:column;gap:8px}
.pl-f div{display:flex;flex-direction:column;gap:3px;padding:10px 0;border-top:1px solid rgba(245,242,234,.1)}
.pl-f dt{font:700 14px/1 var(--body);letter-spacing:.12em;text-transform:uppercase;color:var(--muted)}
.pl-f dd{margin:0;font:600 20px/1.3 var(--body);color:#F5F2EA}
.pl2.hi .pl-f dt{color:rgba(245,242,234,.75)}
.pl2.hi .pl-f div{border-color:rgba(245,242,234,.22)}
.pl2 ul{margin-top:8px}
.pl2 li{padding:9px 0 9px 36px;font-size:20px}
.pl2 li::before{top:11px}
.pl-p b{font-size:62px}
.pl2{padding:26px 28px 26px}
.pl-p{margin-top:12px}
.pl-c{margin-top:16px;font-size:18px;padding:10px 14px}
.plans2{margin-top:40px}
.fine{bottom:-44px}
html.ycflow .bn-big.bn-hm{font-size:84px}
'''
rep('<style id="ix-css">', '<style id="v18-css">' + CSS + '</style>\n<style id="ix-css">')

T = {
    'Users at Leyton': ('Utilisateurs chez Leyton', 'Usuarios en Leyton', 'Användare på Leyton', 'Nutzer bei Leyton'),
    'of the group’s': ('des', 'de los', 'av koncernens', 'der'), 'employees already use YouCraft': ('collaborateurs du groupe utilisent déjà YouCraft', 'empleados del grupo ya usan YouCraft', 'medarbetare använder redan YouCraft', 'Mitarbeitenden des Konzerns nutzen YouCraft bereits'),
    '3,000': ('3 000', '3.000', '3 000', '3.000'),
    '500+ applications deployed': ('500+ applications déployées', '500+ aplicaciones desplegadas', '500+ driftsatta applikationer', '500+ bereitgestellte Anwendungen'),
    'Hours,': ('Des heures,', 'Horas,', 'Timmar,', 'Stunden,'), 'not months.': ('pas des mois.', 'no meses.', 'inte månader.', 'nicht Monate.'),
    'From business idea to working solution': ('De l’idée métier à la solution opérationnelle', 'De la idea de negocio a la solución operativa', 'Från affärsidé till fungerande lösning', 'Von der Geschäftsidee zur funktionierenden Lösung'),
    'Per builder licence and per month, excl. VAT': ('Par licence builder et par mois, HT', 'Por licencia de builder y por mes, sin IVA', 'Per builderlicens och månad, exkl. moms', 'Pro Builder-Lizenz und Monat, zzgl. MwSt.'),
    'per builder / month': ('par builder / mois', 'por builder / mes', 'per builder / månad', 'pro Builder / Monat'),
    'from €12K per year': ('à partir de 12 k€ par an', 'desde 12.000 € al año', 'från 12 000 € per år', 'ab 12.000 € pro Jahr'),
    'Contract': ('Contrat', 'Contrato', 'Avtal', 'Vertrag'),
    '100 AI credits / month': ('100 crédits IA / mois', '100 créditos de IA / mes', '100 AI-krediter / månad', '100 KI-Credits / Monat'),
    '300 AI credits / month': ('300 crédits IA / mois', '300 créditos de IA / mes', '300 AI-krediter / månad', '300 KI-Credits / Monat'),
    '500 AI credits / month': ('500 crédits IA / mois', '500 créditos de IA / mes', '500 AI-krediter / månad', '500 KI-Credits / Monat'),
    'Pooled AI credits': ('Crédits IA mutualisés', 'Créditos de IA compartidos', 'Delade AI-krediter', 'Gemeinsame KI-Credits'),
    'App users': ('Utilisateurs d’application', 'Usuarios de las aplicaciones', 'Appanvändare', 'App-Nutzer'),
    'Commitment': ('Engagement', 'Compromiso', 'Bindningstid', 'Laufzeit'),
    'Up to 5, without authentication': ('Jusqu’à 5, sans authentification', 'Hasta 5, sin autenticación', 'Upp till 5, utan inloggning', 'Bis zu 5, ohne Anmeldung'),
    '10 included, then €10 / user': ('10 inclus, puis 10 € / utilisateur', '10 incluidos, luego 10 € / usuario', '10 ingår, sedan 10 € / användare', '10 inklusive, danach 10 € / Nutzer'),
    'Volume pricing': ('Tarifs de volume', 'Precios por volumen', 'Volympriser', 'Mengenpreise'),
    'Quarterly': ('Trimestriel', 'Trimestral', 'Kvartalsvis', 'Vierteljährlich'),
    'Annual, from €12K': ('Annuel, à partir de 12 k€', 'Anual, desde 12.000 €', 'Årlig, från 12 000 €', 'Jährlich, ab 12.000 €'),
    'Public apps only': ('Applications publiques uniquement', 'Solo aplicaciones públicas', 'Endast publika appar', 'Nur öffentliche Apps'),
    'Up to 10 apps and live previews': ('Jusqu’à 10 applications et aperçus en direct', 'Hasta 10 aplicaciones y vistas previas', 'Upp till 10 appar och liveförhandsvisningar', 'Bis zu 10 Apps und Live-Vorschauen'),
    'One-click deploy': ('Déploiement en un clic', 'Despliegue en un clic', 'Driftsättning med ett klick', 'Bereitstellung mit einem Klick'),
    'Private apps and SSO': ('Applications privées et SSO', 'Aplicaciones privadas y SSO', 'Privata appar och SSO', 'Private Apps und SSO'),
    'No training on your data': ('Pas d’entraînement sur vos données', 'Sin entrenamiento con tus datos', 'Ingen träning på era data', 'Kein Training mit Ihren Daten'),
    'Source code ownership': ('Propriété du code source', 'Propiedad del código fuente', 'Äganderätt till källkoden', 'Eigentum am Quellcode'),
    'Sovereign open-source LLM': ('LLM open source souverain', 'LLM de código abierto soberano', 'Suverän LLM med öppen källkod', 'Souveränes Open-Source-LLM'),
    'Regional data residency': ('Résidence régionale des données', 'Residencia regional de los datos', 'Regional datalagring', 'Regionale Datenhaltung'),
    'Dedicated support': ('Support dédié', 'Soporte dedicado', 'Dedikerad support', 'Dedizierter Support'),
    'Several teams, central governance': ('Plusieurs équipes, gouvernance centrale', 'Varios equipos, gobernanza central', 'Flera team, central styrning', 'Mehrere Teams, zentrale Governance'),
    'Any LLM, including sovereign': ('Tout LLM, y compris souverain', 'Cualquier LLM, incluso soberano', 'Valfri LLM, även suverän', 'Jedes LLM, auch souverän'),
    'Dedicated CSM and architect': ('CSM et architecte dédiés', 'CSM y arquitecto dedicados', 'Dedikerad CSM och arkitekt', 'Eigener CSM und Architekt'),
    'All prices excl. VAT. Quarterly commitment for Starter, Business and Sovereign. Beyond €12K per year, Enterprise applies.': (
        'Tous les prix sont HT. Engagement trimestriel pour Starter, Business et Sovereign. Au‑delà de 12 k€ par an, on passe en Enterprise.',
        'Todos los precios sin IVA. Compromiso trimestral para Starter, Business y Sovereign. A partir de 12.000 € al año, se aplica Enterprise.',
        'Alla priser exkl. moms. Kvartalsvis bindning för Starter, Business och Sovereign. Över 12 000 € per år gäller Enterprise.',
        'Alle Preise zzgl. MwSt. Vierteljährliche Laufzeit für Starter, Business und Sovereign. Ab 12.000 € pro Jahr gilt Enterprise.'),
}
add = {l: {k: v[i] for k, v in T.items()} for i, l in enumerate(('fr', 'es', 'sv', 'de'))}
add['en'] = {}
rep('<script>\n/* language layer (inlined) */',
    '<script>\n/* v1.8 : corrections */\n(function(){var A=' + json.dumps(add, ensure_ascii=False) + ';for(var l in A){window.__YC_DICTS__[l]=window.__YC_DICTS__[l]||{};for(var k in A[l])window.__YC_DICTS__[l][k]=A[l][k];}})();\n</script>\n<script>\n/* language layer (inlined) */')

out = os.path.join(HERE, '..', 'youcraft-presentation_1_8_EN.html' if EN else 'youcraft-presentation_1_8.html')
open(out, 'w', encoding='utf-8').write(src)
print(os.path.abspath(out), round(len(src) / 1024), 'Ko')
