"""Visuels dessinés en HTML et CSS (téléphones, mini sites), réutilisés sur toutes les pages."""


def verrouillage(notifs, date="mardi 14 octobre", heure="09:41"):
    """Écran de verrouillage avec notifications. notifs : [(appli, titre, texte, quand)]."""
    n = "".join(f'<div class="notif" style="--i:{i}"><span class="notif-app{"" if a == "A" else " is-cal"}">{"A" if a == "A" else '<svg><use href="#i-calendar"/></svg>'}</span><div><p><b>{"Aurea" if a == "A" else "Agenda"}</b><em>{q}</em></p><strong>{t}</strong><span>{x}</span></div></div>' for i, (a, t, x, q) in enumerate(notifs))
    return f'<div class="lock"><div class="lock-statut"><span>{heure}</span><span class="lock-ico"><i></i><i></i><i></i></span></div><div class="lock-heure"><small>{date}</small><b>{heure}</b></div><div class="lock-notifs">{n}</div><div class="lock-bas"><i></i></div></div>'


DEMANDES = [("A", "Nouveau devis reçu", "Cabinet Martin · site vitrine", "maintenant"),
            ("A", "Rappel demandé", "BTP Rénov 92 · « Rappelez moi »", "12 min"),
            ("C", "RDV confirmé", "Visio découverte · jeudi 10:00", "1 h")]


def statut():
    return '<div class="m-statut"><span>9:41</span><span class="lock-ico"><i></i><i></i><i></i></span></div>'


def novexia_mobile():
    return f"""<div class="phone m-phone"><div class="phone-view m-novexia">
  {statut()}
  <div class="mn-nav"><b>NOVEXIA<small>CONSTRUCTION</small></b><span class="mn-burger"><i></i><i></i></span></div>
  <div class="mn-hero"><img src="assets/realisations/novexia.webp" alt="" width="1200" height="620" loading="lazy"><div><small>Construction · Rénovation</small><strong>Construisons <em>votre avenir</em></strong><span class="mn-btn o">Devis gratuit</span><span class="mn-btn">Nous appeler</span></div></div>
  <div class="mn-note"><b>+500</b><em>projets réalisés</em></div>
  <div class="mn-services"><span><i></i>Construction neuve</span><span><i></i>Rénovation</span><span><i></i>Isolation</span></div>
</div></div>"""


def perle_mobile():
    return f"""<div class="phone m-phone"><div class="phone-view m-shop">
  {statut()}
  <div class="sh-nav"><span class="mn-burger is-noir"><i></i><i></i></span><b>perle</b><span class="sh-bag">2</span></div>
  <div class="sh-img"><img src="assets/realisations/perle-hair.webp" alt="" width="1200" height="620" loading="lazy"><span class="sh-dots"><i class="on"></i><i></i><i></i></span><span class="sh-tag">Nouveau</span></div>
  <div class="sh-info">
    <span class="sh-cat">Collection Opaline</span>
    <b>Raw Hair Indien lisse</b>
    <div class="sh-ligne"><em>89,00 €</em><span>★★★★★ <small>(48)</small></span></div>
    <div class="sh-tailles"><span>45 cm</span><span class="on">55 cm</span><span>65 cm</span></div>
    <span class="sh-cta">Ajouter au panier</span>
  </div>
</div></div>"""


def paoli_mobile():
    return f"""<div class="phone m-phone"><div class="phone-view m-paoli">
  {statut()}
  <div class="pa-nav"><span class="pa-logo">LP</span><span class="pa-nom">Laurence Paoli<small>Avocat · Essonne</small></span><span class="mn-burger"><i></i><i></i></span></div>
  <div class="pa-hero"><span class="pa-motif"></span><div><small>Droit de la famille</small><strong>Vous accompagner dans les moments <em>qui comptent.</em></strong><span class="pa-btn">Prendre rendez vous</span></div></div>
  <div class="pa-cartes"><span><i>01</i><b>Divorce</b></span><span><i>02</i><b>Autorité parentale</b></span><span><i>03</i><b>Successions</b></span></div>
</div></div>"""


def maryes_ancien():
    return """<div class="old2">
  <div class="o2-top">☎ 06 XX XX XX XX &nbsp;|&nbsp; contact@maryes.fr</div>
  <div class="o2-head"><span class="o2-logo">Maryes <small>vidéo mariage</small></span><span class="o2-menu"><i>ACCUEIL</i><i>VIDÉOS</i><i>TARIFS</i><i>LIVRE D'OR</i><i>CONTACT</i></span></div>
  <div class="o2-slider"><img src="assets/realisations/maryes.webp" alt="" width="1200" height="620"><span class="o2-cap">Bienvenue sur le site de Maryes Vidéo !<small>Vidéaste pour votre mariage depuis 2012</small></span><span class="o2-fl l">‹</span><span class="o2-fl r">›</span></div>
  <div class="o2-cols"><div><b>Nos formules</b><p>Découvrez nos différentes formules adaptées à tous les budgets...</p><u>Lire la suite »</u></div><div><b>Dernières vidéos</b><p>Mariage de Julie et Thomas, juin 2016. Cliquez ici...</p><u>Lire la suite »</u></div><div><b>Livre d'or</b><p>« Merci pour ce magnifique film !! »</p><u>Lire la suite »</u></div></div>
  <div class="o2-foot">© 2016 Maryes Vidéo · Tous droits réservés · Site optimisé pour Internet Explorer</div>
</div>"""


def maquette_figma():
    """Étape « Maquettes » : le design Novexia ouvert dans un outil de maquettage."""
    return """<div class="step-viz sv-design" aria-hidden="true">
  <div class="dz-bar"><span class="dz-dots"><i></i><i></i><i></i></span><b>Novexia · Accueil</b><span class="dz-av"><i>A</i><i>V</i></span></div>
  <div class="dz-canvas">
    <div class="dz-frame dz-desk"><small>Desktop · 1440</small><div class="dz-site"><div class="dz-nav"><b>NOVEXIA</b><i></i></div><div class="dz-hero"><img src="assets/realisations/novexia.webp" alt="" width="1200" height="620" loading="lazy"><span><b>Construisons</b><em>votre avenir</em><u></u></span></div><div class="dz-stats"><i></i><i></i><i></i><i></i></div></div></div>
    <div class="dz-frame dz-mob"><small>Mobile</small><div class="dz-site"><div class="dz-nav"><b>NX</b></div><div class="dz-hero"><img src="assets/realisations/novexia.webp" alt="" width="1200" height="620" loading="lazy"><span><b>Construisons</b><u></u></span></div><div class="dz-stats is-2"><i></i><i></i></div></div></div>
    <div class="dz-cursor"><svg viewBox="0 0 256 256"><path fill="currentColor" d="M168,132.69,214.08,115l.33-.13A16,16,0,0,0,213,85.07L52.92,32.8A15.95,15.95,0,0,0,32.8,52.92L85.07,213a15.82,15.82,0,0,0,14.41,11l.78,0a15.84,15.84,0,0,0,14.61-9.59l.13-.33L132.69,168,184,219.31a16,16,0,0,0,22.63,0l12.68-12.68a16,16,0,0,0,0-22.63Z"/></svg><b>Vous</b></div>
    <div class="dz-comment"><i>V</i>Le bouton en orange, parfait !</div>
  </div>
</div>"""


def novexia_bureau():
    """Fenêtre de navigateur : page d'accueil Novexia redessinée (version ordinateur)."""
    return """<div class="browser nx">
  <div class="browser-bar"><i></i><i></i><i></i><span>novexia-construction.fr</span></div>
  <div class="nx-nav"><b>NOVEXIA<small>CONSTRUCTION</small></b><span>Services</span><span>Réalisations</span><span>Contact</span><i>Devis gratuit</i></div>
  <div class="nx-hero">
    <div class="nx-txt">
      <span class="nx-badge"><i></i>Plus de 15 ans d'expérience</span>
      <strong>Construisons <em>votre avenir</em>, du sol au toit.</strong>
      <p>Construction, rénovation et isolation, avec un seul interlocuteur.</p>
      <div class="nx-btns"><i class="o">Demander un devis</i><i>Nos chantiers</i></div>
      <div class="nx-confiance"><b>+500</b><small>projets réalisés</small><b>98 %</b><small>clients satisfaits</small></div>
    </div>
    <div class="nx-photo"><img src="assets/realisations/novexia.webp" alt="" width="1200" height="620" loading="lazy"><span class="nx-garantie"><b>24 h</b>délai de réponse</span></div>
  </div>
  <div class="nx-services"><span><i></i>Construction</span><span><i></i>Rénovation</span><span><i></i>Isolation</span><span><i></i>Extension</span></div>
</div>"""


def refonte_carte():
    """Carte Refonte : l'ancien site à gauche du curseur, le nouveau à droite ; le curseur suit la souris."""
    return """<div class="viz viz-ba2" data-ba-survol aria-hidden="true">
  <div class="ba2-apres"><img src="assets/realisations/maryes.webp" alt="" width="1200" height="620" loading="lazy"></div>
  <div class="ba2-avant"><div class="ba2-echelle">""" + maryes_ancien() + """</div></div>
  <span class="ba2-ligne"><i></i></span>
  <span class="lbl l">Avant</span><span class="lbl r">Après</span>
  <span class="ba2-aide">Survolez pour comparer</span>
</div>"""


def identite_carte():
    """Carte Identité : charte de marque premium (cartes de visite, dorure, palette, typographie)."""
    return """<div class="viz viz-brand" aria-hidden="true">
  <span class="vb-spec"><b>Aa</b><small>Bricolage Grotesque<br>Geist · Geist Mono</small></span>
  <div class="vb-cartes">
    <div class="vb-carte vb-noire"><span class="vb-dorure"></span><small>AUREA MEDIA</small></div>
    <div class="vb-carte vb-creme"><img src="assets/logo-aurea-media.png" alt="" width="448" height="157" loading="lazy"><span><b>Alexandre Coury</b>Fondateur<br>aurea-media.fr</span></div>
  </div>
  <div class="vb-palette"><i style="--c:#0A0A0A"></i><i style="--c:#B8934E"></i><i style="--c:#C9A961"></i><i style="--c:#E6CB8C"></i><i style="--c:#F5F5F0"></i></div>
</div>"""


def serp_google():
    """Étape Audit : page de résultats Google réaliste."""
    return """<div class="step-viz sv-google" aria-hidden="true">
  <div class="gg-tete"><b class="gg-logo"><i>G</i><i>o</i><i>o</i><i>g</i><i>l</i><i>e</i></b><span class="gg-barre">avocat droit de la famille<svg viewBox="0 0 256 256"><path fill="currentColor" d="M229.66,218.34l-50.07-50.06a88.11,88.11,0,1,0-11.31,11.31l50.06,50.07a8,8,0,0,0,11.32-11.32ZM40,112a72,72,0,1,1,72,72A72.08,72.08,0,0,1,40,112Z"/></svg></span></div>
  <div class="gg-onglets"><span class="on">Tous</span><span>Maps</span><span>Images</span><span>Actualités</span></div>
  <div class="gg-res">
    <div class="gg-site"><i>LP</i><span><b>Maître Laurence Paoli</b>avocat-paoli.fr › droit-de-la-famille</span></div>
    <a>Avocat spécialiste en droit de la famille, Essonne</a>
    <p>Divorce, autorité parentale, successions : <b>avocate spécialiste</b> en droit de la famille, inscrite au barreau de l'Essonne…</p>
    <div class="gg-liens"><span>Divorce</span><span>Honoraires</span><span>Contact</span></div>
  </div>
</div>"""


def outil_design():
    """Étape Maquettes : outil de design avec calques, canevas et propriétés."""
    return """<div class="step-viz sv-tool" aria-hidden="true">
  <div class="dt-bar"><span class="dz-dots"><i></i><i></i><i></i></span><b>Novexia · Accueil</b><span class="dz-av"><i>A</i><i>V</i></span><em>Partager</em></div>
  <div class="dt-corps">
    <div class="dt-calques"><small>Calques</small><span class="on">▾ Accueil</span><span>&nbsp;&nbsp;Hero</span><span>&nbsp;&nbsp;Services</span><span>&nbsp;&nbsp;Avis</span><span>▸ Mobile</span></div>
    <div class="dt-canevas">
      <div class="dt-frame"><div class="dt-site"><div class="dz-nav"><b>NOVEXIA</b><i></i></div><div class="dt-hero"><span><b>Construisons</b><em>votre avenir</em><u></u></span><img src="assets/realisations/novexia.webp" alt="" width="1200" height="620" loading="lazy"></div><div class="dt-cartes"><i></i><i></i><i></i></div></div><span class="dt-sel"></span></div>
      <div class="dz-cursor"><svg viewBox="0 0 256 256"><path fill="currentColor" d="M168,132.69,214.08,115l.33-.13A16,16,0,0,0,213,85.07L52.92,32.8A15.95,15.95,0,0,0,32.8,52.92L85.07,213a15.82,15.82,0,0,0,14.41,11l.78,0a15.84,15.84,0,0,0,14.61-9.59l.13-.33L132.69,168,184,219.31a16,16,0,0,0,22.63,0l12.68-12.68a16,16,0,0,0,0-22.63Z"/></svg><b>Vous</b></div>
    </div>
    <div class="dt-props"><small>Bouton</small><span class="dt-sw"><i></i>#E8661D</span><span>Rayon 8</span><span>Inter 600</span><div class="dt-com"><i>V</i>Parfait !</div></div>
  </div>
</div>"""
