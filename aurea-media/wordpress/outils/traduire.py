"""Version anglaise du site : traduit le contenu exporté par exporter_theme.py.

    python3 wordpress/outils/traduire.py           (lancé aussi à la fin de exporter_theme.py)

Le français reste la référence : chaque texte du contenu français (nœuds de texte, attributs alt,
aria-label, title, placeholder) est cherché dans la table wordpress/traduction/en.json
(texte français normalisé → texte anglais). Les textes absents sont listés dans
wordpress/traduction/en-manquants.json (à traduire, puis relancer).

Sorties dans le thème :
- contenu/en/<slug anglais>.html : pages anglaises (les repères {{url:…}} gardent la clé française ;
  le thème les résout vers la page anglaise correspondante) ;
- parts/en/entete.html, parts/en/pied.html : en-tête et pied de page anglais (sans le blog, en français) ;
- contenu/en/pages.json : titre, slug, réglages Rank Math, fil d'Ariane, service de chaque page anglaise.
"""
import json
import re
import sys
from pathlib import Path

ICI = Path(__file__).resolve().parent
THEME = ICI.parent / "aurea"
TRAD = ICI.parent / "traduction"

TEXTE_RE = re.compile(r"(<!--.*?-->|<[^>]+>)", re.S)
ATTRS = ("alt", "aria-label", "title", "placeholder")
SAUTER = ("script", "style", "svg", "code")


def norme(t):
    return re.sub(r"\s+", " ", t.replace(" ", " ").replace(" ", " ")).strip()


def a_traduire(t):
    """Un texte à traduire contient des lettres hors repères ({{…}}) et codes courts ([…])."""
    reste = re.sub(r"\{\{[^}]+\}\}|\[[a-z_]+[^\]]*\]|&[a-z]+;|&#\d+;", " ", t)
    return bool(re.search(r"[A-Za-zÀ-ÿ]{2,}", reste))


def morceaux(html):
    """(type, valeur, contexte) : type texte, balise ou commentaire ; contexte = dans script/svg…"""
    pile = []
    for m in TEXTE_RE.split(html):
        if not m:
            continue
        if m.startswith("<!--"):
            yield "comm", m, bool(pile)
            continue
        if m.startswith("<"):
            nom = re.match(r"</?\s*([a-zA-Z0-9-]+)", m)
            nom = nom.group(1).lower() if nom else ""
            if nom in SAUTER:
                if m.startswith("</"):
                    if pile and pile[-1] == nom:
                        pile.pop()
                elif not m.endswith("/>"):
                    pile.append(nom)
            yield "balise", m, bool(pile)
            continue
        yield "texte", m, bool(pile)


def segments(html):
    out = []
    for typ, val, cache in morceaux(html):
        if cache:
            continue
        if typ == "texte" and a_traduire(val):
            out.append(norme(val))
        elif typ == "balise" and not val.startswith("</"):
            for a in ATTRS:
                for m in re.finditer(rf'\s{a}="([^"]*)"', val):
                    if a_traduire(m.group(1)):
                        out.append(norme(m.group(1)))
    return out


def typo_en(t):
    """Typographie anglaise : pas d'espace avant ? ! : ; et guillemets anglais."""
    t = re.sub(r"(?:\s|&nbsp;|\u00a0|\u202f)+([?!;:])(?=\s|$|<|&nbsp;)", r"\1", t)
    t = re.sub(r"«(?:\s|&nbsp;|\u00a0|\u202f)*", "\u201c", t)
    t = re.sub(r"(?:\s|&nbsp;|\u00a0|\u202f)*»", "\u201d", t)
    # Prix et pourcentages : « 1 000 € » devient « €1,000 », « 100 % » devient « 100% »
    t = re.sub(r"(\d{1,3}(?:[\u00a0\u202f ]\d{3})*)(?:\s|&nbsp;|\u00a0|\u202f)?€", lambda m: "€" + re.sub(r"[\u00a0\u202f ]", ",", m.group(1)), t)
    return re.sub(r"(\d)(?:\s|&nbsp;|\u00a0|\u202f)%", r"\1%", t)


def chercher(table, cle, ctx):
    """Traduction propre à une page (« page::texte »), sinon traduction commune."""
    for k in ((ctx + "::" + cle) if ctx else None, cle):
        if k and k in table and table[k] is not None:
            return table[k]
    return None


def appliquer(html, table, manquants, ctx=""):
    sortie = []
    for typ, val, cache in morceaux(html):
        if cache or typ == "comm":
            sortie.append(val)
            continue
        if typ == "texte":
            if a_traduire(val):
                cle = norme(val)
                trad = chercher(table, cle, ctx)
                if trad is not None:
                    debut = re.match(r"^\s*", val).group(0)
                    fin = re.search(r"\s*$", val).group(0)
                    val = debut + trad + fin
                else:
                    manquants.add(cle)
            sortie.append(typo_en(val))
            continue
        # balise : attributs traduisibles
        def attr(m):
            a, v = m.group(1), m.group(2)
            if not a_traduire(v):
                return m.group(0)
            cle = norme(v)
            trad = chercher(table, cle, ctx)
            if trad is not None:
                return f' {a}="{trad}"'
            manquants.add(cle)
            return m.group(0)
        sortie.append(re.sub(r'\s(' + "|".join(ATTRS) + r')="([^"]*)"', attr, val))
    return "".join(sortie)


def sans_blog(html):
    """Le blog et les articles restent en français : pas de lien dans la navigation anglaise."""
    return re.sub(r'<a\b[^>]*href="\{\{url:(?:conseils|article:[^}]+)\}\}"[^>]*>.*?</a>', "", html, flags=re.S)


def main():
    table = {}
    for lot in sorted((TRAD / "lots").glob("*.json")):
        table.update(json.loads(lot.read_text()))
    pages = json.loads((TRAD / "pages-en.json").read_text())
    manquants = set()
    (THEME / "contenu" / "en").mkdir(parents=True, exist_ok=True)
    (THEME / "parts" / "en").mkdir(parents=True, exist_ok=True)
    for nom in ("entete", "pied"):
        html = (THEME / "parts" / f"{nom}.html").read_text()
        (THEME / "parts" / "en" / f"{nom}.html").write_text(appliquer(sans_blog(html), table, manquants, nom))
    infos = {}
    fr = json.loads((THEME / "contenu" / "pages.json").read_text())
    for slug_en, p in pages.items():
        source = p["source"]
        fichier = "archive-realisations.html" if source == "realisations" else fr[source]["fichier"]
        html = (THEME / "contenu" / fichier).read_text()
        en = appliquer(html, table, manquants, Path(fichier).stem)
        (THEME / "contenu" / "en" / f"{slug_en}.html").write_text(en)
        info = {k: v for k, v in p.items()}
        h1 = re.search(r"<h1[^>]*>(.*?)</h1>", en, re.S)
        if h1:
            info["h1"] = re.sub(r"\s+", " ", re.sub(r"<[^>]+>", "", h1.group(1))).strip()
        info["fichier"] = f"en/{slug_en}.html"
        info["texte"] = fr.get(source, {}).get("texte", False)
        for k in ("type", "prix"):
            if k not in info and source in fr:
                info[k] = fr[source].get(k)
        if source == "realisations":
            info.setdefault("type", "collection")
        infos[slug_en] = info
        lt, ld = len(p["seo_titre"]), len(p["seo_description"])
        alerte = "" if 45 <= lt <= 62 and 130 <= ld <= 162 else "  ← longueur"
        print(f"  en/{slug_en:34} title {lt} · desc {ld}{alerte}")
        for tiret in (" - ", " – ", " — "):
            if tiret in p["seo_titre"] + p["seo_description"]:
                print("  ALERTE tiret :", slug_en)
    for vieux in (THEME / "contenu" / "en").glob("*.html"):  # pages anglaises renommées ou retirées
        if vieux.stem not in infos:
            vieux.unlink()
    (THEME / "contenu" / "en" / "pages.json").write_text(json.dumps(infos, ensure_ascii=False, indent=1) + "\n")
    m = sorted(manquants)
    (TRAD / "en-manquants.json").write_text(json.dumps({k: None for k in m}, ensure_ascii=False, indent=1) + "\n")
    print(f"Version anglaise : {len(infos)} pages, {len(table)} textes traduits, {len(m)} manquants")
    return len(m)


if __name__ == "__main__":
    sys.exit(1 if main() else 0)
