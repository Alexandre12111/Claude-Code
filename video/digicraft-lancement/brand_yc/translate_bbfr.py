"""Version française allongée de « Bring your ideas to life » YouCraft : src_bb -> src_bbfr.
Usage : python3 translate_bbfr.py src_bb src_bbfr
- traduit tous les textes à l'écran (film et écrans d'applications) avec les formats français (48,2 M€, 94,2 %, 3 nov.) ;
- ajoute la table de ralenti (bbfr/warp.js) : le film passe de 54 à 79 s, voir audio/warp_bbfr.py.
Chaque texte remplacé doit exister dans la source, sinon le script s'arrête."""
import os, re, shutil, sys

HERE = os.path.dirname(os.path.abspath(__file__))
src, dst = sys.argv[1], sys.argv[2]
if os.path.exists(dst):
    shutil.rmtree(dst)
shutil.copytree(src, dst)


def edit(name, pairs, regex=False):
    p = os.path.join(dst, name)
    s = open(p, encoding='utf-8').read()
    for a, b in pairs:
        if regex:
            s, n = re.subn(a, b, s, flags=re.S)
        else:
            n = s.count(a)
            s = s.replace(a, b)
        assert n >= 1, (name, a[:80])
    open(p, 'w', encoding='utf-8').write(s)


CHIPS = ("['Expense approvals', 'Sales pipeline', 'Supplier audits', 'HR onboarding']", "['Notes de frais', 'Pipeline commercial', 'Audits fournisseurs', 'Onboarding RH']")

edit('index.html', [('<html lang="en">', '<html lang="fr">'), ('<title>YouCraft Bring your ideas to life</title>', '<title>YouCraft Donnez vie à vos idées</title>'),
                    ('<script src="main.js"></script>', '<script src="main.js"></script>\n<script src="warp.js"></script>')])
shutil.copy(os.path.join(HERE, 'bbfr', 'warp.js'), os.path.join(dst, 'warp.js'))

edit('s4_v2.js', [
    ("'Imagine being able to build'.split(' ')", "'Imaginez pouvoir créer'.split(' ')"),
    ("top: '470px', textAlign: 'center', fontSize: '210px'", "top: '500px', textAlign: 'center', fontSize: '96px'"),
    ("E.chars(a.l2, 'any app')", "E.chars(a.l2, 'n’importe quelle application')"),
])
edit('s1_intro.js', [("['Just', 'by', 'describing', 'it.']", "['Simplement', 'en', 'la', 'décrivant.']")])
edit('s2_build.js', [
    ("'Build a client onboarding portal for our consulting team, with document upload and manager approval.'", "'Crée un portail d’onboarding client pour notre équipe conseil, avec dépôt de documents et validation du manager.'"),
    ("'Add a manager dashboard with onboarding status by client.'", "'Ajoute un tableau de bord manager par client.'"),
    ("'Templates<span>Docs</span>", "'Modèles<span>Aide</span>"), (">Sign in</span>", ">Connexion</span>"),
    ("'What would you like to <span class=\"dcgrad\">build today?</span>'", "'Que voulez-vous <span class=\"dcgrad\">créer aujourd’hui ?</span>'"),
    ("'Describe your idea. YouCraft builds the app, ready for your team.'", "'Décrivez votre idée. YouCraft crée l’application, prête pour votre équipe.'"),
    CHIPS,
    ("Great idea. I'm building your client onboarding portal now.", "Bonne idée. Je crée votre portail d’onboarding client."),
    ("['Creating the data model', 'Building client pages', 'Adding document upload', 'Setting up manager approval', 'Applying your company branding']",
     "['Création du modèle de données', 'Pages clients', 'Dépôt de documents', 'Validation par le manager', 'Charte de votre entreprise']"),
    ("Done. Managers now see onboarding status for every client.", "C’est fait. Les managers suivent chaque client."),
    ("Ask YouCraft to change anything…", "Demandez une modification à YouCraft…"),
    ("'Creating your app'", "'Création de votre application'"),
    ("['Designing screens…', 'Wiring the data…', 'Almost there…']", "['Conception des écrans…', 'Connexion des données…', 'Presque prêt…']"),
    ("Live for your team · client-portal.youcraft.app", "En ligne pour votre équipe · portail-client.youcraft.app"),
    ("Published`", "Publié`"), ("Publish`", "Publier`"),
    ("Describe the app your team needs…", "Décrivez l’application dont votre équipe a besoin…"),
    ("<span>Attach a process doc</span>", "<span>Joindre une procédure</span>"),
    ("'Not sure where to start?'", "'Besoin d’inspiration ?'"),
])
edit('s3_scale.js', [
    ("'Expense approval workflow for the finance team'", "'Validation des notes de frais pour la finance'"),
    ("'Sales pipeline tracker for our EMEA region'", "'Suivi du pipeline commercial EMEA'"),
    ("'Supplier audit platform for 40 production sites'", "'Audits fournisseurs de nos 40 usines'"),
    ("'Group-wide ESG reporting hub'", "'Reporting ESG pour tout le groupe'"),
    ("'Maintenance planning app for our plants'", "'Planning de maintenance des usines'"),
])
edit('s5_v3.js', [
    ("'Access & roles'", "'Accès et rôles'"), ("'Screens'", "'Écrans'"), ("'Workflows'", "'Processus'"), ("'server', 'Data'", "'server', 'Données'"),
    ("['Clients', 'Approvals', 'Avg. time']", "['Clients', 'Validations', 'Délai moyen']"), ("['24', '5', '6.5 d']", "['24', '5', '6,5 j']"), ("['+3', '−2', '−1.2 d']", "['+3', '−2', '−1,2 j']"),
    ("['Request', 'Review', 'Approve', 'Notify']", "['Demande', 'Revue', 'Validation', 'Alerte']"),
])
edit('s6_v4.js', [
    ("'No code.'", "'Sans code.'"), ("'No IT ticket.'", "'Sans ticket IT.'"), ("'No limits.'", "'Sans limites.'"), ("'Built in minutes.'", "'Créée en quelques minutes.'"),
    ("'Just ask.'", "'Demandez.'"), ("'Enterprise‑grade.'", "'Niveau entreprise.'"), ("'Secure by design.'", "'Sécurisée par conception.'"), ("Request your demo", "Demandez votre démo"),
])
edit('s8_yc.js', [
    ("includes('Request your demo')", "includes('Demandez votre démo')"), ("h('span', '', a.by, 'by');", "h('span', '', a.by, 'par');"),
    ("`Can you <span style=\"color:${UMD}\">imagine it?</span>`", "`Vous <span style=\"color:${UMD}\">l’imaginez ?</span>`"),
    ("`Let’s make it <span style=\"color:${LIME}\">real.</span>`", "`Donnons-lui <span style=\"color:${LIME}\">vie.</span>`"),
    ("['HR onboarding', 'Fleet tracking', 'Warehouse stock', 'Maintenance planner', 'Sales pipeline', 'Shift planning', 'Delivery tracker', 'Recruitment', 'ESG reporting', 'IT helpdesk', 'Expense approvals', 'Site audits', 'Client portal', 'Board reporting']",
     "['Onboarding RH', 'Suivi de flotte', 'Stock entrepôt', 'Maintenance', 'Pipeline commercial', 'Planning des équipes', 'Suivi des livraisons', 'Recrutement', 'Reporting ESG', 'Support IT', 'Notes de frais', 'Audits de site', 'Portail client', 'Reporting direction']"),
])
edit('s9_app.js', [
    ("'HR onboarding', 'People'", "'Onboarding RH', 'RH'"), ("'Fleet control tower', 'Supply chain'", "'Tour de contrôle', 'Supply chain'"), ("'Asset monitoring', 'Operations'", "'Supervision machines', 'Opérations'"),
    ("'Sales pipeline', 'Sales'", "'Pipeline commercial', 'Ventes'"), ("'Warehouse inventory', 'Logistics'", "'Inventaire entrepôt', 'Logistique'"), ("'Shift planning', 'Operations'", "'Planning des équipes', 'Opérations'"),
    ("'Client portal', 'Consulting'", "'Portail client', 'Conseil'"), ("} · template`", "} · modèle`"), ("'Ready to use'", "'Prêt à l’emploi'"),
    ("'Start from an idea, or a template.'", "'Partez d’une idée, ou d’un modèle.'"),
    ("['lock', 'Secure sign-in'], ['shield-check', 'Roles & permissions'], ['server', 'Hosted in Europe'], ['rocket', 'One-click publish']",
     "['lock', 'Connexion sécurisée'], ['shield-check', 'Rôles et droits'], ['server', 'Hébergé en Europe'], ['rocket', 'Publication en un clic']"),
    ("'people.northwind.youcraft.app/onboarding'", "'rh.northwind.youcraft.app/integration'"), ("'control-tower.northwind.youcraft.app/live-map'", "'tour-de-controle.northwind.youcraft.app/carte'"),
    ("'asset-monitor.northwind.youcraft.app/sensors/PR-LY-004'", "'supervision.northwind.youcraft.app/capteurs/PR-LY-004'"),
])
FR = "v.toFixed(1).replace('.', ',')"
edit('app_yc.js', [
    # Coque et briques communes.
    ("Search<span", "Rechercher<span"), ("'SAVED VIEWS'", "'VUES ENREGISTRÉES'"), ("['My team', 'This week', 'Needs attention']", "['Mon équipe', 'Cette semaine', 'À traiter']"),
    ("vs last quarter", "vs trim. préc."),
    # RH.
    ("crumbs: ['People', 'Onboarding', 'Q4 2026'], title: 'Onboarding'", "crumbs: ['RH', 'Intégration', 'T4 2026'], title: 'Intégration des arrivants'"),
    ("[['house', 'Home'], ['users', 'Employees'], ['user-plus', 'Onboarding', '9'], ['target', 'Recruiting', '12'], ['calendar', 'Time off'], ['layers', 'Org chart'], ['chart-column', 'Reports'], ['settings', 'Settings']]",
     "[['house', 'Accueil'], ['users', 'Collaborateurs'], ['user-plus', 'Intégration', '9'], ['target', 'Recrutement', '12'], ['calendar', 'Absences'], ['layers', 'Organigramme'], ['chart-column', 'Rapports'], ['settings', 'Paramètres']]"),
    ("live('Live') + `<span style=\"color:${TX3}\">·</span>48 new hires across 6 departments`", "live('En direct') + `<span style=\"color:${TX3}\">·</span>48 arrivées dans 6 départements`"),
    ("btn('Oct 1 – Dec 31, 2026', false, 'calendar') + btn('Filters', false, 'list-checks') + btn('Add new hire', true, 'plus')", "btn('1 oct. – 31 déc. 2026', false, 'calendar') + btn('Filtres', false, 'list-checks') + btn('Nouvelle arrivée', true, 'plus')"),
    ("'New hires this quarter'", "'Arrivées ce trimestre'"),
    ("'Ready on day one', (v) => v.toFixed(1) + '%', 94.2, '+3.1 pts'", f"'Prêts dès le jour J', (v) => {FR} + ' %', 94.2, '+3,1 pts'"),
    ("'Time to productivity', (v) => v.toFixed(1) + ' days', 18.4, '−2.6 days'", f"'Délai de prise de poste', (v) => {FR} + ' jours', 18.4, '−2,6 jours'"),
    ("'Open onboarding tasks'", "'Tâches d’intégration ouvertes'"), ("'Upcoming starts'", "'Prochaines arrivées'"), ("Search people", "Rechercher"), ("btn('Export', false, 'download')", "btn('Exporter', false, 'download')"),
    ("['All <b>48</b>', 'This week <b>9</b>', 'Blocked <b>3</b>', 'Completed <b>21</b>']", "['Toutes <b>48</b>', 'Cette semaine <b>9</b>', 'Bloquées <b>3</b>', 'Terminées <b>21</b>']"),
    ("[['EMPLOYEE', 196], ['DEPARTMENT', 118], ['START DATE', 98], ['MANAGER', 152], ['ONBOARDING', 148], ['STATUS', 160]]", "[['COLLABORATEUR', 196], ['DÉPARTEMENT', 118], ['ARRIVÉE', 98], ['MANAGER', 152], ['INTÉGRATION', 148], ['STATUT', 160]]"),
    ("badge('Contract signed', 'ok') : badge('Contract unsigned', 'bad')", "badge('Contrat signé', 'ok') : badge('Contrat non signé', 'bad')"),
    ("'Live activity'", "'Activité en direct'"), ("${live('')}Live</span>", "${live('')}En direct</span>"), ("'Hires by department'", "'Arrivées par département'"),
    ("['Cons.', 'Data', 'Ops', 'Eng.', 'Fin.', 'Sales', 'HR']", "['Cons.', 'Data', 'Ops', 'Ing.', 'Fin.', 'Ventes', 'RH']"),
    # Logistique.
    ("crumbs: ['Logistics', 'Live map'], title: 'Live map'", "crumbs: ['Logistique', 'Carte en direct'], title: 'Carte en direct'"),
    ("['map-pin', 'Live map'], ['inbox', 'Shipments', '214'], ['circle-alert', 'Exceptions', '!4'], ['building-2', 'Carriers'], ['house', 'Warehouses'], ['chart-column', 'Analytics']]",
     "['map-pin', 'Carte en direct'], ['inbox', 'Expéditions', '214'], ['circle-alert', 'Incidents', '!4'], ['building-2', 'Transporteurs'], ['house', 'Entrepôts'], ['chart-column', 'Analyses']]"),
    ("live('Live · updated 2 s ago') + `<span style=\"color:${TX3}\">·</span>38 trucks in transit · Europe`", "live('En direct · mis à jour il y a 2 s') + `<span style=\"color:${TX3}\">·</span>38 camions en transit · Europe`"),
    ("btn('Region: Europe', false, 'globe') + btn('Status: In transit', false, 'list-checks') + btn('Share', false, 'external-link')", "btn('Région : Europe', false, 'globe') + btn('Statut : en transit', false, 'list-checks') + btn('Partager', false, 'external-link')"),
    ("[[UM, 'In transit 34'], ['#CF3B3B', 'Delayed 4'], ['#1C8C4E', 'Delivered today 61']]", "[[UM, 'En transit 34'], ['#CF3B3B', 'En retard 4'], ['#1C8C4E', 'Livrés aujourd’hui 61']]"),
    ("`TR-2041 ${badge('Delayed', 'bad')}`", "`TR-2041 ${badge('En retard', 'bad')}`"), ("'Paris DC → Milan DC · TransAlp Logistics'", "'Paris → Milan · TransAlp Logistics'"),
    ("'Estimated arrival'", "'Arrivée estimée'"), ("'Planned 14:30<br>Customer notified ✓'", "'Prévue 14:30<br>Client prévenu ✓'"),
    ("[['Paris DC', 'Departed 06:12', 'ok'], ['Lyon hub', 'Departed 10:48', 'ok'], ['A43 · Chambéry', 'Heavy traffic · 22 min stop', 'bad'], ['Turin cross‑dock', 'ETA 13:40 → 14:15', 'neu'], ['Milan DC', 'ETA 15:05', 'neu']]",
     "[['Entrepôt de Paris', 'Parti à 06:12', 'ok'], ['Hub de Lyon', 'Parti à 10:48', 'ok'], ['A43 · Chambéry', 'Trafic dense · arrêt de 22 min', 'bad'], ['Cross-dock de Turin', 'Arrivée 13:40 → 14:15', 'neu'], ['Entrepôt de Milan', 'Arrivée 15:05', 'neu']]"),
    ("Driver · +33", "Chauffeur · +33"), ("18 pallets · 12.4 t<br>", "18 palettes · 12,4 t<br>"), ("4.1 °C</b> reefer", "4,1 °C</b> frigo"), ("(4.1 + 0.1 * Math.sin(T * 3)).toFixed(1)} °C</b> reefer", "(4.1 + 0.1 * Math.sin(T * 3)).toFixed(1).replace('.', ',')} °C</b> frigo"),
    ("404, 260, 'Exceptions'", "404, 260, 'Incidents'"), (">View all</span>", ">Tout voir</span>"),
    ("[['TR-2041', 'Traffic on A43 · +35 min', 'bad', '2 min'], ['TR-0906', 'Loading delay at Lyon hub · +12 min', 'warn', '18 min'], ['TR-3877', 'Temperature drift 6.2 °C', 'warn', '41 min'], ['TR-1530', 'Missing POD signature', 'neu', '1 h']]",
     "[['TR-2041', 'Bouchon sur l’A43 · +35 min', 'bad', '2 min'], ['TR-0906', 'Chargement retardé à Lyon · +12 min', 'warn', '18 min'], ['TR-3877', 'Dérive de température 6,2 °C', 'warn', '41 min'], ['TR-1530', 'Signature de livraison manquante', 'neu', '1 h']]"),
    ("`Live · updated ${1 + (Math.floor(u * 2) % 4)} s ago`", "`En direct · mis à jour il y a ${1 + (Math.floor(u * 2) % 4)} s`"),
    # Maintenance.
    ("crumbs: ['Lyon plant', 'Press line', 'Press #4'], title: 'Press #4 · Hydraulic press 800 t'", "crumbs: ['Usine de Lyon', 'Ligne de presses', 'Presse n° 4'], title: 'Presse n° 4 · Presse hydraulique 800 t'"),
    ("['server', 'Assets'], ['activity', 'Sensors'], ['clipboard-check', 'Work orders', '23'], ['layers', 'Spare parts'], ['calendar', 'Schedules']]",
     "['server', 'Équipements'], ['activity', 'Capteurs'], ['clipboard-check', 'Ordres de travail', '23'], ['layers', 'Pièces détachées'], ['calendar', 'Plannings']]"),
    ("live('Streaming · 50 Hz') + `<span style=\"color:${TX3}\">·</span>Asset ID PR-LY-004 · 18 sensors`", "live('Flux en direct · 50 Hz') + `<span style=\"color:${TX3}\">·</span>Machine PR-LY-004 · 18 capteurs`"),
    ("btn('Last 15 minutes', false, 'clock') + btn('Compare', false, 'chart-column') + btn('Create work order', true, 'plus')", "btn('15 dernières minutes', false, 'clock') + btn('Comparer', false, 'chart-column') + btn('Créer un ordre de travail', true, 'plus')"),
    ("'Health score'", "'Score de santé'"), ("(v) => v.toFixed(2) + ' mm/s'", "(v) => v.toFixed(2).replace('.', ',') + ' mm/s'"), ("'+38%'", "'+38 %'"),
    ("'Oil temperature', (v) => v.toFixed(1) + ' °C', 71.4, '+6.2 °C'", f"'Température huile', (v) => {FR} + ' °C', 71.4, '+6,2 °C'"),
    ("'Plant OEE today', (v) => v.toFixed(1) + '%', 87.4, '+2.1 pts'", f"'TRS usine aujourd’hui', (v) => {FR} + ' %', 87.4, '+2,1 pts'"),
    ("'Vibration · live'", "'Vibration · en direct'"), (">Axis X</span>", ">Axe X</span>"), (">Axis Y</span>", ">Axe Y</span>"), ("Threshold ${fmt(thr)}", "Seuil ${fmt(thr)}"),
    ("Anomaly detected · 10:44:12", "Anomalie détectée · 10:44:12"), ("`${vx[idx].toFixed(2)} mm/s`", "`${vx[idx].toFixed(2).replace('.', ',')} mm/s`"),
    ("'Lyon plant · assets'", "'Usine de Lyon · équipements'"),
    ("[['Press #4', 62, 'bad', 'Alert'], ['Press #2', 91, 'ok', 'Running'], ['Welding cell W3', 78, 'warn', 'Watch'], ['Paint booth P1', 94, 'ok', 'Running'], ['Conveyor C7', 71, 'warn', 'Belt wear'], ['Robot R12', 96, 'ok', 'Running']]",
     "[['Presse n° 4', 62, 'bad', 'Alerte'], ['Presse n° 2', 91, 'ok', 'En marche'], ['Cellule de soudage W3', 78, 'warn', 'À surveiller'], ['Cabine de peinture P1', 94, 'ok', 'En marche'], ['Convoyeur C7', 71, 'warn', 'Usure courroie'], ['Robot R12', 96, 'ok', 'En marche']]"),
    ("1320, 296, 'Work orders'", "1320, 296, 'Ordres de travail'"), ("btn('All sites', false, 'building-2')}${btn('Priority', false, 'list-checks')", "btn('Tous les sites', false, 'building-2')}${btn('Priorité', false, 'list-checks')"),
    ("[[22, 'Karim Benali', 'WO-1287', 'Press #4 · vibration above threshold', 'Lyon', ['High', 'bad'], 'SLA', ['Assigned', 'info']], [18, 'Hugo Laurent', 'WO-1286', 'Robot R2 · gearbox temperature', 'Łódź', ['Medium', 'warn'], '1 d 04:20', ['In progress', 'warn']], [17, 'Kwame Mensah', 'WO-1281', 'Conveyor C7 · replace belt', 'Turin', ['Medium', 'warn'], '2 d 11:05', ['Parts ordered', 'neu']], [12, 'Julien Leroy', 'WO-1279', 'Paint booth P1 · filter change', 'Lyon', ['Low', 'neu'], '5 d 02:40', ['Scheduled', 'neu']]]",
     "[[22, 'Karim Benali', 'OT-1287', 'Presse n° 4 · vibration au-dessus du seuil', 'Lyon', ['Haute', 'bad'], 'SLA', ['Assigné', 'info']], [18, 'Hugo Laurent', 'OT-1286', 'Robot R2 · température du réducteur', 'Łódź', ['Moyenne', 'warn'], '1 j 04:20', ['En cours', 'warn']], [17, 'Kwame Mensah', 'OT-1281', 'Convoyeur C7 · changer la courroie', 'Turin', ['Moyenne', 'warn'], '2 j 11:05', ['Pièces commandées', 'neu']], [12, 'Julien Leroy', 'OT-1279', 'Cabine P1 · changement de filtre', 'Lyon', ['Basse', 'neu'], '5 j 02:40', ['Planifié', 'neu']]]"),
    ("[['ID', 100], ['ASSET & ISSUE', 380], ['SITE', 110], ['PRIORITY', 130], ['ASSIGNEE', 230], ['SLA REMAINING', 170], ['STATUS', 150]]", "[['ID', 100], ['MACHINE ET PROBLÈME', 380], ['SITE', 110], ['PRIORITÉ', 130], ['TECHNICIEN', 230], ['DÉLAI RESTANT', 170], ['STATUT', 150]]"),
    # CRM.
    ("crumbs: ['Sales', 'Pipeline', 'EMEA']", "crumbs: ['Ventes', 'Pipeline', 'EMEA']"),
    ("['building-2', 'Accounts'], ['users', 'Contacts'], ['calendar', 'Activities', '8'], ['chart-column', 'Forecast'], ['settings', 'Settings']]", "['building-2', 'Comptes'], ['users', 'Contacts'], ['calendar', 'Activités', '8'], ['chart-column', 'Prévisions'], ['settings', 'Paramètres']]"),
    ("`€12.6M open · 92 deals · Q4 target <b style=\"color:${TX}\">€4.2M</b>`", "`12,6 M€ en cours · 92 affaires · objectif T4 <b style=\"color:${TX}\">4,2 M€</b>`"),
    ("btn('Owner: All', false, 'users') + btn('Close date: Q4', false, 'calendar') + btn('New deal', true, 'plus')", "btn('Responsable : tous', false, 'users') + btn('Clôture : T4', false, 'calendar') + btn('Nouvelle affaire', true, 'plus')"),
    ("[['Lead', '€3.1M', 42], ['Qualified', '€2.8M', 27], ['Proposal', '€3.9M', 14], ['Negotiation', '€1.9M', 6], ['Won', '€0.9M', 3]]", "[['Prospect', '3,1 M€', 42], ['Qualifié', '2,8 M€', 27], ['Proposition', '3,9 M€', 14], ['Négociation', '1,9 M€', 6], ['Gagné', '0,9 M€', 3]]"),
    # Stock.
    ("crumbs: ['Warehouses', 'Lyon DC', 'Inventory'], title: 'Inventory · Lyon DC'", "crumbs: ['Entrepôts', 'Entrepôt de Lyon', 'Inventaire'], title: 'Inventaire · Entrepôt de Lyon'"),
    ("['layers', 'Inventory'], ['inbox', 'Inbound', '6'], ['send', 'Outbound'], ['circle-alert', 'Reorders', '!3'], ['building-2', 'Suppliers'], ['chart-column', 'Reports']]", "['layers', 'Inventaire'], ['inbox', 'Réceptions', '6'], ['send', 'Expéditions'], ['circle-alert', 'Réassorts', '!3'], ['building-2', 'Fournisseurs'], ['chart-column', 'Rapports']]"),
    ("live('Synced with ERP 1 min ago') + `<span style=\"color:${TX3}\">·</span>2,418 SKUs`", "live('Synchronisé avec l’ERP il y a 1 min') + `<span style=\"color:${TX3}\">·</span>2 418 références`"),
    ("btn('All categories', false, 'list-checks') + btn('Create PO', true, 'plus')", "btn('Toutes catégories', false, 'list-checks') + btn('Créer une commande', true, 'plus')"),
    ("'Stock value', () => '€8.42M', 0, '+1.8%'", "'Valeur du stock', () => '8,42 M€', 0, '+1,8 %'"), ("'Days of cover', () => '23.6 days', 0, '−1.2 days'", "'Couverture', () => '23,6 jours', 0, '−1,2 jour'"),
    ("'Below reorder point', () => '3 SKUs'", "'Sous le seuil de réassort', () => '3 réf.'"), ("'Order accuracy', () => '99.6%', 0, '+0.2 pts'", "'Fiabilité des commandes', () => '99,6 %', 0, '+0,2 pt'"),
    ("'Stock levels'", "'Niveaux de stock'"),
    ("[['SKU', 130], ['ITEM', 300], ['CATEGORY', 150], ['STOCK LEVEL', 250], ['ON HAND', 110, 'right'], ['REORDER AT', 120, 'right'], ['STATUS', 110], ['INBOUND', 130]]", "[['RÉF.', 130], ['ARTICLE', 300], ['CATÉGORIE', 150], ['NIVEAU', 250], ['EN STOCK', 110, 'right'], ['SEUIL', 120, 'right'], ['STATUT', 110], ['À RECEVOIR', 130]]"),
    # Planning.
    ("crumbs: ['Lyon plant', 'Schedules', 'Week 45'], title: 'Week 45 · Nov 3 – Nov 9'", "crumbs: ['Usine de Lyon', 'Plannings', 'Semaine 45'], title: 'Semaine 45 · 3 – 9 nov.'"),
    ("['calendar', 'Schedule'], ['users', 'Team', '64'], ['clock', 'Time off', '5'], ['circle-alert', 'Open shifts', '!2'], ['chart-column', 'Reports']]", "['calendar', 'Planning'], ['users', 'Équipe', '64'], ['clock', 'Absences', '5'], ['circle-alert', 'Postes à pourvoir', '!2'], ['chart-column', 'Rapports']]"),
    ("`Coverage <b style=\"color:#1C8C4E\">97%</b><span style=\"color:${TX3}\">·</span>2 open shifts`", "`Couverture <b style=\"color:#1C8C4E\">97 %</b><span style=\"color:${TX3}\">·</span>2 postes à pourvoir`"),
    ("btn('Line: Press & Weld', false, 'list-checks') + btn('Publish schedule', true, 'send')", "btn('Ligne : presses et soudure', false, 'list-checks') + btn('Publier le planning', true, 'send')"),
    ("['Mon 3', 'Tue 4', 'Wed 5', 'Thu 6', 'Fri 7', 'Sat 8', 'Sun 9']", "['Lun. 3', 'Mar. 4', 'Mer. 5', 'Jeu. 6', 'Ven. 7', 'Sam. 8', 'Dim. 9']"), ("'TEAM MEMBER'", "'COLLABORATEUR'"),
    ("[[22, 'Karim Benali', 'Technician'], [1, 'Mateo García', 'Operator'], [17, 'Kwame Mensah', 'Operator'], [2, 'Inès Duval', 'Line lead'], [18, 'Hugo Laurent', 'Welder'], [13, 'Sofia Alves', 'Quality'], [3, 'Tom Becker', 'Operator'], [11, 'Emma Novak', 'Welder'], [23, 'Ryan Cole', 'Maintenance'], [14, 'Léa Bernard', 'Operator']]",
     "[[22, 'Karim Benali', 'Technicien'], [1, 'Mateo García', 'Opérateur'], [17, 'Kwame Mensah', 'Opérateur'], [2, 'Inès Duval', 'Cheffe de ligne'], [18, 'Hugo Laurent', 'Soudeur'], [13, 'Sofia Alves', 'Qualité'], [3, 'Tom Becker', 'Opérateur'], [11, 'Emma Novak', 'Soudeuse'], [23, 'Ryan Cole', 'Maintenance'], [14, 'Léa Bernard', 'Opératrice']]"),
    ("['Press line', 'Weld cell', 'Paint']", "['Presses', 'Soudure', 'Peinture']"), ("Open shift`", "Poste à pourvoir`"),
    # Portail client.
    ("[['layout-dashboard', 'Dashboard'], ['building-2', 'Clients'], ['folder', 'Documents'], ['clipboard-check', 'Approvals', '5'], ['message-square', 'Messages', '2'], ['settings', 'Settings']]",
     "[['layout-dashboard', 'Tableau de bord'], ['building-2', 'Clients'], ['folder', 'Documents'], ['clipboard-check', 'Validations', '5'], ['message-square', 'Messages', '2'], ['settings', 'Paramètres']]"),
    ("Client Portal`", "Portail client`"), (">All clients</b>", ">Tous les clients</b>"), ("Search clients", "Rechercher un client"),
    ("[['Active clients', '24', '+3', 'users'], ['In onboarding', '9', '+2', 'loader-circle'], ['Avg. time to onboard', '6.5 d', '−1.2 d', 'timer'], ['Pending approvals', '5', '−2', 'clipboard-check']]",
     "[['Clients actifs', '24', '+3', 'users'], ['En intégration', '9', '+2', 'loader-circle'], ['Délai moyen d’intégration', '6,5 j', '−1,2 j', 'timer'], ['Validations en attente', '5', '−2', 'clipboard-check']]"),
    ("this month</span>", "ce mois-ci</span>"), ("['All clients <b>24</b>', 'Onboarding <b>9</b>', 'Awaiting documents <b>4</b>', 'Approved <b>11</b>']", "['Tous <b>24</b>', 'En intégration <b>9</b>', 'Documents manquants <b>4</b>', 'Validés <b>11</b>']"),
    ("btn('Add client', true, 'plus')", "btn('Ajouter un client', true, 'plus')"), ("'Onboarding', 'info'", "'En cours', 'info'"), ("'Approved', 'ok'", "'Validé', 'ok'"), ("'Awaiting docs', 'warn'", "'Docs manquants', 'warn'"),
    ("[['CLIENT', 230], ['CONTACT', 200], ['STATUS', 150], ['DOCUMENTS', 190], ['DUE', 90], ['', 50]]", "[['CLIENT', 230], ['CONTACT', 200], ['STATUT', 150], ['DOCUMENTS', 190], ['ÉCHÉANCE', 90], ['', 50]]"),
    ("['layout-dashboard', 'Overview']", "['layout-dashboard', 'Vue d’ensemble']"),
])
# Blocs de données RH, flux d'activité et stock, montants et dates des affaires.
edit('app_yc.js', [
    (r"const ROWS = \[\n.*?\n    \];\n", """const ROWS = [
      [0, 'Sarah Martin', 'Consultante', 'Conseil', 'lun. 3 nov.', 5, 'David Cohen', 0.92, '11/12', ['Dans les temps', 'ok']],
      [3, 'Tom Becker', 'Data analyst', 'Data & IA', 'lun. 3 nov.', 4, 'Elena Rossi', 0.66, '8/12', ['PC en livraison', 'warn']],
      [2, 'Inès Duval', 'Cheffe de projet', 'Opérations', 'mar. 4 nov.', 20, 'Paul Martin', 0.84, '10/12', ['Dans les temps', 'ok']],
      [7, 'Marc Petit', 'Fiscaliste', 'Finance', 'mer. 5 nov.', 5, 'David Cohen', 0.33, '4/12', ['Contrat non signé', 'bad']],
      [9, 'Amara Okafor', 'HR partner', 'RH', 'lun. 10 nov.', 19, 'Claire Dubois', 1, '12/12', ['Prête', 'ok']],
      [10, 'Lucas Moreau', 'Ingénieur', 'Ingénierie', 'lun. 10 nov.', 4, 'Elena Rossi', 0.58, '7/12', ['Accès en attente', 'warn']],
      [6, 'Yuki Tanaka', 'Product designer', 'Produit', 'lun. 17 nov.', 20, 'Paul Martin', 0.75, '9/12', ['Dans les temps', 'ok']],
      [16, 'Priya Nair', 'Analyste', 'Data & IA', 'lun. 17 nov.', 4, 'Elena Rossi', 0.5, '6/12', ['Dans les temps', 'ok']],
      [15, 'Jonas Weber', 'Resp. opérations', 'Opérations', 'lun. 1 déc.', 20, 'Paul Martin', 0.25, '3/12', ['Pas commencé', 'neu']],
      [13, 'Sofia Alves', 'Acheteuse', 'Achats', 'lun. 1 déc.', 5, 'David Cohen', 0.17, '2/12', ['Pas commencé', 'neu']],
      [1, 'Ryan Cole', 'Resp. commercial', 'Ventes', 'lun. 8 déc.', 19, 'Claire Dubois', 0.08, '1/12', ['Pas commencé', 'neu']],
    ];
"""),
    (r"const FEED = \[.*?\];\n", "const FEED = [[22, '<b>IT</b> a expédié un PC à <b>Tom Becker</b>', 'à l’instant', 'smartphone'], [0, '<b>Sarah Martin</b> a fini la formation sécurité', 'il y a 2 min', 'graduation-cap'], [5, '<b>David Cohen</b> a validé les accès de <b>M. Petit</b>', 'il y a 6 min', 'shield-check'], [9, '<b>Amara Okafor</b> est prête pour le jour J', 'il y a 14 min', 'circle-check'], [20, '<b>Paul Martin</b> a réservé un déjeuner d’équipe', 'il y a 22 min', 'utensils'], [4, '<b>Elena Rossi</b> a choisi un parrain à <b>Lucas</b>', 'il y a 31 min', 'users']];\n"),
    (r"const RW = \[.*?\];\n", """const RW = [['SKU-10482', 'Flexible hydraulique 1/2" · 2 m', 'Pièces détachées', 0.18, '214', '420', ['Réassort', 'bad'], 'CMD-8812 · 6 nov.'], ['SKU-20915', 'Roulement 6205-2RS', 'Pièces détachées', 0.24, '388', '600', ['Réassort', 'bad'], '—'], ['SKU-33107', 'Gants de sécurité · T9', 'EPI', 0.31, '1 240', '1 500', ['Réassort', 'bad'], '—'],
      ['SKU-11873', 'Film étirable 23 µm', 'Emballage', 0.56, '86 rouleaux', '60', ['OK', 'ok'], '—'], ['SKU-40221', 'Huile moteur 5W-30 · 20 L', 'Consommables', 0.72, '142', '80', ['OK', 'ok'], 'CMD-8790 · 12 nov.'], ['SKU-28644', 'Bande de convoyeur 800 mm', 'Pièces détachées', 0.44, '12', '10', ['Bas', 'warn'], 'CMD-8805 · 4 nov.'], ['SKU-50317', 'Étiquettes 100 × 150', 'Emballage', 0.88, '3 600', '1 200', ['OK', 'ok'], '—'], ['SKU-36118', 'Filtre à air F7', 'Pièces détachées', 0.62, '96', '50', ['OK', 'ok'], '—'], ['SKU-19904', 'Batterie de chariot 48 V', 'Équipement', 0.4, '6', '4', ['Bas', 'warn'], '—'], ['SKU-27460', 'Colliers de serrage 300 mm', 'Consommables', 0.93, '9 800', '2 000', ['OK', 'ok'], '—'], ['SKU-44012', 'Gilet haute visibilité · L', 'EPI', 0.52, '310', '200', ['OK', 'ok'], '—'], ['SKU-31775', 'Cartouche de graisse 400 g', 'Consommables', 0.67, '520', '300', ['OK', 'ok'], '—']];
"""),
    (r"'€(\d+)k'", r"'\1 k€'"),
], regex=True)
MOIS = {'Oct': 'oct.', 'Nov': 'nov.', 'Dec': 'déc.', 'Jan': 'janv.'}
p = os.path.join(dst, 'app_yc.js')
s = open(p, encoding='utf-8').read()
s, n = re.subn(r"'(Oct|Nov|Dec|Jan) (\d+)'", lambda m: f"'{m.group(2)} {MOIS[m.group(1)]}'", s)
assert n > 10, n
open(p, 'w', encoding='utf-8').write(s)

# Mini dashboards (ouverture, galaxie).
edit('dash_yc.js', [
    ("'People · new joiners'", "'RH · arrivées'"), ("'Fleet · live'", "'Flotte · en direct'"), ("'Warehouse stock'", "'Stock entrepôt'"), ("'Line health'", "'Santé des lignes'"), ("'Sales pipeline'", "'Pipeline commercial'"),
    ("'Shift planning'", "'Planning équipes'"), ("'On-time deliveries'", "'Livraisons à l’heure'"), ("'Recruitment'", "'Recrutement'"), ("'CO₂ by site'", "'CO₂ par site'"), ("'IT tickets'", "'Tickets IT'"),
    ("['Expenses', 'receipt'", "['Notes de frais', 'receipt'"), ("['Site audits', 'clipboard-check'", "['Audits de site', 'clipboard-check'"),
    ("'new joiners this month'", "'arrivées ce mois-ci'"), ("<span>Ready on day one</span>", "<span>Prêts dès le jour J</span>"), ("'38 trucks'", "'38 camions'"), ("'SKUs below reorder point'", "'réf. sous le seuil'"),
    ("'reorder'", "'seuil'"), ("'OEE · all lines'", "'TRS · toutes lignes'"), ("['Press', 'Weld', 'Paint'][row]", "['Presse', 'Soudure', 'Peinture'][row]"),
    ("[['Lead', 1, 8], ['Qualified', 0.78, 0], ['Proposal', 0.56, 5], ['Won', 0.34, 11]]", "[['Prospect', 1, 8], ['Qualifié', 0.78, 0], ['Proposition', 0.56, 5], ['Gagné', 0.34, 11]]"),
    ("'MTWTFSS'", "'LMMJVSD'"), ("'+1.8 pts'", "'+1,8 pt'"), ("'96.4%'", "'96,4 %'"), ("'87.4%'", "'87,4 %'"), ("'€12.6M'", "'12,6 M€'"), ("'7 open roles'", "'7 postes ouverts'"),
    ("[[10, 'Priya Nair', 'Offer', OK], [5, 'Lucas Moreau', 'Interview', INFO], [14, 'Sofia Alves', 'Screening', NEU]]", "[[10, 'Priya Nair', 'Offre', OK], [5, 'Lucas Moreau', 'Entretien', INFO], [14, 'Sofia Alves', 'Présélection', NEU]]"),
    ("['Data analyst', 'Engineer', 'Buyer'][j]", "['Data analyst', 'Ingénieur', 'Acheteuse'][j]"), ("'CO₂ vs last year'", "'CO₂ vs l’an dernier'"), ("'−12% this week'", "'−12 % cette semaine'"),
    ("'12 to approve'", "'12 à valider'"), ("[[0, 'Client dinner', '€420'], [7, 'Train Lyon–Turin', '€186'], [9, 'Software licence', '€1,240']]", "[[0, 'Dîner client', '420 €'], [7, 'Train Lyon–Turin', '186 €'], [9, 'Licence logiciel', '1 240 €']]"),
    ("j ? 'Review' : 'Approve'", "j ? 'Revoir' : 'Valider'"), ("['Safety gear checked', 'Fire exits clear', 'Training up to date', 'Waste sorted']", "['EPI vérifiés', 'Issues de secours dégagées', 'Formations à jour', 'Tri des déchets']"),
])
# Pas d'« app » à l'écran : adresses web en .fr et chemin « applications ».
edit('s2_build.js', [("youcraft.leyton-cognitx.com/apps/client-portal", "youcraft.leyton-cognitx.com/applications/portail-client"), ("youcraft.app", "youcraft.fr")])
edit('s9_app.js', [("youcraft.app", "youcraft.fr")])

# Ni acronymes ni anglicismes à l'écran : mots complets.
edit('s6_v4.js', [("'Sans ticket IT.'", "'Sans ticket informatique.'")])
edit('s2_build.js', [("portail d’onboarding client", "portail d’accueil client"), ("'Onboarding RH'", "'Accueil des recrues'")])
edit('s3_scale.js', [("'Reporting ESG pour tout le groupe'", "'Bilan carbone de tout le groupe'")])
edit('s8_yc.js', [("'Onboarding RH'", "'Accueil des recrues'"), ("'Reporting ESG'", "'Bilan carbone'"), ("'Support IT'", "'Support informatique'")])
edit('s9_app.js', [("'Onboarding RH', 'RH'", "'Accueil des recrues', 'Ressources humaines'"), ("'Tour de contrôle', 'Supply chain'", "'Tour de contrôle', 'Logistique'")])
edit('app_yc.js', [
    ("crumbs: ['RH', 'Intégration', 'T4 2026']", "crumbs: ['Ressources humaines', 'Intégration', '4e trimestre 2026']"),
    ("'Data & IA'", "'Données'"), ("'Data analyst'", "'Analyste de données'"), ("'HR partner', 'RH'", "'Chargée de recrutement', 'Ressources humaines'"), ("'Product designer'", "'Designer produit'"),
    ("'PC en livraison'", "'Ordinateur en livraison'"), ("'<b>IT</b> a expédié un PC à <b>Tom Becker</b>'", "'<b>Ordinateur</b> expédié à <b>Tom Becker</b>'"),
    ("['Cons.', 'Data', 'Ops', 'Ing.', 'Fin.', 'Ventes', 'RH']", "['Conseil', 'Données', 'Opér.', 'Ingén.', 'Finance', 'Ventes', 'Pers.']"),
    ("'TRS usine aujourd’hui'", "'Rendement de l’usine'"), ("Synchronisé avec l’ERP il y a 1 min", "Synchronisé avec la gestion il y a 1 min"), ("'EPI'", "'Protection'"),
    ("'Resp. opérations'", "'Responsable opérations'"), ("'Resp. commercial'", "'Commercial senior'"), ("objectif T4", "objectif du trimestre"), ("btn('Clôture : T4'", "btn('Clôture : ce trimestre'"),
])
edit('dash_yc.js', [("'EPI vérifiés'", "'Protections vérifiées'"), ("'RH · arrivées'", "'Nouvelles recrues'"), ("'Tickets IT'", "'Tickets informatiques'"), ("'TRS · toutes lignes'", "'rendement global'")])
# Sans « par Leyton CognitX » sous le logo ni sur la signature finale.
edit('s8_yc.js', [("        h('span', '', a.by, 'par');\n        C.leytonLogo(a.by, 92);\n", ''), ("        h('span', '', a.by, 'par');\n        C.leytonLogo(a.by, 80, true);\n", '')])

# Modification en ligne en quelques minutes : la vignette reprend la phrase dite.
edit('s6_v4.js', [("[26.45, 27.2, 'Demandez.', 1180, 860, 70]", "[26.45, 27.6, 'En ligne en quelques minutes.', 640, 860, 66]")])
# Rafale de demandes : trois exemples affichés plus longtemps (2,15 s chacun) au lieu de cinq.
edit('s3_scale.js', [
    ("      ['Audits fournisseurs de nos 40 usines', ['#16161C', '#1E3BFF', '#DFE8F0']],\n      ['Bilan carbone de tout le groupe', ['#0B0B0F', '#4F6BFF', '#7F93FF']],\n", ""),
    ("const T0 = 30.0, D = 1.43;", "const T0 = 30.7, D = 2.15;"),
    ("['b49/blur_citynight', 'b49/blur_violet_flow', 'b50/blur_skyline', 'b49/blur_street', 'b49/blur_boardroom'][k]", "['b49/blur_citynight', 'b49/blur_violet_flow', 'b49/blur_boardroom'][k]"),
])
# Plan « portable » refait en plan héros premium (bbfr/s10_laptop.js).
edit('index.html', [('<script src="s9_app.js"></script>', '<script src="s9_app.js"></script>\n<script src="s10_laptop.js"></script>')])
shutil.copy(os.path.join(HERE, 'bbfr', 's10_laptop.js'), os.path.join(dst, 's10_laptop.js'))
print('version française :', dst)
