/* ---------------------------------------------------------------- BILAN SOCIAL (remplace « Contract Desk » sous la clé legal)
   Application de démonstration pour une direction financière : absentéisme, parité, catégories socio-professionnelles.
   Données fictives, comme si elles venaient du fichier Excel des salariés. */
(function () {
  var F_ = '#DB2777', H_ = '#4F46E5', AM = '#F59E0B', TE = '#14B8A6';
  var CSPC = { 'Cadres': F_, 'Agents de maîtrise': H_, 'Employés': AM, 'Ouvriers': TE };
  NAV.legal = { main: 0, name: 'Bilan social', icon: 'users', mark: INK, user: [5, 'David Cohen'], dom: 'Direction financière',
    nav: [['layout-dashboard', 'Synthèse'], ['calendar', 'Absentéisme'], ['users', 'Parité'], ['layers', 'Catégories (CSP)'], ['user', 'Salariés', '1 248'], ['file-text', 'Rapports']],
    saved: [[1, null], [2, null], [4, 'Cadres']],
    notif: [['circle-alert', 'Absentéisme Logistique au‑dessus du seuil de 6 %', 'il y a 12 min', 1], ['file-spreadsheet', 'Fichier salaries_2026.xlsx importé · 1 248 lignes', 'il y a 1 h', 4], ['file-text', 'Bilan social 2026 prêt à être relu', 'hier', 5]] };

  var CSP = [['Cadres', 412, 41, 41, 8.2, 58, 2.9], ['Agents de maîtrise', 286, 49, 43, 10.4, 39, 4.1], ['Employés', 398, 58, 37, 6.1, 31, 5.6], ['Ouvriers', 152, 18, 39, 9.3, 27, 7.4]];
  var DEP = [['Conseil', 2.8], ['Données', 3.1], ['Finance', 3.4], ['RH', 3.9], ['Commercial', 4.6], ['Opérations', 5.8], ['Logistique', 7.8]];
  var MOIS = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.'];
  var A26 = [5.4, 5.9, 5.1, 4.6, 4.2, 4.0, 3.6, 3.2, 4.1, 4.5, 4.8], A25 = [5.8, 6.2, 5.5, 5.0, 4.6, 4.3, 3.9, 3.5, 4.4, 4.9, 5.2];
  var SAL = [
    ['Léa Martin', 'F', 'Conseil', 'Cadres', 34, 6, 2, 'CDI'], ['Hugo Bernard', 'H', 'Logistique', 'Ouvriers', 41, 12, 19, 'CDI'],
    ['Chloé Petit', 'F', 'Finance', 'Agents de maîtrise', 38, 9, 4, 'CDI'], ['Nathan Robert', 'H', 'Données', 'Cadres', 29, 3, 1, 'CDI'],
    ['Manon Richard', 'F', 'Opérations', 'Employés', 27, 2, 11, 'CDD'], ['Lucas Durand', 'H', 'Commercial', 'Cadres', 45, 15, 3, 'CDI'],
    ['Inès Moreau', 'F', 'RH', 'Agents de maîtrise', 36, 8, 6, 'CDI'], ['Théo Laurent', 'H', 'Logistique', 'Employés', 24, 1, 14, 'Alternance'],
    ['Camille Simon', 'F', 'Conseil', 'Cadres', 42, 11, 0, 'CDI'], ['Louis Michel', 'H', 'Opérations', 'Ouvriers', 52, 22, 9, 'CDI'],
    ['Sarah Lefebvre', 'F', 'Commercial', 'Employés', 31, 4, 7, 'CDI'], ['Jules Leroy', 'H', 'Finance', 'Cadres', 39, 7, 2, 'CDI'],
    ['Emma Roux', 'F', 'Logistique', 'Employés', 33, 5, 21, 'CDI'], ['Adam David', 'H', 'Données', 'Agents de maîtrise', 30, 4, 3, 'CDI'],
    ['Zoé Bertrand', 'F', 'Opérations', 'Ouvriers', 46, 18, 8, 'CDI'], ['Raphaël Morel', 'H', 'RH', 'Employés', 28, 2, 5, 'CDD']];
  function cspB(c) { return '<span class="ya-badge" style="background:' + CSPC[c] + '1A;color:' + CSPC[c] + '"><i></i>' + c + '</span>'; }
  function absB(j) { return j >= 15 ? badge('À suivre', 'bad') : j >= 8 ? badge('Élevé', 'warn') : badge('Normal', 'ok'); }
  function sexe(s) { return '<span style="display:inline-flex;align-items:center;gap:6px">' + dot(s === 'F' ? F_ : H_) + (s === 'F' ? 'Femme' : 'Homme') + '</span>'; }
  function salSheet(x) {
    return { lead: iniB(x[0], 52), t: x[0], s: x[3] + ' · ' + x[2], f: [['Sexe', x[1] === 'F' ? 'Femme' : 'Homme'], ['Âge', x[4] + ' ans'], ['Ancienneté', x[5] + ' an' + (x[5] > 1 ? 's' : '')], ['Contrat', x[7]], ['Absences 2026', x[6] + ' j'], ['Suivi', absB(x[6])]],
      html: '<div class="yx-ai' + (x[6] >= 15 ? '' : ' ok') + '"><b>' + ic('sparkles', 14, x[6] >= 15 ? BADC : OKC) + 'Analyse</b><p>' + (x[6] >= 15 ? 'Absences au‑dessus de la moyenne du service (' + x[6] + ' j contre 9 j). Un entretien de retour est conseillé.' : 'Absences dans la moyenne du service. Aucune action requise.') + '</p></div>',
      a: [['Voir le dossier', 'file-text', 0, 'Dossier salarié ouvert'], ['Planifier un entretien', 'calendar', 1, 'Entretien planifié']] };
  }
  function cspSheet(c) {
    return { lead: tile('layers', CSPC[c[0]], 52), t: c[0], s: nf(c[1]) + ' salariés · ' + pct(c[1] / 12.48, 0) + ' de l’effectif',
      f: [['Part de femmes', pct(c[2])], ['Âge moyen', c[3] + ' ans'], ['Ancienneté moyenne', nf(c[4], 1) + ' ans'], ['Salaire médian', nf(c[5]) + ' k€'], ['Absentéisme', pct(c[6], 1)], ['Écart F/H', pct(c[0] === 'Ouvriers' ? 6 : c[0] === 'Cadres' ? 4.8 : c[0] === 'Employés' ? 1.2 : 2.1, 1)]],
      a: [['Voir les salariés', 'users', 1, function () { var app = curApp; if (app) { app.vs = app.vs || {}; app.vs[4] = { tab: c[0] }; YAX.nav(app, 4); } }]] };
  }
  var curApp = null;
  VIEWS.legal = {};
  VIEWS.legal[0] = function (app, V) {
    curApp = app;
    return { title: 'Bilan social 2026', sub: 'Données importées depuis salaries_2026.xlsx · 1 248 salariés', actions: [['Importer un fichier Excel', 'upload', 0, 'Fichier importé · 1 248 salariés mis à jour'], ['Exporter le bilan social', 'download', 1, 'Bilan social 2026 exporté (PDF)']],
      kpis: [['Effectif total', 'users', nf(1248), '+3,2 %', 1], ['Taux d’absentéisme', 'calendar', pct(4.6, 1), '−0,4 pt', 1, SPD], ['Part de femmes', 'user', pct(45), '+2 pts', 1], ['Index égalité F/H', 'badge-check', '89 / 100', '+3', 1]],
      lay: 'grid4', cards: [
        { t: 'line', title: 'Absentéisme par mois (%)', labels: MOIS, series: [['2026', A26, F_], ['2025', A25, '#B5B0A5']], thr: 6, fmt: function (x) { return pct(x, 1); } },
        { t: 'donut', title: 'Catégories socio‑professionnelles', items: CSP.map(function (c) { return [c[0], c[1], CSPC[c[0]]]; }), fmt: function (x) { return nf(x); }, cl: 'salariés', center: '1 248', click: function () { YAX.nav(app, 3); } },
        { t: 'hb', title: 'Part de femmes par catégorie', lw: 150, items: CSP.map(function (c) { return [c[0], c[2] / 100, pct(c[2]), CSPC[c[0]]]; }), click: function () { YAX.nav(app, 2); } },
        { t: 'list', title: 'Points d’attention', items: [
          ['circle-alert', BADC, 'Absentéisme Logistique : 7,8 %', 'Au‑dessus du seuil de 6 % depuis 3 mois', badge('À traiter', 'bad'), 1],
          ['users', AM, 'Écart de rémunération Ouvriers : 6 %', 'Index égalité : à corriger avant mars', badge('À suivre', 'warn'), 2],
          ['layers', H_, 'Ouvriers : 18 % de femmes', 'Catégorie la moins mixte', badge('Info', 'info'), 3],
          ['user', TE, '41 alternants et CDD', 'Fin de contrat sous 90 jours pour 12 d’entre eux', badge('Info', 'info'), 4]].map(function (x) { return { lead: tile(x[0], x[1]), t: x[2], s: x[3], r: x[4], click: function () { YAX.nav(app, x[5]); } }; }) }
      ] };
  };
  VIEWS.legal[1] = function (app, V) {
    curApp = app;
    var dsel = V.dep;
    return { sub: 'Taux d’absentéisme sur 12 mois glissants · seuil d’alerte à 6 %', actions: [['Exporter', 'download', 0, 'Absentéisme exporté (Excel)']],
      kpis: [['Taux d’absentéisme', 'calendar', pct(4.6, 1), '−0,4 pt', 1, SPD], ['Jours d’absence', 'clock', nf(13920), '−6 %', 1, SPD], ['Absences courtes (< 3 j)', 'timer', pct(38), '+2 pts', 0], ['Coût estimé', 'wallet', eur(2.1, 1, 'M'), '−0,2 M€', 1, SPD]],
      lay: 'L2R1', cards: [
        { t: 'line', title: 'Évolution mensuelle (%)', labels: MOIS, series: [['2026', A26, F_], ['2025', A25, '#B5B0A5']], thr: 6, fmt: function (x) { return pct(x, 1); } },
        { t: 'bars', title: 'Absentéisme par service (%)' + (dsel != null ? ' · ' + DEP[dsel][0] : ''), labels: DEP.map(function (d) { return d[0]; }), vals: DEP.map(function (d) { return d[1]; }), colors: DEP.map(function (d) { return d[1] > 6 ? BADC : d[1] > 5 ? AM : F_; }), fmt: function (x) { return pct(x, 1); } },
        { t: 'hb', title: 'Motifs d’absence', lw: 150, items: [['Maladie', .58, '58 %', F_], ['Absences familiales', .14, '14 %', H_], ['Accident du travail', .09, '9 %', AM], ['Absences injustifiées', .04, '4 %', BADC], ['Autres motifs', .15, '15 %', '#B5B0A5']] }
      ] };
  };
  VIEWS.legal[2] = function (app, V) {
    curApp = app;
    var IDX = [['Écart de rémunération', 37, 40, 'Écart global de 3,1 % en faveur des hommes'], ['Écart d’augmentations', 20, 20, 'Aucun écart significatif'], ['Écart de promotions', 15, 15, 'Aucun écart significatif'], ['Retour de congé maternité', 15, 15, '100 % des salariées augmentées au retour'], ['10 plus hautes rémunérations', 2, 10, '2 femmes sur 10']];
    return { sub: 'Index de l’égalité professionnelle 2026 · publication avant le 1er mars', actions: [['Publier l’index', 'badge-check', 1, 'Index 2026 prêt à être déclaré']],
      kpis: [['Femmes', 'user', nf(567), pct(45) + ' de l’effectif', 1, SP, ' '], ['Hommes', 'user', nf(681), pct(55) + ' de l’effectif', 1, SP, ' '], ['Index égalité F/H', 'badge-check', '89 / 100', '+3', 1], ['Femmes parmi les dirigeants', 'briefcase', pct(38), '+5 pts', 1]],
      lay: 'grid4', cards: [
        { t: 'hb', title: 'Part de femmes par catégorie', lw: 150, items: CSP.map(function (c) { return [c[0], c[2] / 100, pct(c[2]), F_]; }) },
        { t: 'bars', title: 'Écart de rémunération F/H par catégorie (%)', labels: ['Cadres', 'Maîtrise', 'Employés', 'Ouvriers'], vals: [4.8, 2.1, 1.2, 6.0], colors: [F_, H_, AM, BADC], fmt: function (x) { return pct(x, 1); } },
        { t: 'donut', title: 'Répartition femmes et hommes', items: [['Femmes', 567, F_], ['Hommes', 681, H_]], fmt: function (x) { return nf(x); }, cl: 'salariés', center: '1 248' },
        { t: 'list', title: 'Indicateurs de l’index', items: IDX.map(function (x, i) { return { lead: '<span class="ya-ini" style="width:42px;height:30px;font-size:12px;background:' + (x[1] === x[2] ? 'rgba(28,140,78,.12);color:' + OKC : 'rgba(217,137,15,.14);color:#A8670A') + '">' + x[1] + '/' + x[2] + '</span>', t: x[0], s: x[3], r: x[1] === x[2] ? badge('Atteint', 'ok') : badge('À améliorer', 'warn'),
          d: function () { return { lead: tile('badge-check', x[1] === x[2] ? OKC : WARNC, 52), t: x[0], s: 'Index de l’égalité professionnelle 2026', big: x[1] + ' / ' + x[2] + ' points', f: [['Résultat', x[3]], ['Statut', x[1] === x[2] ? badge('Atteint', 'ok') : badge('À améliorer', 'warn')]],
            html: '<div class="yx-ai"><b>' + ic('sparkles', 14, F_) + 'Recommandation</b><p>' + (x[1] === x[2] ? 'Indicateur au maximum : à maintenir lors de la prochaine campagne.' : 'Prévoir une enveloppe de rattrapage ciblée pour gagner jusqu’à ' + (x[2] - x[1]) + ' points.') + '</p></div>',
            a: [['Voir le détail', 'chart-column', 1, 'Détail de l’indicateur ouvert']] }; } }; }) }
      ] };
  };
  VIEWS.legal[3] = function (app, V) {
    curApp = app;
    var rows = CSP.map(function (c, i) { return { id: 'p' + i, c: [tile('layers', CSPC[c[0]]) + '<b style="font-weight:600">' + c[0] + '</b>', nf(c[1]), '<span style="display:flex;align-items:center;gap:10px">' + bar(c[2] / 100, F_, 70) + pct(c[2]) + '</span>', c[3] + ' ans', nf(c[4], 1) + ' ans', nf(c[5]) + ' k€', c[6] > 6 ? badge(pct(c[6], 1), 'bad') : c[6] > 5 ? badge(pct(c[6], 1), 'warn') : badge(pct(c[6], 1), 'ok')],
      m: [tile('layers', CSPC[c[0]], 34), c[0], nf(c[1]) + ' salariés', badge(pct(c[6], 1), 'neu')], q: c[0], d: function () { return cspSheet(c); } }; });
    return { sub: '4 catégories · répartition, âge, ancienneté et rémunération', actions: [['Exporter', 'download', 0, 'Répartition par CSP exportée (Excel)']],
      kpis: CSP.map(function (c) { return [c[0], 'layers', nf(c[1]), pct(c[1] / 12.48, 0) + ' de l’effectif', 1, SP, ' ']; }),
      lay: 'splitR2', cards: [
        { t: 'table', title: 'Indicateurs par catégorie', search: false, cols: [['CATÉGORIE', 230], ['EFFECTIF', 90], ['FEMMES', 150], ['ÂGE MOYEN', 100], ['ANCIENNETÉ', 100], ['SALAIRE MÉDIAN', 120], ['ABSENTÉISME', 92]], rows: rows },
        { t: 'donut', title: 'Répartition des effectifs', items: CSP.map(function (c) { return [c[0], c[1], CSPC[c[0]]]; }), fmt: function (x) { return nf(x); }, cl: 'salariés', center: '1 248' },
        { t: 'bars', title: 'Pyramide des âges', labels: ['< 25', '25‑34', '35‑44', '45‑54', '55 +'], vals: [96, 382, 401, 268, 101], colors: [TE, F_, H_, AM, '#B5B0A5'], fmt: function (x) { return nf(x) + ' salariés'; } }
      ] };
  };
  VIEWS.legal[4] = function (app, V) {
    curApp = app;
    var rows = SAL.map(function (x, i) { return { id: 's' + i, c: [iniB(x[0]) + '<span class="ya-who"><span><b>' + x[0] + '</b><span>' + x[7] + '</span></span></span>', sexe(x[1]), x[2], cspB(x[3]), x[4] + ' ans', x[5] + ' an' + (x[5] > 1 ? 's' : ''), x[6] + ' j', absB(x[6])],
      m: [iniB(x[0]), x[0], x[3] + ' · ' + x[2], absB(x[6])], tab: [x[3]], q: x[0] + ' ' + x[2] + ' ' + x[3], d: function () { return salSheet(x); } }; });
    return { sub: 'Source : salaries_2026.xlsx · 1 248 salariés, 16 affichés', actions: [['Importer un fichier Excel', 'upload', 0, 'Fichier importé · 1 248 salariés mis à jour'], ['Ajouter un salarié', 'user-plus', 1, 'Fiche salarié créée']],
      kpis: [['Salariés', 'users', nf(1248), '+39', 1], ['Âge moyen', 'user', '39 ans', '+0,4', 1, SP, ' '], ['Ancienneté moyenne', 'clock', '8,1 ans', '+0,3', 1, SP, ' '], ['CDI', 'badge-check', pct(91), '+1 pt', 1]],
      cards: [{ t: 'table', title: 'Salariés', tabs: [['all', 'Tous'], ['Cadres', 'Cadres'], ['Agents de maîtrise', 'Maîtrise'], ['Employés', 'Employés'], ['Ouvriers', 'Ouvriers']], cols: [['SALARIÉ', 270], ['SEXE', 120], ['SERVICE', 140], ['CATÉGORIE', 190], ['ÂGE', 90], ['ANCIENNETÉ', 110], ['ABSENCES', 100], ['SUIVI', 282]], rows: rows }] };
  };
  VIEWS.legal[5] = function (app, V) {
    curApp = app;
    return { sub: 'Rapports réglementaires et tableaux de bord partagés', actions: [['Nouveau rapport', 'plus', 1, 'Rapport créé']],
      kpis: [['Rapports générés', 'file-text', nf(14), '+3', 1], ['Dernier import', 'file-spreadsheet', 'Aujourd’hui', '1 248 lignes', 1, SP, ' '], ['Destinataires', 'users', nf(9), 'CODIR et CSE', 1, SP, ' '], ['Prochaine échéance', 'calendar', '1er mars', 'Index égalité', 1, SP, ' ']],
      lay: 'split', cards: [
        { t: 'list', title: 'Rapports', items: [['Bilan social 2026', 'PDF · 42 pages · prêt à relire', 'file-text', F_], ['Index de l’égalité professionnelle', 'Déclaration avant le 1er mars', 'badge-check', H_], ['Absentéisme mensuel', 'Envoyé au CODIR chaque 1er du mois', 'calendar', AM], ['Base de données économiques et sociales', 'Partagée avec le CSE', 'layers', TE]].map(function (x) { return { lead: tile(x[2], x[3]), t: x[0], s: x[1], r: '<button class="yx-mini p" data-act="g">Générer</button>', act: function () { say(app, x[0] + ' · rapport généré'); } }; }) },
        { t: 'tg', title: 'Sources de données', items: [['Fichier Excel des salariés', 'Importé aujourd’hui', true], ['Déclaration sociale nominative', 'Synchronisation mensuelle', true], ['SIRH', 'Connexion à la demande', false], ['Anonymiser les exports', 'Pour le CSE', true, 1]] }
      ] };
  };
  Y.DEF.legal = [genericApp('legal'), 'Bilan social', 'users', INK, 'bilan-social.northwind.youcraft.tech/synthese'];
})();
