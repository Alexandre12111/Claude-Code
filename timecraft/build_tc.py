"""TimeCraft · présentation client FR, v1.2 : finition graphique premium.
Part de la v1.1.3 d'origine (contenu, captures, simulateur conservés à l'identique) et ajoute :
une couche de styles (tc_premium.css), les graisses 500 et 600 de Plus Jakarta Sans,
des icônes Lucide sur les cartes et un habillage « fenêtre d'application » sur les captures.
Usage : python3 build_tc.py  ->  TimeCraft_Presentation_Client_FR_1_2.html"""
import base64, json, os, re

HERE = os.path.dirname(os.path.abspath(__file__))
ROOT = os.path.abspath(os.path.join(HERE, '..'))
src = open(os.path.join(HERE, 'TimeCraft_Presentation_Client_FR_1_1_3_original.html'), encoding='utf-8').read()

ref = open(os.path.join(ROOT, 'presentation', 'youcraft-presentation_1_9.html'), encoding='utf-8').read()
I = json.loads(re.search(r'window\.YA_ICONS=(\{.*?\});\n', ref, re.S).group(1))
I.update({
    'database': '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5V19A9 3 0 0 0 21 19V5"/><path d="M3 12A9 3 0 0 0 21 12"/>',
    'fingerprint': '<path d="M12 10a2 2 0 0 0-2 2c0 1.02-.1 2.51-.26 4"/><path d="M14 13.12c0 2.38 0 6.38-1 8.88"/><path d="M17.29 21.02c.12-.6.43-2.3.5-3.02"/><path d="M2 12a10 10 0 0 1 18-6"/><path d="M2 16h.01"/><path d="M21.8 16c.2-2 .131-5.354 0-6"/><path d="M5 19.5C5.5 18 6 15 6 12a6 6 0 0 1 .34-2"/><path d="M8.65 22c.21-.66.45-1.32.57-2"/><path d="M9 6.8a6 6 0 0 1 9 5.2v2"/>',
    'ban': '<circle cx="12" cy="12" r="10"/><path d="m4.9 4.9 14.2 14.2"/>',
    'gavel': '<path d="m14.5 12.5-8 8a2.119 2.119 0 1 1-3-3l8-8"/><path d="m16 16 6-6"/><path d="m8 8 6-6"/><path d="m9 7 8 8"/><path d="m21 11-8-8"/>',
})
MAP = {  # titre ou libellé de carte -> icône
    'Affaire ADELIOM': 'gavel', 'Affaire SAS Quantum Surgical': 'gavel', 'Ce que l’administration attend': 'landmark',
    'Un CIR qui n’est pas piloté': 'chart-column', 'Trop peu détaillés': 'file-text', 'Fragiles en contrôle': 'circle-alert',
    'Journal d’audit': 'history', 'Visibilité': 'eye', 'Red flags': 'bell', 'Éligibilité et pilotage du CIR': 'badge-check',
    'Une application prête': 'rocket', 'Modifiable par vos équipes': 'wand-sparkles', 'Un socle pour d’autres usages': 'layers', 'Un code qui vous appartient': 'code-xml',
    'Cloud en Union européenne': 'cloud', 'Données isolées par application': 'database', 'Aucun entraînement sur vos données': 'sparkles',
    'Accès par rôles': 'fingerprint', 'Aucun salaire dans l’outil': 'ban', 'Votre simulation': 'coins'}
ICO = {k: f'<svg viewBox="0 0 24 24">{I[v]}</svg>' for k, v in MAP.items()}

JS = '''<script>
/* v1.2 : icônes des cartes et habillage des captures (contenu d'origine inchangé) */
(function () {
  var ICO = ''' + json.dumps(ICO, ensure_ascii=False) + ''';
  var URL = ['timecraft.leyton.youcraft.tech/agenda', 'timecraft.leyton.youcraft.tech/agenda', 'timecraft.leyton.youcraft.tech/agenda', 'timecraft.leyton.youcraft.tech/absences', 'timecraft.leyton.youcraft.tech/validation', 'timecraft.leyton.youcraft.tech/admin', 'timecraft.leyton.youcraft.tech/cabinet'];
  document.querySelectorAll('.card').forEach(function (c) {
    var h = c.querySelector('h3'), k = c.querySelector(':scope > .k');
    var key = h && ICO[h.textContent.trim()] ? h.textContent.trim() : (k && ICO[k.textContent.trim()] ? k.textContent.trim() : null);
    if (!key) return;
    var t = document.createElement('span'); t.className = 'tci' + (c.style.background && c.style.background.indexOf('11, 11, 15') >= 0 ? ' ink' : ''); t.innerHTML = ICO[key];
    var hd = document.createElement('div'); hd.className = 'chd'; hd.appendChild(t);
    if (k && /^0\\d$/.test(k.textContent.trim())) { hd.appendChild(k); c.insertBefore(hd, c.firstChild); }
    else if (k && k.textContent.trim() === key) { hd.appendChild(k); c.insertBefore(hd, c.firstChild); }
    else if (h) { c.insertBefore(hd, c.firstChild); if (k) hd.appendChild(k); else { hd.appendChild(h); h.style.margin = '0'; } }
  });
  document.querySelectorAll('.shot').forEach(function (s, i) {
    var b = document.createElement('div'); b.className = 'bbar'; b.innerHTML = '<i></i><i></i><i></i><u>' + (URL[i] || URL[0]) + '</u>';
    s.insertBefore(b, s.firstChild);
  });
})();
</script>'''

HERE_FONTS = os.path.join(ROOT, 'presentation', 'v2')
fonts = ''.join("@font-face{font-family:'Plus Jakarta Sans';font-style:normal;font-weight:%d;font-display:swap;src:url(data:font/woff2;base64,%s) format('woff2')}"
                % (w, base64.b64encode(open(os.path.join(HERE_FONTS, f'plus-jakarta-sans-latin-{w}-normal.woff2'), 'rb').read()).decode()) for w in (500, 600))
css = open(os.path.join(HERE, 'tc_premium.css'), encoding='utf-8').read()

assert src.count('</head>') == 1 and src.count('</body>') == 1
out = src.replace('</head>', '<style id="tc-v12">' + fonts + css + '</style>\n</head>')
out = out.replace('</body>', JS + '\n</body>')
dst = os.path.join(HERE, 'TimeCraft_Presentation_Client_FR_1_2.html')
open(dst, 'w', encoding='utf-8').write(out)
print(dst, round(len(out) / 1024), 'Ko')
