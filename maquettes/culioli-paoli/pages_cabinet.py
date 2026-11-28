# -*- coding: utf-8 -*-
"""Pages Le cabinet, Expertises, Ressources, Honoraires · direction « Éditorial parisien »."""
from pages_expertises import EXPERTISES

T = '<span class="todo">à confirmer</span>'

END_BAND = """<section class="navy end-band"><div class="wrap">
  <div class="t"><p class="eyebrow">Contact</p><h2 style="margin-top:1rem">{title}</h2><p>{text}</p></div>
  <div class="a"><a class="btn btn--light" href="/contact/">Prendre rendez-vous</a><button class="urg-link urg-link--light" type="button" data-urgence><span class="dot" aria-hidden="true"></span>Urgence pénale</button></div>
</div></section>"""

CABINET_BODY = f"""
<section class="page-head"><div class="wrap">
{{{{CRUMBS}}}}
<div class="head-split">
  <div><p class="eyebrow">Le cabinet</p>
    <h1>Charles Culioli-Paoli, <em>avocat</em></h1>
    <p class="intro">Avocat pénaliste indépendant installé à Paris, <strong>Charles Culioli-Paoli</strong> a construit sa pratique entre la formation académique de Sciences Po et de Harvard Law School et l'expérience concrète des cabinets pénalistes et du Parquet de Paris.</p></div>
  <figure class="head-photo"><img src="assets/img/charles-culioli-paoli-portrait.webp" width="400" height="400" alt="Portrait de Charles Culioli-Paoli, avocat au Barreau de Paris"></figure>
</div></div></section>

<section class="section"><div class="wrap sec-grid">
  <div class="sec-side"><div class="sticky"><p class="eyebrow">Portrait</p><h2>Défendre, c'est d'abord <em>comprendre</em></h2></div></div>
  <div class="sec-main prose">
    <p class="lead">Charles Culioli-Paoli exerce en droit pénal, aux côtés des personnes mises en cause comme des victimes. Sa pratique couvre l'ensemble de la chaîne pénale : garde à vue, enquête, information judiciaire, audience et exécution des peines.</p>
    <p>Il porte une attention particulière aux dossiers qui exigent une lecture technique, comme le droit pénal des affaires ou la cybercriminalité, et à ceux qui franchissent les frontières : extradition, mandat d'arrêt européen, entraide pénale internationale.</p>
    <p>Chaque dossier est suivi personnellement. La personne défendue a un interlocuteur unique, qui lui explique la procédure, les risques et les choix possibles.</p>
    <p class="small muted">Texte de présentation proposé, à réécrire avec Me Culioli-Paoli pour refléter sa voix.</p>
  </div>
</div></section>

<section class="arch" aria-label="Tribunal judiciaire de Paris" style="margin-top:0">
  <img src="assets/img/tribunal-judiciaire-paris-architecture.webp" width="1600" height="520" loading="lazy" alt="Verrières de la salle des pas perdus du tribunal judiciaire de Paris">
  <div class="wrap"><div class="arch-note reveal">
    <blockquote>« La défense pénale demande de connaître le dossier mieux que quiconque, d'être présent quand tout se décide, et de dire la vérité à la personne défendue. »</blockquote>
    <p class="eyebrow">Formulation proposée, à valider</p></div></div>
</section>

<section class="section"><div class="wrap sec-grid">
  <div class="sec-side"><div class="sticky"><p class="eyebrow">Formation</p><h2>Deux écoles, <em>deux traditions juridiques</em></h2></div></div>
  <div class="sec-main">
    <div class="cv" style="margin-top:0"><h3>Formation</h3><dl>
      <div><dt>LL.M., Harvard Law School</dt><dd>Cambridge, Massachusetts. Une immersion dans la common law et la procédure pénale américaine, utile aux dossiers internationaux.{T}</dd></div>
      <div><dt>Master en droit, École de droit de Sciences Po</dt><dd>Paris. Une formation exigeante, ouverte sur le contentieux et l'international.{T}</dd></div>
    </dl></div>
    <div class="cv"><h3>Expérience</h3><dl>
      <div><dt>Fedida &amp; Associés</dt><dd>Cabinet de défense pénale, Paris{T}</dd></div>
      <div><dt>Schapira Associés</dt><dd>Cabinet de défense pénale, Paris{T}</dd></div>
      <div><dt>Parquet de Paris</dt><dd>Ministère public{T}</dd></div>
      <div><dt>Ministère de l'Économie</dt><dd>Administration centrale{T}</dd></div>
    </dl></div>
    <div class="cv"><h3>Inscription et langues</h3><dl>
      <div><dt>Barreau de Paris</dt><dd>Prestation de serment en 2025{T}</dd></div>
      <div><dt>Barreau de New York</dt><dd>Inscription mentionnée sur sa fiche professionnelle{T}</dd></div>
      <div><dt>Langues de travail</dt><dd>Français, anglais, espagnol, arabe, corse{T}</dd></div>
    </dl></div>
  </div>
</div></section>

""" + END_BAND.format(title="Rencontrer <em>Charles Culioli-Paoli</em>",
                      text="Au cabinet, à Paris 8<sup>e</sup>, ou en visioconférence. Un premier échange permet d'analyser votre situation.")


def expertises_hub(p):
    items = "".join(
        f'<li><a href="/{x["slug"]}/"><span class="num">{i+1:02d}</span><h3>{x["nav"]}</h3><p>{x["somm"]}</p>{{{{ARROW}}}}</a></li>'
        for i, x in enumerate(EXPERTISES))
    return f"""<section class="page-head"><div class="wrap">{{{{CRUMBS}}}}
<p class="eyebrow">Expertises</p><h1>Les domaines <em>d'intervention</em></h1>
<p class="intro">Le cabinet de <strong>Me Charles Culioli-Paoli</strong> intervient en droit pénal, du premier acte d'enquête à l'exécution de la peine, en France et dans les dossiers internationaux.</p></div></section>
<section class="section"><div class="wrap">
<ol class="index-list stagger">{items}</ol>
<div class="sec-grid" style="margin-top:var(--sp-5)">
  <div class="sec-side"><p class="eyebrow">Procédures</p><h2 style="font-size:var(--fs-h3)">Une situation <em>précise</em>&nbsp;?</h2></div>
  <div class="sec-main"><div class="related" style="margin-top:0">
    <a href="/avocat-garde-a-vue-paris/"><b>Garde à vue</b><span>Les droits de la personne et le rôle de l'avocat</span></a>
    <a href="/guide-amenagement-de-peine/"><b>Aménagement de peine</b><span>Conditions, procédure et délais</span></a>
    <a href="/etablissements-penitentiaires/"><b>Personne détenue</b><span>Intervention en établissement pénitentiaire</span></a>
  </div>
  <p class="small muted" style="margin-top:1.5rem">Les domaines présentés sont des domaines d'activité dominante. Ils ne constituent pas des mentions de spécialisation délivrées par le Conseil national des barreaux.</p></div>
</div></div></section>"""


def ressources_body(p, RES):
    themes = ""
    for i, (theme, items) in enumerate(RES, 1):
        li = "".join(
            (f'<li><a href="{u}"><span>{t}</span><span class="st">Lire</span></a></li>' if u else
             f'<li><div class="soon"><span>{t}</span><span class="st">À paraître</span></div></li>')
            for t, u, st in items)
        themes += f'<div class="theme"><span class="num">{i:02d}</span><h2>{theme}</h2><ul>{li}</ul></div>'
    return f"""<section class="page-head"><div class="wrap">{{{{CRUMBS}}}}
<p class="eyebrow">Ressources</p><h1>Comprendre le droit pénal, <em>thème par thème</em></h1>
<p class="intro">Des guides rédigés par <strong>Me Charles Culioli-Paoli</strong> pour comprendre une procédure, connaître ses droits et préparer sa défense.</p></div></section>
<section class="section"><div class="wrap">
<div class="reads" style="margin-bottom:var(--sp-6)">
  <a class="read-main" href="/guide-amenagement-de-peine/"><p class="eyebrow">À la une · Guide</p><h3>Aménagement de peine : conditions, procédure et délais</h3><p>Bracelet électronique, semi-liberté, libération conditionnelle : ce que prévoit le Code de procédure pénale et comment préparer la demande.</p></a>
  <div class="read-list">
    <a href="/avocat-garde-a-vue-paris/"><p class="eyebrow">Procédure</p><h3>Garde à vue : vos droits et le rôle de l'avocat</h3></a>
    <a href="/avocat-extradition/"><p class="eyebrow">International</p><h3>Mandat d'arrêt européen : les premières heures</h3></a>
  </div>
</div>
<div class="themes">{themes}</div></div></section>"""


HONORAIRES = {
    "slug": "honoraires", "nav": "Honoraires", "prio": "P2", "schema_label": "WebPage",
    "kw": "honoraires avocat pénaliste Paris", "kw2": ["tarif avocat pénaliste", "convention d'honoraires avocat", "prix avocat garde à vue"],
    "title": "Honoraires d'avocat pénaliste à Paris | Culioli-Paoli",
    "desc": "Comment sont fixés les honoraires de Me Charles Culioli-Paoli, avocat pénaliste à Paris : convention écrite, forfait ou temps passé, transparence.",
    "h1_plain": "Honoraires",
    "body": f"""<section class="page-head"><div class="wrap">{{{{CRUMBS}}}}
<div class="head-split">
  <div><p class="eyebrow">Honoraires</p><h1>Des honoraires <em>clairs et convenus par écrit</em></h1>
  <p class="intro">La loi impose une <strong>convention d'honoraires</strong> écrite entre l'avocat et son client. Elle précise le montant ou le mode de calcul des honoraires avant toute intervention.</p></div>
  <aside class="head-aside"><dl>
    <div><dt>Premier rendez-vous</dt><dd>Modalités à préciser{T}</dd></div>
    <div><dt>Taux horaire indicatif</dt><dd>À préciser{T}</dd></div>
    <div><dt>Paiement</dt><dd>Échelonnement possible selon le dossier{T}</dd></div>
  </dl></aside>
</div></div></section>
<section class="section"><div class="wrap sec-grid">
  <div class="sec-side"><div class="sticky"><p class="eyebrow">Principes</p><h2>Comment sont fixés <em>les honoraires</em></h2></div></div>
  <div class="sec-main prose">
    <p class="lead">Les honoraires tiennent compte de la complexité du dossier, du temps consacré, de l'urgence et des diligences nécessaires. Ils sont présentés lors du premier échange.</p>
    <h3>Le forfait</h3>
    <p>Adapté aux interventions bien délimitées : assistance en garde à vue, audience correctionnelle, requête en aménagement de peine.</p>
    <h3>Le temps passé</h3>
    <p>Adapté aux dossiers dont la durée est difficile à prévoir, comme une information judiciaire ou une affaire de droit pénal des affaires. Un relevé détaillé des diligences est communiqué.</p>
    <h3>L'honoraire complémentaire de résultat</h3>
    <p>Il peut s'ajouter à un honoraire fixe lorsque la convention le prévoit. La loi interdit en revanche un honoraire fixé uniquement en fonction du résultat.</p>
    <h3>L'aide juridictionnelle</h3>
    <p>Selon ses ressources, une personne peut bénéficier de l'aide juridictionnelle. Les conditions sont présentées sur <a href="https://www.service-public.fr/" target="_blank" rel="noopener">service-public.fr</a>.<span class="todo">acceptation à confirmer</span></p>
    <p class="small muted">Référence : article 10 de la loi n° 71-1130 du 31 décembre 1971. Les montants seront affichés seulement si Me Culioli-Paoli souhaite les communiquer publiquement.</p>
  </div>
</div></section>
""" + END_BAND.format(title="Obtenir une <em>estimation</em>", text="Décrivez votre situation : le cabinet vous indique le mode de facturation adapté avant toute intervention."),
}
