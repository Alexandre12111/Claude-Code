"""Traduit la composition FR CognitX (src6) en italien ou en espagnol.
Usage : python3 translate_scenes.py it|es dossier_cible"""
import os, shutil, sys

LANG, DST = sys.argv[1], sys.argv[2]
I = 0 if LANG == 'it' else 1
HERE = os.path.dirname(os.path.abspath(__file__))
SRC = os.path.join(HERE, '..', 'src6')
DST = os.path.join(HERE, '..', DST)

# (fichier, texte français exact, italien, espagnol)
T = [
    ('p01_logo.js', "by.innerHTML = '<span>par</span>';", "by.innerHTML = '<span>di</span>';", "by.innerHTML = '<span>por</span>';"),
    ('p13_fin.js', "by.innerHTML = '<span>par</span>';", "by.innerHTML = '<span>di</span>';", "by.innerHTML = '<span>por</span>';"),
    ('p13_fin.js', "const TAG = 'Build, sans coder.';", "const TAG = 'Build, senza codice.';", "const TAG = 'Build, sin código.';"),
    ('p13_fin.js', ">Demander une démo<", ">Richiedi una demo<", ">Solicitar una demo<"),

    ('p02_valeur.js', "chars(lab, 'Depuis 1997')", "chars(lab, 'Dal 1997')", "chars(lab, 'Desde 1997')"),
    ('p02_valeur.js', "'Leyton révèle et capte la'", "'Leyton rivela e cattura il'", "'Leyton revela y capta el'"),
    ('p02_valeur.js', "chars(a.vWrap, 'VALEUR')", "chars(a.vWrap, 'VALORE')", "chars(a.vWrap, 'VALOR')"),
    ('p02_valeur.js', "'que ses clients ne voient pas.'", "'che i suoi clienti non vedono.'", "'que sus clientes no ven.'"),
    ('p02_valeur.js', "chars(a.pWrap, 'PRODUCTIVITÉ')", "chars(a.pWrap, 'PRODUTTIVITÀ')", "chars(a.pWrap, 'PRODUCTIVIDAD')"),

    ('p04_idees.js', "'Pilotage des KPI'", "'Monitoraggio KPI'", "'Seguimiento de KPI'"),
    ('p04_idees.js', "'Projets stratégiques'", "'Progetti strategici'", "'Proyectos estratégicos'"),
    ('p04_idees.js', "'Reporting mensuel'", "'Report mensile'", "'Informe mensual'"),
    ('p04_idees.js', "'Validation des devis'", "'Approvazione preventivi'", "'Aprobación de presupuestos'"),
    ('p04_idees.js', "'Prévisionnel de trésorerie'", "'Previsione di cassa'", "'Previsión de tesorería'"),
    ('p04_idees.js', "'Onboarding RH'", "'Onboarding HR'", "'Incorporación RR. HH.'"),
    ('p04_idees.js', "'Plan de formation'", "'Piano di formazione'", "'Plan de formación'"),
    ('p04_idees.js', "'Entretiens annuels'", "'Colloqui annuali'", "'Evaluaciones anuales'"),
    ('p04_idees.js', "['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin']",
     "['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno']", "['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio']"),
    ('p04_idees.js', """[{ t: 'Vos' }, { t: 'équipes' }, { t: 'ne' }, { t: 'manquent' }, { t: 'pas' }, { t: "d'idées.", c: 'o' }]""",
     """[{ t: 'I' }, { t: 'vostri' }, { t: 'team' }, { t: 'non' }, { t: 'mancano' }, { t: 'di' }, { t: 'idee.', c: 'o' }]""",
     """[{ t: 'A' }, { t: 'sus' }, { t: 'equipos' }, { t: 'no' }, { t: 'les' }, { t: 'faltan' }, { t: 'ideas.', c: 'o' }]"""),
    ('p04_idees.js', """[{ t: 'Elles' }, { t: 'manquent' }, { t: "d'un" }, { t: 'outil', c: 'o' }, { t: 'pour' }, { t: 'les' }, { t: 'construire.', c: 'o' }]""",
     """[{ t: 'Manca' }, { t: 'loro' }, { t: 'uno' }, { t: 'strumento', c: 'o' }, { t: 'per' }, { t: 'costruirle.', c: 'o' }]""",
     """[{ t: 'Les' }, { t: 'falta' }, { t: 'una' }, { t: 'herramienta', c: 'o' }, { t: 'para' }, { t: 'construirlas.', c: 'o' }]"""),
    ('p04_idees.js', "a.tB._w[6].parentElement", "a.tB._w[5].parentElement", "a.tB._w[5].parentElement"),
    ('p04_idees.js', "const wi = a.tB._w[6]", "const wi = a.tB._w[5]", "const wi = a.tB._w[5]"),
    ('p04_idees.js', '<div class="nh" style="font-size:38px;line-height:1">${k}</div>',
     '<div class="nh" style="font-size:36px;line-height:1">${({ DG: \'DG\', DAF: \'CFO\', DRH: \'HR\' })[k]}</div>',
     '<div class="nh" style="font-size:30px;line-height:1">${({ DG: \'DG\', DAF: \'CFO\', DRH: \'RR.HH.\' })[k]}</div>'),
    ('p04_idees.js', ">En attente</div>", ">In attesa</div>", ">En espera</div>"),
    ('p04_idees.js', ">d'un créneau informatique</div>", ">di disponibilità IT</div>", ">de disponibilidad de IT</div>"),
    ('p04_idees.js', "Toujours en attente :", "Ancora in attesa:", "Aún en espera:"),

    ('p05_decouvrez.js', "words(t, 'Découvrez la solution')", "words(t, 'Scoprite la soluzione')", "words(t, 'Descubra la solución')"),
    ('p05_decouvrez.js', "[{ t: 'qui' }, { t: 'répond' }, { t: 'à' }, { t: 'votre', c: 'o' }, { t: 'besoin.', c: 'o' }]",
     "[{ t: 'che' }, { t: 'risponde' }, { t: 'alle' }, { t: 'vostre', c: 'o' }, { t: 'esigenze.', c: 'o' }]",
     "[{ t: 'que' }, { t: 'responde' }, { t: 'a' }, { t: 'su', c: 'o' }, { t: 'necesidad.', c: 'o' }]"),

    ('p06_titres.js', "[{ t: 'Comment' }, { t: 'ça' }, { t: 'marche', c: 'o' }, { t: '?', c: 'o' }]",
     "[{ t: 'Come' }, { t: 'funziona?', c: 'o' }]", "[{ t: '¿Cómo' }, { t: 'funciona?', c: 'o' }]"),
    ('p06_titres.js', "')}Comment ça marche ?`", "')}Come funziona?`", "')}¿Cómo funciona?`"),
    ('p06_titres.js', "[{ t: 'Aussi' }, { t: 'simple' }, { t: \"qu'une\" }, { t: 'conversation.', c: 'o' }]",
     "[{ t: 'Semplice' }, { t: 'come' }, { t: 'una' }, { t: 'conversazione.', c: 'o' }]",
     "[{ t: 'Tan' }, { t: 'sencillo' }, { t: 'como' }, { t: 'una' }, { t: 'conversación.', c: 'o' }]"),
    ('p06_titres.js', "step(1, 'Décrivez votre besoin')", "step(1, 'Descrivete la vostra esigenza')", "step(1, 'Describa su necesidad')"),
    ('p06_titres.js', "step(2, 'En direct')", "step(2, 'In diretta')", "step(2, 'En directo')"),
    ('p06_titres.js', "'construit.'", "'costruisce.'", "'construye.'"),
    ('p06_titres.js', "'Votre application prend forme sous vos yeux.'", "'La vostra app prende forma sotto i vostri occhi.'", "'Su aplicación toma forma ante sus ojos.'"),
    ('p06_titres.js', "step(3, 'Publiez')", "step(3, 'Pubblicate')", "step(3, 'Publique')"),
    ('p06_titres.js', "'En ligne.'", "'Online.'", "'En línea.'"),
    ('p06_titres.js', "'Le jour même.'", "'In giornata.'", "'El mismo día.'"),

    ('p06_demo.js', "const PROMPT = \"Crée un tableau de bord de pilotage des KPI cross directions : chiffre d'affaires, marge, effectifs et trésorerie, avec alertes et accès mobile.\";",
     "const PROMPT = \"Crea una dashboard di monitoraggio dei KPI di tutte le direzioni: fatturato, margine, organico e cassa, con avvisi e accesso da mobile.\";",
     "const PROMPT = \"Crea un panel de control de KPI de todas las direcciones: facturación, margen, plantilla y tesorería, con alertas y acceso móvil.\";"),
    ('p06_demo.js', "'Analyse de votre demande'", "'Analisi della richiesta'", "'Analizando su solicitud'"),
    ('p06_demo.js', "'Créé', 'prisma", "'Creato', 'prisma", "'Creado', 'prisma"),
    ('p06_demo.js', "'Créé', 'app/kpi/finance", "'Creato', 'app/kpi/finance", "'Creado', 'app/kpi/finance"),
    ('p06_demo.js', "'Créé', 'app/kpi/rh", "'Creato', 'app/kpi/hr", "'Creado', 'app/kpi/rrhh"),
    ('p06_demo.js', "'Créé', 'app/alertes/route.ts'", "'Creato', 'app/avvisi/route.ts'", "'Creado', 'app/alertas/route.ts'"),
    ('p06_demo.js', "'Créé', 'app/pilotage/page.tsx'", "'Creato', 'app/cruscotto/page.tsx'", "'Creado', 'app/panel/page.tsx'"),
    ('p06_demo.js', "'Exécuté', 'tests : 14 réussis'", "'Eseguito', 'test: 14 superati'", "'Ejecutado', 'pruebas: 14 superadas'"),
    ('p06_demo.js', "'Aperçu mis à jour'", "'Anteprima aggiornata'", "'Vista previa actualizada'"),
    ('p06_demo.js', "['FIN', 'Cash-flow opérationnel', 'Finance', '+1,4 M€', 'ok']", "['FIN', 'Cash flow operativo', 'Finanza', '+1,4 M€', 'ok']", "['FIN', 'Flujo de caja operativo', 'Finanzas', '+1,4 M€', 'ok']"),
    ('p06_demo.js', "['COM', 'Marge Europe du Sud', 'Commercial', '−2,1 pts', 'warn']", "['COM', 'Margine Europa del Sud', 'Commerciale', '−2,1 pt', 'warn']", "['COM', 'Margen Europa del Sur', 'Comercial', '−2,1 pts', 'warn']"),
    ('p06_demo.js', "['RH', 'Recrutements T4', 'RH', '18 / 25 postes', 'warn']", "['HR', 'Assunzioni Q4', 'HR', '18 / 25 posizioni', 'warn']", "['RH', 'Contrataciones T4', 'RR. HH.', '18 / 25 puestos', 'warn']"),
    ('p06_demo.js', "['DG', 'Satisfaction clients', 'Groupe', 'NPS 62', 'ok']", "['DG', 'Soddisfazione clienti', 'Gruppo', 'NPS 62', 'ok']", "['DG', 'Satisfacción de clientes', 'Grupo', 'NPS 62', 'ok']"),
    ('p06_demo.js', "['FIN', 'Délai de paiement', 'Finance', '47 jours', 'warn']", "['FIN', 'Tempi di pagamento', 'Finanza', '47 giorni', 'warn']", "['FIN', 'Plazo de pago', 'Finanzas', '47 días', 'warn']"),
    ('p06_demo.js', "ok: ['Atteint',", "ok: ['Raggiunto',", "ok: ['Alcanzado',"),
    ('p06_demo.js', "warn: ['À surveiller',", "warn: ['Da monitorare',", "warn: ['A vigilar',"),
    ('p06_demo.js', "[\"Chiffre d'affaires\", 48.2,", "['Fatturato', 48.2,", "['Facturación', 48.2,"),
    ('p06_demo.js', "['Marge brute', 31.4,", "['Margine lordo', 31.4,", "['Margen bruto', 31.4,"),
    ('p06_demo.js', "['Effectifs', 1240,", "['Organico', 1240,", "['Plantilla', 1240,"),
    ('p06_demo.js', "['Trésorerie', 12.8,", "['Cassa', 12.8,", "['Tesorería', 12.8,"),
    ('p06_demo.js', "[['Avr', 0.52], ['Mai', 0.6], ['Juin', 0.57], ['Juil', 0.68], ['Août', 0.63], ['Sept', 0.82]]",
     "[['Apr', 0.52], ['Mag', 0.6], ['Giu', 0.57], ['Lug', 0.68], ['Ago', 0.63], ['Set', 0.82]]",
     "[['Abr', 0.52], ['May', 0.6], ['Jun', 0.57], ['Jul', 0.68], ['Ago', 0.63], ['Sep', 0.82]]"),
    ('p06_demo.js', "color:transparent\">Qu'aimeriez-vous</span><br>construire aujourd'hui ?",
     "color:transparent\">Cosa vorreste</span><br>costruire oggi?", "color:transparent\">¿Qué le gustaría</span><br>construir hoy?"),
    ('p06_demo.js', "'Décrivez votre idée, DigiCraft lui donne vie.'", "'Descrivete la vostra idea, DigiCraft le dà vita.'", "'Describa su idea, DigiCraft le da vida.'"),
    ('p06_demo.js', "'Décrivez votre projet…'", "'Descrivete il vostro progetto…'", "'Describa su proyecto…'"),
    ('p06_demo.js', "#fff')}Créer`", "#fff')}Crea`", "#fff')}Crear`"),
    ('p06_demo.js', "['chart-column', 'Tableau de bord'], ['file-pen-line', 'Formulaire'], ['users', 'Portail RH']",
     "['chart-column', 'Dashboard'], ['file-pen-line', 'Modulo'], ['users', 'Portale HR']",
     "['chart-column', 'Panel'], ['file-pen-line', 'Formulario'], ['users', 'Portal RR. HH.']"),
    ('p06_demo.js', "[['layout-dashboard', \"Vue d'ensemble\", 1], ['landmark', 'Finance'], ['briefcase', 'Commercial'], ['users', 'Ressources humaines'], ['bell', 'Alertes', 0, '3']]",
     "[['layout-dashboard', 'Panoramica', 1], ['landmark', 'Finanza'], ['briefcase', 'Commerciale'], ['users', 'Risorse umane'], ['bell', 'Avvisi', 0, '3']]",
     "[['layout-dashboard', 'Resumen', 1], ['landmark', 'Finanzas'], ['briefcase', 'Comercial'], ['users', 'Recursos humanos'], ['bell', 'Alertas', 0, '3']]"),
    ('p06_demo.js', "\"Vue d'ensemble groupe\"", "'Panoramica di gruppo'", "'Resumen del grupo'"),
    ('p06_demo.js', "'Septembre 2026 · toutes directions'", "'Settembre 2026 · tutte le direzioni'", "'Septiembre 2026 · todas las direcciones'"),
    ('p06_demo.js', "#0B2545')}Export PDF`", "#0B2545')}Esporta PDF`", "#0B2545')}Exportar PDF`"),
    ('p06_demo.js', "#fff')}Partager au CODIR`", "#fff')}Condividi col CdA`", "#fff')}Compartir con el comité`"),
    ('p06_demo.js', "'Chiffre d’affaires mensuel'", "'Fatturato mensile'", "'Facturación mensual'"),
    ('p06_demo.js', "'Alertes et objectifs'", "'Avvisi e obiettivi'", "'Alertas y objetivos'"),
    ('p06_demo.js', "#fff')}Publier`", "#fff')}Pubblica`", "#fff')}Publicar`"),
    ('p06_demo.js', "#fff')}En ligne`", "#fff')}Online`", "#fff')}En línea`"),
    ('p06_demo.js', "Construction en cours…", "Costruzione in corso…", "Construyendo…"),
    ('p06_demo.js', "')}Votre application est prête`", "')}La vostra app è pronta`", "')}Su aplicación está lista`"),
    ('p06_demo.js', "'Demandez à DigiCraft…'", "'Chiedete a DigiCraft…'", "'Pregunte a DigiCraft…'"),
    ('p06_demo.js', "<span>Aperçu en direct</span>", "<span>Anteprima in diretta</span>", "<span>Vista previa en directo</span>"),
    ('p06_demo.js', "')}Accéléré`", "')}Accelerato`", "')}Acelerado`"),
    ('p06_demo.js', "Votre application est en ligne</div><div style=\"font-size:16px;color:#A9BCD0\">Lien partagé avec votre équipe",
     "La vostra app è online</div><div style=\"font-size:16px;color:#A9BCD0\">Link condiviso con il team",
     "Su aplicación está en línea</div><div style=\"font-size:16px;color:#A9BCD0\">Enlace compartido con su equipo"),
    ('p06_demo.js', "'Rapport de septembre'", "'Report di settembre'", "'Informe de septiembre'"),
    ('p06_demo.js', "\"Chiffre d'affaires\");", "'Fatturato');", "'Facturación');"),
    ('p06_demo.js', "'+6 % vs N-1'", "'+6% vs anno prec.'", "'+6 % vs año ant.'"),
    ('p06_demo.js', "'Marge 31,4 %'", "'Margine 31,4%'", "'Margen 31,4 %'"),
    ('p06_demo.js', ">Objectif atteint</div>", ">Obiettivo raggiunto</div>", ">Objetivo alcanzado</div>"),
    ('p06_demo.js', "Cash-flow opérationnel : +1,4 M€ ce mois-ci.", "Cash flow operativo: +1,4 M€ questo mese.", "Flujo de caja operativo: +1,4 M€ este mes."),
    ('p06_demo.js', "Partagé au CODIR` : `${icon('send', 22, 2.2, '#fff')}Partager au comité de direction`",
     "Condiviso col CdA` : `${icon('send', 22, 2.2, '#fff')}Condividi col comitato direttivo`",
     "Compartido con el comité` : `${icon('send', 22, 2.2, '#fff')}Compartir con el comité de dirección`"),
    ('p06_demo.js', "['Objectifs atteints', '#15803D', '#DCFCE7'] : sent ? ['Envoyé au CODIR', '#B45309', '#FEF3C7'] : ['Mise à jour en direct',",
     "['Obiettivi raggiunti', '#15803D', '#DCFCE7'] : sent ? ['Inviato al CdA', '#B45309', '#FEF3C7'] : ['Aggiornamento in diretta',",
     "['Objetivos alcanzados', '#15803D', '#DCFCE7'] : sent ? ['Enviado al comité', '#B45309', '#FEF3C7'] : ['Actualización en directo',"),

    ('p10_adopte.js', "lab: 'utilisateurs'", "lab: 'utenti'", "lab: 'usuarios'"),
    ('p10_adopte.js', "lab: 'applications déployées'", "lab: 'app distribuite'", "lab: 'aplicaciones desplegadas'"),
    ('p10_adopte.js', "[{ t: 'Déjà' }, { t: 'adopté' }, { t: 'par' }, { t: 'de' }, { t: 'nombreux', c: 'o-light' }, { t: 'utilisateurs.', c: 'o-light' }]",
     "[{ t: 'Già' }, { t: 'adottato' }, { t: 'da' }, { t: 'molti', c: 'o-light' }, { t: 'utenti.', c: 'o-light' }]",
     "[{ t: 'Ya' }, { t: 'adoptado' }, { t: 'por' }, { t: 'numerosos', c: 'o-light' }, { t: 'usuarios.', c: 'o-light' }]"),

    ('p11_serenite.js', "[{ t: 'Sérénité' }, { t: 'et' }, { t: 'sécurité' }, { t: 'assurées.', c: 'o' }]",
     "[{ t: 'Serenità' }, { t: 'e' }, { t: 'sicurezza' }, { t: 'garantite.', c: 'o' }]",
     "[{ t: 'Tranquilidad' }, { t: 'y' }, { t: 'seguridad' }, { t: 'garantizadas.', c: 'o' }]"),
    ('p11_serenite.js', "'Hébergé en Europe', 'Vos données restent dans l’Union européenne.'",
     "'Ospitato in Europa', 'I vostri dati restano nell’Unione europea.'", "'Alojado en Europa', 'Sus datos permanecen en la Unión Europea.'"),
    ('p11_serenite.js', "'Gouvernance intégrée', 'Accès, droits et traçabilité maîtrisés.'",
     "'Governance integrata', 'Accessi, permessi e tracciabilità sotto controllo.'", "'Gobernanza integrada', 'Accesos, permisos y trazabilidad bajo control.'"),
    ('p11_serenite.js', "'Intuitif dès la première minute', 'Aucune formation ni ligne de code nécessaire.'",
     "'Intuitivo fin dal primo minuto', 'Nessuna formazione né riga di codice necessaria.'", "'Intuitivo desde el primer minuto', 'Sin formación ni una sola línea de código.'"),
    ('p11_serenite.js', "</div>Pilotage DG`);", "</div>Cruscotto DG`);", "</div>Panel DG`);"),
    ('p11_serenite.js', "['Vue d’ensemble', 'Finance', 'Commercial', 'Ressources humaines', 'Alertes']",
     "['Panoramica', 'Finanza', 'Commerciale', 'Risorse umane', 'Avvisi']", "['Resumen', 'Finanzas', 'Comercial', 'Recursos humanos', 'Alertas']"),
    ('p11_serenite.js', "'Vue d’ensemble groupe'", "'Panoramica di gruppo'", "'Resumen del grupo'"),
    ('p11_serenite.js', "[['Chiffre d’affaires', '48,2 M€'], ['Marge brute', '31,4 %'], ['Effectifs', '1 240'], ['Trésorerie', '12,8 M€']]",
     "[['Fatturato', '48,2 M€'], ['Margine lordo', '31,4%'], ['Organico', '1.240'], ['Cassa', '12,8 M€']]",
     "[['Facturación', '48,2 M€'], ['Margen bruto', '31,4 %'], ['Plantilla', '1240'], ['Tesorería', '12,8 M€']]"),
    ('p11_serenite.js', "'Chiffre d’affaires mensuel'", "'Fatturato mensile'", "'Facturación mensual'"),

    ('p12_cree.js', "'Créé par vous,'", "'Creato da voi,'", "'Creado por usted,'"),
    ('p12_cree.js', "'pour vous.'", "'per voi.'", "'para usted.'"),

    ('index.html', '<html lang="fr">', '<html lang="it">', '<html lang="es">'),
]
# « Pilotage DG » apparaît à plusieurs endroits de la démo : remplacé partout.
ALL = [('p06_demo.js', 'Pilotage DG', 'Cruscotto DG', 'Panel DG')]

if os.path.exists(DST):
    shutil.rmtree(DST)
shutil.copytree(SRC, DST)
bad = 0
for f, fr, it, es in T:
    p = os.path.join(DST, f)
    s = open(p, encoding='utf-8').read()
    if fr not in s:
        print('MANQUANT', f, fr[:80]); bad += 1; continue
    s = s.replace(fr, (it, es)[I])
    open(p, 'w', encoding='utf-8').write(s)
for f, fr, it, es in ALL:
    p = os.path.join(DST, f)
    s = open(p, encoding='utf-8').read()
    open(p, 'w', encoding='utf-8').write(s.replace(fr, (it, es)[I]))
print(LANG, 'ok' if not bad else f'{bad} manquants')
