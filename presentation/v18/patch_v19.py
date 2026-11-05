"""Présentation YouCraft v1.9 (version française, ouverture en français), construite sur la v1.8 FR (12 diapositives) :
1) Tarifs au niveau de détail de la grille de référence (offre de lancement clients Leyton, liste complète par offre, notes).
2) Chiffres clés : 1 000+ utilisateurs sur les 3 000 collaborateurs Leyton.
3) Nouvelle diapositive 13 « Prochaines étapes » : essai de 7 jours en quatre temps.
Usage : python3 patch_v18.py && python3 patch_v19.py  ->  ../youcraft-presentation_1_9.html"""
import json, os

HERE = os.path.dirname(os.path.abspath(__file__))
src = open(os.path.join(HERE, '..', 'youcraft-presentation_1_8.html'), encoding='utf-8').read()


def rep(a, b, n=1):
    global src
    c = src.count(a)
    assert c == n, (c, a[:100])
    src = src.replace(a, b)


T = {}  # anglais -> (fr, es, sv, de)


def t(en, fr, es, sv, de):
    T[en] = (fr, es, sv, de)
    return en


# ---------------------------------------------------------------- 2. chiffres clés
rep('<div class="n" data-count="800" data-suffix="+">800+</div>', '<div class="n" data-count="1000" data-suffix="+">1000+</div>')
rep('<p><b>800+</b> of the group’s <b>3,000</b> employees already use YouCraft</p>', '<p><b>1000+</b> of the group’s <b>3,000</b> employees already use YouCraft</p>')

# ---------------------------------------------------------------- 1. tarifs
CK = '<svg class="ic"><use href="#i-spark"/></svg>'
UNIT = t('€ excl. VAT / user / month', '€ HT / utilisateur / mois', '€ sin IVA / usuario / mes', '€ exkl. moms / användare / månad', '€ zzgl. MwSt. / Nutzer / Monat')
PLANS = [
    ('st', 'Starter', 'Build', '<b>25</b>', UNIT, None,
     t('Get started fast with the essential AI tools to boost productivity.', 'Démarrez vite avec les outils IA essentiels pour gagner en productivité.', 'Empieza rápido con las herramientas de IA esenciales para ganar productividad.', 'Kom igång snabbt med de viktigaste AI-verktygen för ökad produktivitet.', 'Schnell starten mit den wichtigsten KI-Tools für mehr Produktivität.'),
     [('b', t('100 credits / month', '100 crédits / mois', '100 créditos / mes', '100 krediter / månad', '100 Credits / Monat'), '*'),
      t('Up to 10 apps and live previews', 'Jusqu’à 10 applications et aperçus en direct', 'Hasta 10 aplicaciones y vistas previas', 'Upp till 10 appar och liveförhandsvisningar', 'Bis zu 10 Apps und Live-Vorschauen'),
      t('1-click deploy', 'Déploiement en 1 clic', 'Despliegue en 1 clic', 'Driftsättning med 1 klick', 'Bereitstellung mit 1 Klick'),
      t('CSV / JSON data import', 'Import de données CSV / JSON', 'Importación de datos CSV / JSON', 'Dataimport CSV / JSON', 'Datenimport CSV / JSON'),
      t('Public apps and domains only', 'Applications et domaines publics uniquement', 'Solo aplicaciones y dominios públicos', 'Endast publika appar och domäner', 'Nur öffentliche Apps und Domains'),
      t('Up to 5 app users, no authentication', 'Jusqu’à 5 utilisateurs d’application, sans authentification', 'Hasta 5 usuarios de aplicación, sin autenticación', 'Upp till 5 appanvändare, utan inloggning', 'Bis zu 5 App-Nutzer, ohne Anmeldung'),
      t('Standard SaaS cloud hosting', 'Hébergement cloud SaaS standard', 'Alojamiento cloud SaaS estándar', 'Standard SaaS-molnhosting', 'Standard-SaaS-Cloud-Hosting')]),
    ('bu', 'Business', 'Control', '<s>50</s><b>' + t('Free', 'Offert', 'Gratis', 'Gratis', 'Gratis') + '</b>', UNIT,
     t('7 days free, renewable · Leyton clients', '7 jours offerts, renouvelables · clients Leyton', '7 días gratis, renovables · clientes de Leyton', '7 dagar gratis, förnybara · Leyton-kunder', '7 Tage gratis, verlängerbar · Leyton-Kunden'),
     t('Everything in Starter, plus advanced control and data security.', 'Tout Starter, plus contrôle avancé et sécurité des données.', 'Todo Starter, más control avanzado y seguridad de los datos.', 'Allt i Starter, plus avancerad kontroll och datasäkerhet.', 'Alles aus Starter, plus erweiterte Kontrolle und Datensicherheit.'),
     [('b', t('300 credits / month', '300 crédits / mois', '300 créditos / mes', '300 krediter / månad', '300 Credits / Monat'), '*'),
      t('No training on your data', 'Aucun entraînement sur vos données', 'Sin entrenamiento con tus datos', 'Ingen träning på era data', 'Kein Training mit Ihren Daten'),
      t('Zero Data Retention', 'Zero Data Retention', 'Zero Data Retention', 'Zero Data Retention', 'Zero Data Retention'),
      t('Source code ownership', 'Propriété du code source', 'Propiedad del código fuente', 'Äganderätt till källkoden', 'Eigentum am Quellcode'),
      t('SSO (Google / Microsoft / GitHub) and role-based access', 'SSO (Google / Microsoft / GitHub) et accès par rôles', 'SSO (Google / Microsoft / GitHub) y acceso por roles', 'SSO (Google / Microsoft / GitHub) och rollbaserad åtkomst', 'SSO (Google / Microsoft / GitHub) und rollenbasierter Zugriff'),
      t('Private apps and custom domains', 'Applications privées et domaines personnalisés', 'Aplicaciones privadas y dominios propios', 'Privata appar och egna domäner', 'Private Apps und eigene Domains'),
      ('sub', t('10 app user licences included', '10 licences utilisateur d’application incluses', '10 licencias de usuario de aplicación incluidas', '10 appanvändarlicenser ingår', '10 App-Nutzer-Lizenzen inklusive'),
       t('then €10 excl. VAT / app user / month', 'puis 10 € HT / utilisateur d’application / mois', 'luego 10 € sin IVA / usuario / mes', 'sedan 10 € exkl. moms / appanvändare / månad', 'danach 10 € zzgl. MwSt. / App-Nutzer / Monat')),
      t('Regional cloud hosting', 'Hébergement cloud régional', 'Alojamiento cloud regional', 'Regional molnhosting', 'Regionales Cloud-Hosting')]),
    ('sv', 'Sovereign', 'Protect', '<b>100</b>', UNIT, None,
     t('Everything in Business, with maximum data protection and sovereignty.', 'Tout Business, avec une protection et une souveraineté maximales des données.', 'Todo Business, con la máxima protección y soberanía de los datos.', 'Allt i Business, med maximalt dataskydd och suveränitet.', 'Alles aus Business, mit maximalem Datenschutz und maximaler Souveränität.'),
     [('b', t('500 credits / month', '500 crédits / mois', '500 créditos / mes', '500 krediter / månad', '500 Credits / Monat'), '*'),
      t('Sovereign open-source LLM', 'LLM souverain open source', 'LLM soberano de código abierto', 'Suverän LLM med öppen källkod', 'Souveränes Open-Source-LLM'),
      t('Regional data residency', 'Résidence régionale des données', 'Residencia regional de los datos', 'Regional datalagring', 'Regionale Datenhaltung'),
      ('star', t('Self-hosted deployment options for apps', 'Options de déploiement self‑hosted des applications', 'Opciones de despliegue autoalojado de las aplicaciones', 'Alternativ för egen drift av apparna', 'Self-Hosting-Optionen für die Apps'), '**'),
      t('Dedicated support', 'Support dédié', 'Soporte dedicado', 'Dedikerad support', 'Dedizierter Support')]),
    ('en', 'Enterprise', 'Scale', '<b class="sm">' + t('Custom', 'Sur mesure', 'A medida', 'Skräddarsytt', 'Individuell') + '</b>', t('by volume', 'selon volume', 'según volumen', 'efter volym', 'nach Volumen'), None,
     t('A tailored solution for your organisation, at scale.', 'Une solution sur mesure pour votre organisation, à l’échelle.', 'Una solución a medida para tu organización, a escala.', 'En skräddarsydd lösning för hela organisationen.', 'Eine maßgeschneiderte Lösung für Ihre gesamte Organisation.'),
     [t('AI credits pooled across users', 'Crédits IA mutualisés entre utilisateurs', 'Créditos de IA compartidos entre usuarios', 'AI-krediter delade mellan användare', 'KI-Credits für alle Nutzer gebündelt'),
      t('Organisation management and usage insights', 'Gestion d’organisation et insights d’usage', 'Gestión de la organización y datos de uso', 'Organisationshantering och användningsinsikter', 'Organisationsverwaltung und Nutzungsanalysen'),
      t('Corporate SSO (SAML / OIDC)', 'SSO corporate (SAML / OIDC)', 'SSO corporativo (SAML / OIDC)', 'Företags-SSO (SAML / OIDC)', 'Unternehmens-SSO (SAML / OIDC)'),
      t('Penetration testing and security scans', 'Tests d’intrusion et scans de sécurité', 'Pruebas de intrusión y análisis de seguridad', 'Penetrationstester och säkerhetsskanningar', 'Penetrationstests und Sicherheitsscans'),
      t('Dedicated CSM and solution architect', 'CSM dédié et architecte solution', 'CSM y arquitecto de soluciones dedicados', 'Dedikerad CSM och lösningsarkitekt', 'Eigener CSM und Lösungsarchitekt'),
      t('Tailored contract, SLA and governance', 'Contrat, SLA et gouvernance sur mesure', 'Contrato, SLA y gobernanza a medida', 'Skräddarsytt avtal, SLA och styrning', 'Individueller Vertrag, SLA und Governance'),
      t('Custom integrations', 'Intégrations sur mesure', 'Integraciones a medida', 'Skräddarsydda integrationer', 'Individuelle Integrationen')]),
]


def li(x):
    if isinstance(x, str):
        return f'<li>{x}</li>'
    if x[0] == 'b':
        return f'<li><b>{x[1]}</b> <sup>{x[2]}</sup></li>'
    if x[0] == 'sub':
        return f'<li>{x[1]}<small>{x[2]}</small></li>'
    return f'<li>{x[1]} <sup>{x[2]}</sup></li>'


cards = []
for i, (cls, name, tier, price, unit, offer, tag, items) in enumerate(PLANS):
    flag = '<div class="pc-flag">' + t('BEST VALUE', 'LE PLUS AVANTAGEUX', 'LA MEJOR OPCIÓN', 'BÄST VÄRDE', 'BESTES ANGEBOT') + '</div>' if cls == 'bu' else ''
    cards.append(f'      <div class="pc pc-{cls} rise" style="--d:{.15 + i * .12:.2f}s">{flag}<div class="pc-h"><div><h3>{name}</h3><small>{tier}</small></div>'
                 f'<div class="pc-p"><div class="pc-v">{price}</div><span>{unit}</span></div></div>'
                 + (f'<div class="pc-o">{offer}</div>' if offer else '') +
                 f'<div class="pc-b"><p>{tag}</p><ul>{"".join(li(x) for x in items)}</ul></div></div>')
LEAD1 = t('Leyton client launch offer:', 'Offre de lancement clients Leyton :', 'Oferta de lanzamiento para clientes de Leyton:', 'Lanseringserbjudande för Leyton-kunder:', 'Startangebot für Leyton-Kunden:')
LEAD2 = t('7 days of Business free, renewable.', '7 jours de Business offerts, renouvelables.', '7 días de Business gratis, renovables.', '7 dagar Business gratis, förnybara.', '7 Tage Business gratis, verlängerbar.')
F1 = t('* 200 credits ≈ 3 simple apps or 1 moderate app per month. Extra credits are available beyond the plan. Reference prices for France, excl. VAT.',
       '* 200 crédits ≈ 3 applications simples ou 1 application modérée par mois. Des crédits additionnels sont disponibles au‑delà du plan. Tarifs de référence France, HT.',
       '* 200 créditos ≈ 3 aplicaciones sencillas o 1 aplicación moderada al mes. Hay créditos adicionales más allá del plan. Precios de referencia para Francia, sin IVA.',
       '* 200 krediter ≈ 3 enkla appar eller 1 medelstor app per månad. Extra krediter finns utöver planen. Referenspriser för Frankrike, exkl. moms.',
       '* 200 Credits ≈ 3 einfache Apps oder 1 mittlere App pro Monat. Zusätzliche Credits sind über den Tarif hinaus verfügbar. Referenzpreise Frankreich, zzgl. MwSt.')
F2 = t('** Additional fees.', '** Frais additionnels.', '** Costes adicionales.', '** Tillkommande avgifter.', '** Zusätzliche Gebühren.')
F3 = t('Quarterly commitment (annual for Enterprise). From Business, the code of your apps belongs to you.',
       'Engagement trimestriel (annuel pour Enterprise). À partir de Business, le code de vos applications vous appartient.',
       'Compromiso trimestral (anual para Enterprise). Desde Business, el código de tus aplicaciones te pertenece.',
       'Kvartalsvis bindning (årlig för Enterprise). Från Business äger ni koden till era appar.',
       'Vierteljährliche Laufzeit (jährlich bei Enterprise). Ab Business gehört Ihnen der Code Ihrer Apps.')
a = src.index('<div class="pr-h">'); b = src.index('</section>', a)
src = src[:a] + ('<div class="pr-h"><h2 class="title rise">Simple, transparent pricing</h2></div>\n'
                 f'    <div class="pz-offer rise" style="--d:.1s"><b>{LEAD1}</b> <span>{LEAD2}</span></div>\n'
                 '    <div class="pz">\n' + '\n'.join(cards) + '\n    </div>\n'
                 f'    <div class="pz-fine"><p><span>{F1}</span> · <span>{F2}</span></p><p><b>{F3}</b></p></div>\n  </div>\n') + src[b:]

# ---------------------------------------------------------------- 3. prochaines étapes
STEPS = [
    ('1', t('Day 0 · 1 h', 'J0 · 1 h', 'Día 0 · 1 h', 'Dag 0 · 1 h', 'Tag 0 · 1 Std.'), t('We build your app together', 'On crée ensemble votre application', 'Creamos juntos tu aplicación', 'Vi bygger er app tillsammans', 'Wir bauen Ihre App gemeinsam'),
     t('In a one-hour session, we pick your priority use case with your teams and build the first version of the app together, live.', 'En une séance d’1 h, nous choisissons avec vos équipes le cas d’usage prioritaire et construisons ensemble, en direct, la première version de l’application.',
       'En una sesión de 1 h, elegimos con tus equipos el caso de uso prioritario y creamos juntos, en directo, la primera versión de la aplicación.', 'Under en timme väljer vi tillsammans med era team det viktigaste användningsfallet och bygger den första versionen av appen live.',
       'In einer einstündigen Session wählen wir mit Ihren Teams den wichtigsten Anwendungsfall und bauen gemeinsam live die erste Version der App.'), 'users'),
    ('2', t('Day 0 to Day 7', 'De J0 à J7', 'Del día 0 al 7', 'Dag 0 till 7', 'Tag 0 bis 7'), t('You use it in-house', 'Vous l’utilisez en interne', 'La usas internamente', 'Ni använder den internt', 'Sie nutzen sie intern'),
     t('We leave the app with you: your teams improve it and use it day to day to see its full power.', 'On vous laisse l’application : vos équipes l’améliorent et l’utilisent au quotidien pour en mesurer toute la puissance.',
       'Te dejamos la aplicación: tus equipos la mejoran y la usan a diario para comprobar todo su potencial.', 'Appen är er: teamen förbättrar den och använder den dagligen för att se hela dess kraft.',
       'Die App gehört Ihnen: Ihre Teams verbessern sie und nutzen sie täglich, um ihr volles Potenzial zu erleben.'), 'rocket'),
    ('3', t('Day 3 · 30 min', 'J3 · 30 min', 'Día 3 · 30 min', 'Dag 3 · 30 min', 'Tag 3 · 30 Min.'), t('First check-in', 'Premier point d’étape', 'Primer punto de control', 'Första avstämning', 'Erster Check-in'),
     t('A call of about 30 minutes to talk about the app: what works, what is missing, what to improve.', 'Un échange d’environ 30 minutes pour parler de l’application : ce qui fonctionne, ce qui manque, ce qu’il faut améliorer.',
       'Una llamada de unos 30 minutos para hablar de la aplicación: qué funciona, qué falta, qué mejorar.', 'Ett samtal på cirka 30 minuter om appen: vad som fungerar, vad som saknas, vad som kan förbättras.',
       'Ein Gespräch von etwa 30 Minuten über die App: Was funktioniert, was fehlt, was sich verbessern lässt.'), 'chat'),
    ('4', t('Day 7', 'J7', 'Día 7', 'Dag 7', 'Tag 7'), t('Your feedback and next steps', 'Votre avis et la suite', 'Tu opinión y los siguientes pasos', 'Er feedback och nästa steg', 'Ihr Feedback und die nächsten Schritte'),
     t('After one week, you give us your full feedback and we discuss a possible next step together.', 'Au bout d’une semaine, vous nous donnez votre avis complet et nous discutons ensemble d’une éventuelle suite.',
       'Al cabo de una semana, nos das tu opinión completa y hablamos juntos de una posible continuación.', 'Efter en vecka ger ni oss er fullständiga feedback och vi diskuterar tillsammans en eventuell fortsättning.',
       'Nach einer Woche geben Sie uns Ihr vollständiges Feedback und wir besprechen gemeinsam die mögliche Fortsetzung.'), 'flag'),
]
ICO = {'users': '#i-users', 'rocket': '#i-plane', 'chat': '#i-chat', 'flag': '#i-chart'}
DAYS = [t('D' + str(d), 'J' + str(d), 'D' + str(d), 'D' + str(d), 'T' + str(d)) if d else t('D0', 'J0', 'D0', 'D0', 'T0') for d in range(8)]
ticks = ''.join(f'<span class="ns-tk{" on" if d in (0, 3, 7) else ""}" style="left:{d / 7 * 100:.3f}%"><i></i><em>{DAYS[d]}</em></span>' for d in range(8))
pins = ''.join(f'<span class="ns-pin" style="left:{p:.3f}%">{n}</span>' for n, p in (('1', 0), ('3', 3 / 7 * 100), ('4', 100)))
band = t('Your teams use and improve the app', 'Vos équipes utilisent et améliorent l’application', 'Tus equipos usan y mejoran la aplicación', 'Era team använder och förbättrar appen', 'Ihre Teams nutzen und verbessern die App')
steps = ''.join(f'<div class="ns-c rise" style="--d:{.35 + i * .12:.2f}s"><div class="ns-ch"><span class="ns-n">{n}</span><span class="ns-d">{d}</span></div><h3>{h}</h3><p>{p}</p></div>'
                + ('' if i == 3 else '<span class="ns-ar" aria-hidden="true"></span>') for i, (n, d, h, p, ic) in enumerate(STEPS))
NS = f'''
<!-- 13 NEXT STEPS -->
<section class="slide" id="s14">
  <div class="brand logo-bg" role="img" aria-label="YouCraft"></div><div class="pg"></div>
  <div class="content">
    <div class="ns-h"><div><div class="ns-k rise">{t('NEXT STEPS', 'PROCHAINES ÉTAPES', 'PRÓXIMOS PASOS', 'NÄSTA STEG', 'NÄCHSTE SCHRITTE')}</div><h2 class="title rise" style="--d:.05s">{t('Four steps, and your teams are building', 'Quatre temps, et vos équipes construisent', 'Cuatro pasos, y tus equipos construyen', 'Fyra steg, och era team bygger', 'Vier Schritte, und Ihre Teams bauen')}</h2></div>
      <p class="lead rise" style="--d:.1s">{t('A 7-day trial with your teams, on a real use case.', 'Un essai de 7 jours avec vos équipes, sur un cas d’usage réel.', 'Una prueba de 7 días con tus equipos, sobre un caso de uso real.', 'En 7 dagars provperiod med era team, på ett verkligt användningsfall.', 'Ein 7-tägiger Test mit Ihren Teams, an einem echten Anwendungsfall.')}</p></div>
    <div class="ns-wk rise" style="--d:.2s"><div class="ns-tr"></div><div class="ns-band"><span><b>2</b>{band}</span></div>{ticks}{pins}</div>
    <div class="ns-g">{steps}</div>
    <div class="ns-ft rise" style="--d:.85s"><span class="ns-fi"><svg class="ic"><use href="#i-spark"/></svg></span><b>{t('7 days to try it, renewable, no commitment', '7 jours pour essayer, renouvelables, sans engagement', '7 días para probarlo, renovables, sin compromiso', '7 dagar att prova, förnybara, utan bindning', '7 Tage zum Testen, verlängerbar, unverbindlich')}</b><span class="ns-fs">{t('Free access during the trial', 'Accès gratuit pendant l’essai', 'Acceso gratuito durante la prueba', 'Gratis åtkomst under provperioden', 'Kostenloser Zugang während des Tests')}</span></div>
  </div>
</section>
'''
i = src.rindex('</section>', 0, src.index('<nav id="ui"')) + len('</section>')
src = src[:i] + '\n' + NS + src[i:]

CSS = '''
/* v1.9 : chiffres clés */
.kf .kf-yc .n{font-size:84px}
.kf-ad .kf-bar i{width:33.3%}
html.ycflow .kf .kf-yc .n{font-size:68px}
/* v1.9 : tarifs détaillés */
.pz-offer{margin-top:14px;display:inline-flex;align-items:center;gap:10px;padding:12px 22px;border-radius:14px;border-left:4px solid #D7FF3F;background:rgba(215,255,63,.08);font-size:22px;color:#F5F2EA}
.pz-offer b{color:#D7FF3F;font-weight:700}
.pz{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-top:26px;align-items:start}
.pc{position:relative;border-radius:24px;background:linear-gradient(170deg,rgba(20,26,66,.94),rgba(8,8,14,.92));border:1px solid rgba(245,242,234,.14);box-shadow:0 24px 70px rgba(5,5,8,.4);display:flex;flex-direction:column}
.pc-h{display:flex;justify-content:space-between;align-items:flex-start;gap:10px;padding:20px 22px 18px;border-radius:23px 23px 0 0;min-height:104px}
.pc-h h3{font:700 30px/1 var(--display);letter-spacing:-.01em}
.pc-h small{display:block;margin-top:8px;font:500 18px/1 var(--body);opacity:.85}
.pc-p{text-align:right}
.pc-v{display:flex;align-items:baseline;justify-content:flex-end;gap:8px}
.pc-v b{font:800 44px/1 var(--display);letter-spacing:-.03em}
.pc-v b.sm{font-size:30px;line-height:1.05}
.pc-v s{font:700 22px/1 var(--display);opacity:.6}
.pc-p span{display:block;margin-top:6px;font-size:12.5px;opacity:.85;white-space:nowrap}
.pc-st .pc-h{background:#1E3BFF}
.pc-bu{border:2px solid #6F86FF;box-shadow:0 30px 90px rgba(30,59,255,.5)}
.pc-bu .pc-h{background:linear-gradient(135deg,#1E3BFF,#4F6BFF)}
.pc-sv .pc-h{background:#D7FF3F;color:#0B0B0F}
.pc-en .pc-h{background:#0B0B0F;border-bottom:1px solid rgba(245,242,234,.14)}
.pc-flag{position:absolute;left:50%;top:-17px;transform:translateX(-50%);padding:8px 18px;border-radius:20px;background:#D7FF3F;color:#0B0B0F;font:800 14px/1 var(--display);letter-spacing:.14em;white-space:nowrap;box-shadow:0 0 26px rgba(215,255,63,.5);z-index:2}
.pc-o{padding:10px 16px;text-align:center;background:rgba(215,255,63,.12);border-bottom:1px solid rgba(215,255,63,.25);color:#D7FF3F;font:700 16px/1.3 var(--body)}
.pc-b{padding:18px 22px 20px}
.pc-b>p{font:700 18px/1.35 var(--display);letter-spacing:-.005em;margin-bottom:10px}
.pc-b ul{list-style:none;display:flex;flex-direction:column}
.pc-b li{position:relative;padding:6px 0 6px 30px;font-size:16.5px;line-height:1.35;color:rgba(245,242,234,.92)}
.pc-b li::before{content:"";position:absolute;left:0;top:8px;width:20px;height:20px;border-radius:50%;background:#1E3BFF url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23fff' stroke-width='3.4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M5 12l5 5 9-10'/%3E%3C/svg%3E") center/12px no-repeat}
.pc-sv li::before{background-color:#D7FF3F;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230B0B0F' stroke-width='3.4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M5 12l5 5 9-10'/%3E%3C/svg%3E")}
.pc-en li::before{background-color:#F5F2EA;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230B0B0F' stroke-width='3.4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M5 12l5 5 9-10'/%3E%3C/svg%3E")}
.pc-b li b{font-weight:700;color:#F5F2EA}
.pc-b li sup{font-size:12px;color:#D7FF3F;vertical-align:super}
.pc-b li small{display:block;font-size:13.5px;color:var(--muted);margin-top:2px}
.pz-fine{position:absolute;left:0;right:0;bottom:-34px;text-align:center;font-size:17px;line-height:1.5;color:var(--muted)}
.pz-fine b{color:rgba(245,242,234,.8);font-weight:600}
/* v1.9 : prochaines étapes */
.ns-h{display:flex;align-items:flex-end;justify-content:space-between;gap:40px}
.ns-k{display:inline-flex;align-items:center;gap:10px;font:700 18px/1 var(--display);letter-spacing:.2em;color:#D7FF3F;margin-bottom:16px}
.ns-k::before{content:"";width:28px;height:3px;border-radius:2px;background:#D7FF3F}
.ns-h .title{font-size:56px;white-space:nowrap}
html.ycflow .ns-h .title{white-space:normal}
.ns-h .lead{font-size:22px;max-width:420px;text-align:right;margin-bottom:10px}
.ns-wk{position:relative;height:118px;margin:44px 34px 0}
.ns-tr{position:absolute;left:0;right:0;top:52px;height:4px;border-radius:2px;background:rgba(245,242,234,.16)}
.ns-band{position:absolute;left:0;right:0;top:66px;height:40px;border-radius:12px;background:linear-gradient(90deg,rgba(30,59,255,.45),rgba(79,107,255,.28) 60%,rgba(215,255,63,.22));border:1px solid rgba(118,140,255,.45);display:flex;align-items:center;justify-content:center;font:600 17px/1 var(--body);color:#E6EAFF}
.ns-band span{display:flex;align-items:center;gap:10px}
.ns-band b{width:24px;height:24px;border-radius:50%;background:#4F6BFF;color:#fff;display:grid;place-items:center;font:800 13px/1 var(--display)}
.ns-tk{position:absolute;top:0;transform:translateX(-50%);display:flex;flex-direction:column;align-items:center;gap:8px}
.ns-tk em{font:700 16px/1 var(--display);font-style:normal;color:var(--muted);letter-spacing:.04em}
.ns-tk.on em{color:#D7FF3F}
.ns-tk i{order:2;width:2px;height:14px;margin-top:12px;background:rgba(245,242,234,.3)}
.ns-pin{position:absolute;top:36px;transform:translateX(-50%);width:36px;height:36px;border-radius:50%;background:#D7FF3F;color:#0B0B0F;display:grid;place-items:center;font:800 17px/1 var(--display);box-shadow:0 0 0 6px rgba(215,255,63,.16),0 0 26px rgba(215,255,63,.5)}
.ns-g{display:grid;grid-template-columns:1fr 34px 1fr 34px 1fr 34px 1fr;align-items:stretch;margin-top:30px}
.ns-c{border-radius:26px;padding:28px 28px 30px;background:linear-gradient(170deg,rgba(20,26,66,.94),rgba(8,8,14,.9));border:1px solid rgba(245,242,234,.14);box-shadow:0 24px 70px rgba(5,5,8,.4);transition:transform .25s,border-color .25s}
.ns-c:hover{transform:translateY(-6px);border-color:rgba(215,255,63,.6)}
.ns-c:nth-child(3){background:linear-gradient(160deg,#1E3BFF 0%,#3550FF 60%,#5E78FF 100%);border-color:#7A90FF;box-shadow:0 30px 90px rgba(30,59,255,.45)}
.ns-ch{display:flex;align-items:center;justify-content:space-between}
.ns-n{width:52px;height:52px;border-radius:50%;display:grid;place-items:center;background:#D7FF3F;color:#0B0B0F;font:800 24px/1 var(--display)}
.ns-c:nth-child(3) .ns-n{background:#fff;color:#1E3BFF}
.ns-d{padding:8px 14px;border-radius:20px;background:rgba(215,255,63,.1);border:1px solid rgba(215,255,63,.35);color:#D7FF3F;font:700 16px/1 var(--body);white-space:nowrap}
.ns-c:nth-child(3) .ns-d{background:rgba(11,11,15,.22);border-color:rgba(245,242,234,.35);color:#fff}
.ns-c h3{margin-top:24px;font:600 30px/1.15 var(--display);letter-spacing:-.015em}
.ns-c p{margin-top:12px;font-size:21px;line-height:1.5;color:var(--muted)}
.ns-c:nth-child(3) p{color:rgba(245,242,234,.9)}
.ns-ar{align-self:center;justify-self:center;width:14px;height:14px;border-right:3px solid #D7FF3F;border-top:3px solid #D7FF3F;transform:rotate(45deg);opacity:.8}
.ns-ft{margin-top:30px;display:flex;align-items:center;gap:18px;padding:22px 30px;border-radius:22px;background:#0B0B0F;border:1px solid rgba(215,255,63,.35);box-shadow:0 0 50px rgba(215,255,63,.12)}
.ns-fi{width:46px;height:46px;border-radius:13px;background:#D7FF3F;display:grid;place-items:center;flex:none}
.ns-fi .ic{width:24px;height:24px;color:#0B0B0F}
.ns-ft b{font:700 28px/1.2 var(--display);letter-spacing:-.01em}
.ns-fs{margin-left:auto;padding:9px 16px;border-radius:20px;background:rgba(215,255,63,.12);color:#D7FF3F;font:600 18px/1 var(--body);white-space:nowrap}
.active .ns-band{animation:nsband 1.2s cubic-bezier(.3,.8,.3,1) .5s backwards;transform-origin:left}
@keyframes nsband{from{transform:scaleX(0);opacity:.3}}
html.ycflow .pz{grid-template-columns:1fr;gap:34px}
html.ycflow .pz-fine{position:relative;bottom:auto;margin-top:26px}
html.ycflow .ns-h{flex-direction:column;align-items:flex-start;gap:12px}
html.ycflow .ns-h .lead{text-align:left}
html.ycflow .ns-g{grid-template-columns:1fr;gap:18px}
html.ycflow .ns-ar{display:none}
html.ycflow .ns-ft{flex-wrap:wrap}
html.ycflow .ns-fs{margin-left:0}
'''
i = src.index('<style id="v18-css">'); src = src[:i] + '<style id="v19-css">' + CSS + '</style>\n' + src[i:]

# Ouverture en français (le choix enregistré dans le navigateur reste prioritaire).
rep('  var lang = "en";\n', '  var lang = "fr";\n')
rep('<html lang="en">', '<html lang="fr">')

add = {l: {k: v[i] for k, v in T.items()} for i, l in enumerate(('fr', 'es', 'sv', 'de'))}
add['en'] = {k: k for k in T}
rep('<script>\n/* language layer (inlined) */',
    '<script>\n/* v1.9 : tarifs détaillés, prochaines étapes */\n(function(){var A=' + json.dumps(add, ensure_ascii=False) + ';for(var l in A){window.__YC_DICTS__[l]=window.__YC_DICTS__[l]||{};for(var k in A[l])window.__YC_DICTS__[l][k]=A[l][k];}})();\n</script>\n<script>\n/* language layer (inlined) */')

out = os.path.join(HERE, '..', 'youcraft-presentation_1_9.html')
open(out, 'w', encoding='utf-8').write(src)
print(os.path.abspath(out), round(len(src) / 1024), 'Ko', len(T), 'textes traduits')
