# -*- coding: utf-8 -*-
"""Document de travail : audit, trois pistes créatives, choix, design system, auto-évaluation."""

SPEC = {
    "A": {"bg": "#F7F4EE", "fg": "#262A31", "acc": "#8A6A45", "font": "'Cormorant Garamond',serif", "sans": "Manrope,sans-serif"},
    "B": {"bg": "#13223A", "fg": "#EFEBE3", "acc": "#AAB3C1", "font": "'Libre Baskerville',Georgia,serif", "sans": "'DM Sans',sans-serif"},
    "C": {"bg": "#E9E7E3", "fg": "#1E1F21", "acc": "#7B7F86", "font": "'DM Sans',sans-serif", "sans": "'DM Sans',sans-serif"},
}


def specimen(k, title, sub, img=True):
    s = SPEC[k]
    pic = ('<div style="flex:0 0 38%;background:url(assets/img/charles-culioli-paoli-avocat-robe.webp) center 20%/cover"></div>' if img else "")
    if k == "C":
        return f'''<div class="spec" style="background:{s["bg"]};color:{s["fg"]}">
<div style="height:58%;background:url(assets/img/tribunal-judiciaire-paris-architecture.webp) center/cover"></div>
<div style="padding:14px 16px;font-family:{s["sans"]}"><div style="font-size:9px;letter-spacing:.2em;text-transform:uppercase;color:{s["acc"]}">Avocat · Paris</div>
<div style="font-size:17px;font-weight:500;margin-top:6px;letter-spacing:-.01em">{title}</div><div style="font-size:10px;margin-top:4px;color:{s["acc"]}">{sub}</div></div></div>'''
    return f'''<div class="spec" style="background:{s["bg"]};color:{s["fg"]};display:flex">
<div style="flex:1;padding:18px 16px;display:flex;flex-direction:column;justify-content:center">
<div style="font:500 8px/1.4 {s["sans"]};letter-spacing:.2em;text-transform:uppercase;color:{s["acc"]}">Avocat au Barreau de Paris</div>
<div style="font-family:{s["font"]};font-size:{21 if k == "A" else 17}px;line-height:1.15;margin-top:8px">{title}</div>
<div style="font:400 9.5px/1.5 {s["sans"]};margin-top:8px;opacity:.75">{sub}</div>
<div style="margin-top:12px;align-self:flex-start;font:500 8.5px/1 {s["sans"]};padding:7px 10px;border:1px solid currentColor">Prendre rendez-vous</div></div>{pic}</div>'''


ROWS = [
    ("Palette", "Ivoire dominant, anthracite, bleu nuit en contrepoint, bronze très discret", "Bleu nuit dominant, blanc cassé, gris bleuté", "Gris minéral, blanc, noir, aucune couleur d'accent"),
    ("Typographies", "Cormorant Garamond (titres) et Manrope (texte)", "Libre Baskerville et DM Sans", "DM Sans seule, en graisses fines"),
    ("Photographie", "Portraits authentiques et détails d'architecture du tribunal, cadrages éditoriaux", "Portrait en noir et blanc, fonds sombres", "Grands formats d'architecture, l'avocat apparaît peu"),
    ("Héros", "Deux colonnes déséquilibrées 6/5 : nom et proposition à gauche, portrait en robe à droite", "Plein écran bleu nuit, nom centré, photo en arrière-plan assombrie", "Image d'architecture plein cadre, titre court en bas à gauche"),
    ("Navigation", "Barre ivoire fine, six entrées, soulignement animé, urgence en lien discret", "Menu caché derrière un bouton, même sur ordinateur", "Navigation minimale en capitales, sans accent"),
    ("Compositions", "Colonnes latérales collantes, index numérotés, filets fins, alternance ivoire et bleu nuit", "Blocs symétriques, sections sombres successives", "Grilles très graphiques, beaucoup de vide, peu de texte"),
    ("Ambiance", "Publication juridique de qualité, luxe discret, lisible", "Autorité, gravité, distance", "Contemporain, froid, galerie d'architecte"),
]


def direction_body(p):
    rows = "".join(f"<tr><td><b>{a}</b></td><td>{b}</td><td>{c}</td><td>{d}</td></tr>" for a, b, c, d in ROWS)
    return f"""<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Libre+Baskerville&family=DM+Sans:wght@400;500&display=swap">
<section class="page-head"><div class="wrap">{{{{CRUMBS}}}}
<div class="head-split"><div><p class="eyebrow">Document de travail · non indexé</p><h1>Direction artistique <em>et design system</em></h1>
<p class="intro">Audit de la version précédente, trois pistes créatives, choix motivé, règles de conception et revue finale. Cette page accompagne la maquette et ne sera pas publiée.</p></div>
<aside class="head-aside"><dl><div><dt>Piste retenue</dt><dd>A · Éditorial parisien</dd></div><div><dt>Typographies</dt><dd>Cormorant Garamond, Manrope</dd></div><div><dt>Grille</dt><dd>12 colonnes, contenu max 1240 px</dd></div></dl></aside></div>
</div></section>

<section class="section"><div class="wrap sec-grid">
<div class="sec-side"><div class="sticky"><p class="eyebrow">Étape 1</p><h2>Audit <em>visuel</em></h2><p class="muted" style="margin-top:1rem">Ce qui ne fonctionnait pas dans les versions précédentes.</p></div></div>
<div class="sec-main prose">
<ol class="steps" style="margin-top:0">
<li><div><b>Typographie disproportionnée</b><span>Titres jusqu'à 168 px, graisses fortes partout, hiérarchie écrasée : tout criait au même niveau.</span></div></li>
<li><div><b>Compositions de modèle</b><span>Mosaïques de cartes, cartes empilées, compteurs, bandeau défilant : des composants de bibliothèque, pas des choix liés au cabinet.</span></div></li>
<li><div><b>Effets démonstratifs</b><span>Halo au curseur, inclinaison 3D, cartes qui se retournent, défilement horizontal épinglé : une impression technologique contraire au luxe discret recherché.</span></div></li>
<li><div><b>Rythme monotone</b><span>Succession de sections sombres puis beiges sans intention, mêmes marges et même gabarit de titre partout.</span></div></li>
<li><div><b>Photographie mal exploitée</b><span>Le même portrait répété, la photographie du tribunal recadrée avec une tête coupée, aucun détail d'architecture mis en valeur.</span></div></li>
<li><div><b>Informations internes exposées</b><span>Statuts de publication et potentiel de recherche affichés sur des pages publiques : déplacés dans ce document.</span></div></li>
</ol></div></div></section>

<section class="section tone"><div class="wrap">
<div class="sec-grid" style="margin-bottom:var(--sp-4)"><div class="sec-side"><p class="eyebrow">Étapes 2 et 3</p><h2>Trois pistes <em>créatives</em></h2></div>
<div class="sec-main"><p class="lead muted" style="max-width:34em;padding-top:2rem">Trois directions réellement différentes, présentées sur le même contenu de héros.</p></div></div>
<div class="specs">
<figure><span class="num">A</span>{specimen("A", "Charles Culioli-Paoli,<br><i>avocat pénaliste à Paris</i>", "Une défense pénale exigeante et discrète.")}<figcaption><b>Éditorial parisien</b>Ivoire, serif élégante, colonnes déséquilibrées, photographie authentique.</figcaption></figure>
<figure><span class="num">B</span>{specimen("B", "Charles Culioli-Paoli<br>Avocat pénaliste", "Défense pénale à Paris.")}<figcaption><b>Institutionnel contemporain</b>Bleu nuit dominant, contrastes forts, structure symétrique.</figcaption></figure>
<figure><span class="num">C</span>{specimen("C", "Culioli-Paoli. Défense pénale.", "Paris · Essonne")}<figcaption><b>Architectural minimaliste</b>Grandes images d'architecture, typographie discrète, beaucoup de vide.</figcaption></figure>
</div>
<div class="table-wrap" style="margin-top:var(--sp-5)"><table><thead><tr><th>Critère</th><th>A · Éditorial parisien</th><th>B · Institutionnel</th><th>C · Architectural</th></tr></thead><tbody>{rows}</tbody></table></div>
<div class="quote-block" style="margin-top:var(--sp-5)"><p class="eyebrow">Choix</p><blockquote style="margin-top:1rem">La piste A est retenue.</blockquote>
<p class="muted" style="max-width:var(--read)">Elle sert les deux objectifs du projet. Pour la marque, elle incarne l'avocat grâce aux portraits authentiques et donne une impression de publication juridique soignée, sans ostentation. Pour le SEO, une mise en page éditoriale accueille naturellement des contenus longs et structurés. La piste B, très sombre, retombait dans l'ambiance technologique reprochée. La piste C effaçait l'avocat et réduisait le texte, ce qui pénaliserait le référencement. Le bleu nuit de la piste B est conservé uniquement en contrepoint : domaines d'intervention, bandeau de contact, pied de page.</p></div>
</div></section>

<section class="section"><div class="wrap sec-grid">
<div class="sec-side"><div class="sticky"><p class="eyebrow">Design system</p><h2>Règles <em>de conception</em></h2></div></div>
<div class="sec-main prose">
<h3 style="margin-top:0">Couleurs</h3>
<div class="swatches">
<div><span style="background:#F7F4EE"></span><b>Ivoire</b>#F7F4EE · fond principal</div>
<div><span style="background:#EFEAE1"></span><b>Ivoire ombré</b>#EFEAE1 · une section par page au plus</div>
<div><span style="background:#13223A"></span><b>Bleu nuit</b>#13223A · contrepoint institutionnel</div>
<div><span style="background:#262A31"></span><b>Anthracite</b>#262A31 · texte</div>
<div><span style="background:#DAD3C7"></span><b>Gris clair</b>#DAD3C7 · filets</div>
<div><span style="background:#8A6A45"></span><b>Bronze</b>#8A6A45 · numéros et détails seulement</div>
</div>
<h3>Typographie</h3>
<div class="table-wrap"><table><thead><tr><th>Rôle</th><th>Police</th><th>Ordinateur</th><th>Mobile</th></tr></thead><tbody>
<tr><td><b>H1</b></td><td>Cormorant Garamond 500</td><td>54 px</td><td>36 px</td></tr>
<tr><td><b>H2</b></td><td>Cormorant Garamond 500</td><td>37 px</td><td>28 px</td></tr>
<tr><td><b>H3</b></td><td>Cormorant Garamond 500</td><td>25 px</td><td>21 px</td></tr>
<tr><td><b>Introduction</b></td><td>Manrope 400</td><td>19 px</td><td>17 px</td></tr>
<tr><td><b>Texte</b></td><td>Manrope 400, interligne 1,75</td><td>16 px</td><td>16 px</td></tr>
<tr><td><b>Navigation, boutons</b></td><td>Manrope 500</td><td>13 px</td><td>13 px</td></tr>
<tr><td><b>Surtitres</b></td><td>Manrope 500, capitales espacées</td><td>11,5 px</td><td>11,5 px</td></tr>
</tbody></table></div>
<h3>Grille et espacements</h3>
<p>Grille de 12 colonnes, contenu limité à 1240 px, marges latérales de 20 px minimum. Compositions déséquilibrées : 4/8 pour les sections avec titre latéral collant, 6/5 pour le héros, 4/6 pour la présentation. Largeur de lecture limitée à 62 caractères. Espacements : 8, 16, 24, 40, 64 px, puis 72 à 120 px entre les sections.</p>
<h3>Composants</h3>
<ul><li>Bouton principal : fond bleu nuit, angles droits, texte de 13 px, 48 px de haut. Au survol, le fond devient transparent.</li>
<li>Lien : texte souligné d'un filet qui se rétracte au survol, flèche qui avance de 4 px.</li>
<li>Filets : 1 px gris clair, ou anthracite pour ouvrir une liste. Aucune ombre, aucun arrondi.</li>
<li>Index numéroté pour les domaines, les étapes et les territoires, à la place des cartes.</li>
<li>Mouvement : apparition de 16 px en fondu, soulignements, léger décalage des lignes au survol. Désactivé si l'utilisateur réduit les animations.</li></ul>
<h3>Photographie</h3>
<p>Uniquement des photographies authentiques de Me Culioli-Paoli et de lieux réels. Lumière naturelle, tonalités froides et neutres. Le portrait en robe ouvre le site, le portrait studio accompagne la présentation, et les verrières du tribunal judiciaire de Paris servent de motif architectural. Recommandation : une séance photo professionnelle (cabinet, mains et dossiers, façades du Faubourg Saint-Honoré) pour enrichir les pages intérieures.</p>
</div></div></section>

<section class="section tone"><div class="wrap sec-grid">
<div class="sec-side"><div class="sticky"><p class="eyebrow">Stratégie</p><h2>Notes <em>SEO internes</em></h2></div></div>
<div class="sec-main prose">
<p><b>Régions proposées.</b> La Corse : le nom du cabinet et la pratique de la langue corse lui donnent une légitimité naturelle, et peu de cabinets pénalistes sont visibles sur les requêtes liées à Borgo ou à la cour d'appel de Bastia. La région PACA, avec Marseille et Nice : des dossiers transfrontaliers proches des compétences en extradition. Lyon : une JIRS et un contentieux pénal des affaires important. Ces trois pistes sont à valider avec des données réelles de volume.</p>
<p><b>Établissements pénitentiaires.</b> Les pages à publier en priorité, selon l'estimation qualitative : Fleury-Mérogis (publiée dans la maquette), Fresnes, Paris-La Santé, puis Villepinte et Réau. Chaque page devra comporter des informations propres pour éviter les pages satellites.</p>
</div></div></section>

<section class="section"><div class="wrap sec-grid">
<div class="sec-side"><div class="sticky"><p class="eyebrow">Étape 7</p><h2>Auto-<em>évaluation</em></h2><p class="muted" style="margin-top:1rem">Revue de direction artistique après contrôle des pages rendues sur ordinateur et sur mobile.</p></div></div>
<div class="sec-main">
<div class="table-wrap" style="margin-top:0"><table><thead><tr><th>Critère</th><th>Poids</th><th>Note</th><th>Constat</th></tr></thead><tbody>
<tr><td><b>Qualité esthétique générale</b></td><td>25 %</td><td>8/10</td><td>Sobre, aéré, hiérarchie lisible. La qualité finale dépendra de la séance photo.</td></tr>
<tr><td><b>Originalité et identité</b></td><td>20 %</td><td>7,5/10</td><td>Identité éditoriale cohérente, notamment la citation posée sur l'architecture du tribunal. Elle gagnera en singularité avec un monogramme dessiné par un typographe.</td></tr>
<tr><td><b>Typographie et compositions</b></td><td>15 %</td><td>8,5/10</td><td>Échelle conforme au brief, colonnes déséquilibrées, aucune section construite sur le même gabarit que la précédente.</td></tr>
<tr><td><b>Cohérence entre les pages</b></td><td>15 %</td><td>8,5/10</td><td>Un seul système, décliné en gabarits différents selon le contenu : article, index, fiche, formulaire.</td></tr>
<tr><td><b>Qualité photographique</b></td><td>10 %</td><td>6/10</td><td>Deux photographies seulement, dont une prise au téléphone. C'est le principal point à améliorer.</td></tr>
<tr><td><b>Expérience utilisateur</b></td><td>10 %</td><td>8,5/10</td><td>Urgence accessible partout, numéro révélé au clic, formulaire validé champ par champ, sommaire de lecture.</td></tr>
<tr><td><b>Responsive</b></td><td>5 %</td><td>8,5/10</td><td>Sections recomposées sur mobile, barre d'actions basse, aucun défilement horizontal.</td></tr>
</tbody></table></div>
<p class="lead" style="margin-top:2rem">Note pondérée : 7,9 sur 10.</p>
<p class="muted" style="margin-top:.6rem;max-width:var(--read)">Pour aller au-delà : une séance photo, un monogramme dessiné sur mesure, et la validation des contenus par Me Culioli-Paoli.</p>
</div></div></section>"""


DIRECTION = {
    "slug": "direction-artistique", "nav": "Direction artistique", "title": "Direction artistique | Maquette Culioli-Paoli",
    "desc": "Document de travail.", "kw": "", "h1_plain": "Direction artistique", "noindex": True, "body": direction_body,
}
