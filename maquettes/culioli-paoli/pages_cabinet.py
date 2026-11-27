# -*- coding: utf-8 -*-
"""Gabarits v2 : Le cabinet, Expertises, Ressources."""
from pages_expertises import EXPERTISES
from home import CHIPS, COLORS, ROMAN, SVG

T = '<span class="todo">à confirmer</span>'

CABINET_BODY = f"""
<section class="page-head"><div class="wrap">
{{{{CRUMBS}}}}
<div class="head-split">
  <div><span class="cote">Le cabinet</span>
    <h1 style="margin-top:1rem">Charles Culioli-Paoli, <em>avocat</em></h1>
    <p class="intro">Avocat pénaliste indépendant installé à Paris, <strong>Charles Culioli-Paoli</strong> a construit sa pratique entre la rigueur académique de Sciences Po et de Harvard Law School et l'expérience concrète des cabinets pénalistes et du Parquet de Paris.</p>
    <div class="hero-actions"><a class="btn btn--solid" href="/contact/">Prendre rendez-vous {{{{ARROW}}}}</a></div></div>
  <div class="head-photo"><img src="assets/img/charles-culioli-paoli-portrait.webp" width="400" height="400" alt="Portrait de Charles Culioli-Paoli, avocat au Barreau de Paris"></div>
</div></div></section>

<section class="section"><div class="wrap">
  <div class="bento stagger">
    <div class="b-card b-quote"><span class="cote" style="color:var(--gold-2)">Le portrait</span>
      <p>Défendre, c'est d'abord comprendre <em>une personne</em>, puis un dossier.</p>
      <div class="sig"><span><b>Charles Culioli-Paoli</b>Formulation proposée, à valider</span></div></div>
    <div class="b-card b-1"><span class="ic">{SVG["eye"]}</span><h3>Toute la chaîne pénale</h3><p>Garde à vue, enquête, information judiciaire, audience et exécution des peines, pour les mis en cause comme pour les victimes.</p></div>
    <div class="b-card b-2"><span class="ic">{SVG["cap"]}</span><h3>Dossiers techniques et internationaux</h3><p>Pénal des affaires, cybercriminalité, extradition, mandat d'arrêt européen, entraide pénale.</p></div>
  </div>
</div></section>

<section class="dark section"><div class="wrap">
  <div class="sec-head"><div><span class="cote">Formation</span><h2 class="sec-title">Entre <em>Paris et Cambridge</em></h2></div>
    <p>Deux écoles, deux traditions juridiques. Survolez ou touchez une carte pour la retourner.</p></div>
  <div class="flips" style="grid-template-columns:repeat(auto-fit,minmax(260px,1fr))">
    <button class="flip" type="button" aria-pressed="false" aria-label="LL.M., Harvard Law School : afficher le détail"><span class="flip-in">
      <span class="flip-f"><span class="hint"><span>Cambridge, MA</span><span>Retourner</span></span><span class="deg">LL.M.</span><span class="sch">Harvard Law School</span></span>
      <span class="flip-b"><b>Common law</b><p>Une immersion dans la common law et la procédure pénale américaine, utile aux dossiers internationaux.{T}</p></span></span></button>
    <button class="flip" type="button" aria-pressed="false" aria-label="Master, Sciences Po : afficher le détail"><span class="flip-in">
      <span class="flip-f"><span class="hint"><span>Paris</span><span>Retourner</span></span><span class="deg">Master</span><span class="sch">École de droit de Sciences Po</span></span>
      <span class="flip-b"><b>Contentieux</b><p>Une formation exigeante, ouverte sur le contentieux et l'international.{T}</p></span></span></button>
    <button class="flip" type="button" aria-pressed="false" aria-label="Barreaux : afficher le détail"><span class="flip-in">
      <span class="flip-f"><span class="hint"><span>Inscription</span><span>Retourner</span></span><span class="deg">Paris</span><span class="sch">Barreau de Paris · New York{T}</span></span>
      <span class="flip-b"><b>Serment 2025</b><p>Inscription aux barreaux de Paris et de New York mentionnée sur sa fiche professionnelle, à confirmer avant publication.</p></span></span></button>
  </div>
</div></section>

<section class="section"><div class="wrap parcours">
  <div class="reveal">
    <span class="cote">Expériences</span>
    <h2 class="sec-title" style="font-size:var(--step-3);max-width:18ch">Le pénal vu de <em>chaque côté</em> de la barre</h2>
    <p style="margin-top:1.4rem;color:var(--ink-soft);max-width:52ch">Cabinets pénalistes reconnus, Parquet de Paris, ministère de l'Économie : une double perspective, celle de la défense et celle de l'accusation, qui nourrit sa manière de lire un dossier.{T}</p>
    <div class="langs"><span>Français</span><span lang="en">English</span><span lang="es">Español</span><span lang="ar">العربية</span><span lang="co">Corsu</span></div>
  </div>
  <ul class="timeline stagger" style="margin:0">
    <li><b>Fedida &amp; Associés</b><span>Défense pénale</span></li>
    <li><b>Schapira Associés</b><span>Défense pénale</span></li>
    <li><b>Parquet de Paris</b><span>Ministère public</span></li>
    <li><b>Ministère de l'Économie</b><span>Administration</span></li>
    <li><b>Barreau de Paris</b><span>Prestation de serment 2025</span></li>
  </ul>
</div></section>

<section class="section--tight" style="padding-top:0"><div class="wrap">
  <div class="final reveal">
    <div class="txt"><span class="cote">La vision</span><h2>Connaître le dossier, être <em>présent</em>, dire la vérité.</h2>
      <p>Trois exigences pour une défense pénale. Formulation proposée, à valider par Me Culioli-Paoli.</p>
      <div class="hero-actions"><a class="btn btn--solid" href="/contact/">Rencontrer Charles Culioli-Paoli {{{{ARROW}}}}</a><a class="btn btn--ghost" href="/expertises/">Les expertises</a></div></div>
    <div class="img"><img src="assets/img/charles-culioli-paoli-tribunal-paris.webp" width="1600" height="1200" loading="lazy" alt="Charles Culioli-Paoli en robe au tribunal judiciaire de Paris"></div>
  </div>
</div></section>"""


def expertises_hub(p):
    stack = "".join(
        f'''<a class="s-card" href="/{x["slug"]}/" style="--i:{i};--c:{COLORS[i]}">
  <div class="l"><span class="k">Expertise {ROMAN[i]}</span><h3>{x["nav"]}</h3><p>{x["somm"]}</p>
    <span class="chips">{"".join(f'<span class="chip">{c}</span>' for c in CHIPS[x["slug"]])}</span></div>
  <div class="r"><span class="cta">{{{{ARROW}}}}</span><span class="big" aria-hidden="true">{ROMAN[i]}</span></div></a>'''
        for i, x in enumerate(EXPERTISES))
    return f"""<section class="page-head"><div class="wrap">{{{{CRUMBS}}}}
<span class="cote">Expertises</span><h1 style="margin-top:1rem">Les domaines <em>d'intervention</em> du cabinet</h1>
<p class="intro">Le cabinet de <strong>Me Charles Culioli-Paoli</strong> intervient en droit pénal, du premier acte d'enquête à l'exécution de la peine, en France et dans les dossiers internationaux.</p></div></section>
<section class="section"><div class="wrap"><div class="stack">{stack}</div>
<div class="list-cards" style="margin-top:3rem">
  <a href="/avocat-garde-a-vue-paris/"><span><span class="cote">Procédure</span><h3>Garde à vue : vos droits et le rôle de l'avocat</h3></span><span class="arr">{{{{ARROW}}}}</span></a>
  <a href="/guide-amenagement-de-peine/"><span><span class="cote">Guide</span><h3>Aménagement de peine : conditions, procédure et délais</h3></span><span class="arr">{{{{ARROW}}}}</span></a>
</div>
<p class="ref" style="margin-top:1.5rem">Mention déontologique : les domaines présentés sont des domaines d'activité dominante et non des mentions de spécialisation délivrées par le Conseil national des barreaux.</p>
</div></section>"""


def ressources_body(p, RES):
    themes = ""
    for i, (theme, items) in enumerate(RES, 1):
        li = "".join(
            (f'<a href="{u}"><span><span class="cote">{st}</span><h3>{t}</h3></span><span class="arr">{{{{ARROW}}}}</span></a>' if u else
             f'<div class="muted"><span><span class="cote">{st}</span><h3>{t}</h3></span></div>')
            for t, u, st in items)
        themes += f'<div class="theme"><span class="cote">Thème {i:02d}</span><h2>{theme}</h2><div class="list-cards" style="margin-top:1rem">{li}</div></div>'
    return f"""<section class="page-head"><div class="wrap">{{{{CRUMBS}}}}
<span class="cote">Ressources</span><h1 style="margin-top:1rem">Comprendre le droit pénal, <em>thème par thème</em></h1>
<p class="intro">Des guides et des analyses rédigés par <strong>Me Charles Culioli-Paoli</strong> pour comprendre une procédure, connaître ses droits et préparer sa défense.</p></div></section>
<section class="section"><div class="wrap">
<div class="covers stagger" style="margin-bottom:1rem">
  <a class="cover cover--gold" href="/guide-amenagement-de-peine/"><span class="meta"><span>Guide</span><span>6 min</span></span><h3>Aménagement de peine : conditions, procédure et délais</h3><span class="glyph" aria-hidden="true">A</span></a>
  <a class="cover" href="/avocat-garde-a-vue-paris/"><span class="meta"><span>Procédure</span><span>4 min</span></span><h3>Garde à vue : vos droits et le rôle de l'avocat</h3><span class="glyph" aria-hidden="true">G</span></a>
  <a class="cover cover--ink" href="/avocat-extradition/"><span class="meta"><span>International</span><span>5 min</span></span><h3>Mandat d'arrêt européen : les premières heures</h3><span class="glyph" aria-hidden="true">E</span></a>
</div>
<div class="theme-grid">{themes}</div></div></section>"""
