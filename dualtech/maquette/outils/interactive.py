"""Assemble la maquette en un seul fichier HTML autonome et navigable : python3 outils/interactive.py

Les dix pages deviennent des vues d'une même page (adresses du type #/programme-2026), les images sont
embarquées une seule fois, et les formulaires sont désactivés (aucun envoi réel depuis la maquette).
"""
import base64, json, re
from html import unescape
from collections import Counter
from pathlib import Path

RACINE = Path(__file__).resolve().parent.parent
SORTIE = RACINE.parent / "maquette-interactive.html"
from donnees import PAGES

fichiers = {cle: f for cle, (f, _) in PAGES.items()}
noms = {f: f.removesuffix(".html") for f in fichiers.values()}
noms["index.html"] = "accueil"
html = {f: (RACINE / f).read_text() for f in fichiers.values()}

def bloc(s, debut, fin):
    i = s.index(debut); return s[i:s.index(fin, i) + len(fin)]

idx = html["index.html"]
tete = idx[:idx.index("</head>")]
tete = re.sub(r'<script type="application/ld\+json">.*?</script>', "", tete, flags=re.S)
tete = re.sub(r'<link rel="(canonical|preload|icon|apple-touch-icon)"[^>]*>\n?', "", tete)
entete = bloc(idx, '<a class="skip"', '</div>\n<main id="contenu">').removesuffix('<main id="contenu">')
pied = bloc(idx, '<footer class="pied">', "</footer>")

# Identifiants présents sur plusieurs pages : préfixés par la page pour rester uniques
mains = {f: bloc(s, '<main id="contenu">', "</main>")[len('<main id="contenu">'):-len("</main>")] for f, s in html.items()}
compte = Counter(i for m in mains.values() for i in set(re.findall(r'\bid="([^"]+)"', m)))
doublons = {i for i, n in compte.items() if n > 1}

def prefixer(m, p):
    for i in doublons:
        m = re.sub(rf'\b(id|for|aria-labelledby|aria-controls|aria-describedby)="{re.escape(i)}"', rf'\1="{p}-{i}"', m)
        m = m.replace(f'href="#{i}"', f'href="#{p}-{i}"')
    return m

def liens(s):
    # page.html#ancre → #/page/ancre ; page.html → #/page
    def r(mo):
        f, a = mo.group(1), mo.group(2)
        n = noms[f]
        if a:
            a = a[1:]
            a = f"{n}-{a}" if a in doublons else a
            return f'href="#/{n}/{a}"'
        return f'href="#/{n}"'
    return re.sub(r'href="(' + "|".join(map(re.escape, noms)) + r')(#[^"]*)?"', r, s)

vues, titres = [], {}
for f, m in mains.items():
    n = noms[f]
    titres[n] = unescape(re.search(r"<title>(.*?)</title>", html[f]).group(1))
    vues.append(f'<div class="vue" data-vue="{n}"{"" if n == "accueil" else " hidden"}>{liens(prefixer(m, n))}</div>')
corps = liens(entete) + '<main id="contenu">' + "".join(vues) + "</main>" + liens(pied)

# Images : chaque fichier est embarqué une seule fois, puis attribué au chargement
assets = sorted(set(re.findall(r'assets/[\w/.-]+\.(?:webp|png|jpg)', corps)))
carte = {a: f"data:image/{'jpeg' if a.endswith('jpg') else a.rsplit('.', 1)[1]};base64," + base64.b64encode((RACINE / a).read_bytes()).decode() for a in assets}
corps = re.sub(r'<img([^>]*?) src="(assets/[^"]+)"', r'<img\1 data-a="\2"', corps)
corps = re.sub(r'url\((assets/[^)]+)\)', lambda mo: f'url({carte[mo.group(1)]})', corps)

css = (RACINE / "assets/dualtech.css").read_text()
js = (RACINE / "assets/dualtech.js").read_text()

routeur = """
(() => {
  const A = JSON.parse(document.getElementById('images').textContent);
  document.querySelectorAll('img[data-a]').forEach((i) => { i.src = A[i.dataset.a]; });
  const T = %s;
  const vues = [...document.querySelectorAll('.vue')];
  const aller = () => {
    const [, page = 'accueil', ancre] = (location.hash.match(/^#\\/([^/]*)\\/?(.*)$/) || []);
    const v = vues.find((x) => x.dataset.vue === page) ? page : 'accueil';
    vues.forEach((x) => { x.hidden = x.dataset.vue !== v; });
    document.title = T[v] || T.accueil;
    document.querySelectorAll('.nav__menu a, .tiroir a').forEach((a) => {
      const h = a.getAttribute('href');
      if (h === '#/' + v) a.setAttribute('aria-current', 'page'); else a.removeAttribute('aria-current');
    });
    const cible = ancre && document.getElementById(ancre);
    if (cible) requestAnimationFrame(() => cible.scrollIntoView({ behavior: 'instant' })); else scrollTo({ top: 0, behavior: 'instant' });
    dispatchEvent(new Event('scroll'));
  };
  addEventListener('hashchange', aller);
  /* ancres internes à une page (#formulaire…) : défilement sans changer de vue */
  document.addEventListener('click', (e) => {
    const a = e.target.closest('a[href^="#"]');
    if (!a || a.getAttribute('href').startsWith('#/')) return;
    const el = document.getElementById(a.getAttribute('href').slice(1));
    if (el) { e.preventDefault(); el.scrollIntoView({ behavior: 'smooth' }); }
  });
  /* maquette : aucun envoi réel */
  document.querySelectorAll('form').forEach((f) => f.addEventListener('submit', (e) => {
    e.preventDefault();
    let m = f.querySelector('.envoi-maquette');
    if (!m) { m = document.createElement('p'); m.className = 'envoi-maquette plein'; m.setAttribute('role', 'status'); m.style.cssText = 'padding:14px 16px;border-left:3px solid var(--tech);background:rgba(73,167,255,.1)'; f.appendChild(m); }
    m.textContent = 'Maquette : formulaire valide. Sur le site, la demande sera transmise à l\\u2019équipe DualTech.';
  }));
  aller();
})();
""" % json.dumps(titres, ensure_ascii=False)

page = f"""{tete}
<style>{css}</style>
</head>
<body>
{corps}
<script type="application/json" id="images">{json.dumps(carte)}</script>
<script>{routeur}</script>
<script>{js}</script>
</body>
</html>
"""
# le fichier principal est déjà dans la page : on retire l'appel externe
page = page.replace('<link rel="stylesheet" href="assets/dualtech.css">', "")
SORTIE.write_text(page)
print(SORTIE, f"{SORTIE.stat().st_size / 1e6:.1f} Mo", f"{len(assets)} images", f"identifiants préfixés : {sorted(doublons)}")
