"""Visuels dessinés en HTML et CSS (téléphones, mini sites), réutilisés sur toutes les pages."""


def verrouillage(notifs, date="mardi 14 octobre", heure="09:41"):
    """Écran de verrouillage avec notifications. notifs : [(appli, titre, texte, quand)]."""
    n = "".join(f'<div class="notif" style="--i:{i}"><span class="notif-app{"" if a == "A" else " is-cal"}">{"A" if a == "A" else '<svg><use href="#i-calendar"/></svg>'}</span><div><p><b>{"Aurea" if a == "A" else "Agenda"}</b><em>{q}</em></p><strong>{t}</strong><span>{x}</span></div></div>' for i, (a, t, x, q) in enumerate(notifs))
    return f'<div class="lock"><div class="lock-statut"><span>{heure}</span><span class="lock-ico"><i></i><i></i><i></i></span></div><div class="lock-heure"><small>{date}</small><b>{heure}</b></div><div class="lock-notifs">{n}</div><div class="lock-bas"><i></i></div></div>'


DEMANDES = [("A", "Nouveau devis reçu", "Formulaire du site · site vitrine", "maintenant"),
            ("A", "Rappel demandé", "« Rappelez moi en fin de journée »", "12 min"),
            ("C", "RDV confirmé", "Visio découverte · jeudi 10:00", "1 h")]


def statut():
    return '<div class="m-statut"><span>9:41</span><span class="lock-ico"><i></i><i></i><i></i></span></div>'


def telephone(cle, classe="", alt=""):
    """Téléphone avec la vraie capture mobile du site (780 × 1688, format exact de l'écran)."""
    c = f" {classe}" if classe else ""
    return f'<div class="phone{c}"><div class="phone-view"><img src="assets/realisations/{cle}-mobile.webp" alt="{alt}" width="780" height="1688" loading="lazy" decoding="async"></div></div>'


def fenetre(cle, domaine, classe="", alt=""):
    """Fenêtre de navigateur avec la vraie capture ordinateur du site (1600 × 1000)."""
    c = f" {classe}" if classe else ""
    return f'<div class="browser{c}"><div class="browser-bar"><i></i><i></i><i></i><span>{domaine}</span></div><img src="assets/realisations/{cle}.webp" alt="{alt}" width="1600" height="1000" loading="lazy" decoding="async"></div>'


def novexia_mobile():
    return telephone("novexia", "m-phone", "Site Novexia Construction sur mobile")


def perle_mobile():
    return telephone("perle-hair", "m-phone", "Boutique Perle Hair sur mobile")


def paoli_mobile():
    return telephone("laurence-paoli", "m-phone", "Site de Maître Laurence Paoli sur mobile")


def maryes_ancien():
    """Un site daté générique (aucun client réel) pour illustrer l'avant d'une refonte."""
    return """<div class="old2">
  <div class="o2-top">☎ 01 XX XX XX XX &nbsp;|&nbsp; contact@votre-entreprise.fr</div>
  <div class="o2-head"><span class="o2-logo">Votre entreprise <small>depuis toujours</small></span><span class="o2-menu"><i>ACCUEIL</i><i>SERVICES</i><i>PHOTOS</i><i>LIVRE D'OR</i><i>CONTACT</i></span></div>
  <div class="o2-slider"><span class="o2-vide"></span><span class="o2-cap">Bienvenue sur notre site internet !<small>Cliquez sur les flèches pour voir nos photos</small></span><span class="o2-fl l">‹</span><span class="o2-fl r">›</span></div>
  <div class="o2-cols"><div><b>Nos services</b><p>Découvrez nos différentes prestations adaptées à tous les besoins...</p><u>Lire la suite »</u></div><div><b>Actualités</b><p>Notre site fait peau neuve, revenez bientôt...</p><u>Lire la suite »</u></div><div><b>Livre d'or</b><p>Laissez nous un message !</p><u>Lire la suite »</u></div></div>
  <div class="o2-foot">© Votre entreprise · Tous droits réservés · Site optimisé pour Internet Explorer</div>
</div>"""


def maquette_figma():
    """Étape « Maquettes » : le design Novexia ouvert dans un outil de maquettage."""
    return """<div class="step-viz sv-design" aria-hidden="true">
  <div class="dz-bar"><span class="dz-dots"><i></i><i></i><i></i></span><b>Novexia · Accueil</b><span class="dz-av"><i>A</i><i>V</i></span></div>
  <div class="dz-canvas">
    <div class="dz-frame dz-desk"><small>Desktop · 1440</small><div class="dz-site"><div class="dz-nav"><b>NOVEXIA</b><i></i></div><div class="dz-hero"><img src="assets/realisations/novexia.webp" alt="" width="1600" height="1000" loading="lazy"><span><b>Construisons</b><em>votre avenir</em><u></u></span></div><div class="dz-stats"><i></i><i></i><i></i><i></i></div></div></div>
    <div class="dz-frame dz-mob"><small>Mobile</small><div class="dz-site"><div class="dz-nav"><b>NX</b></div><div class="dz-hero"><img src="assets/realisations/novexia.webp" alt="" width="1600" height="1000" loading="lazy"><span><b>Construisons</b><u></u></span></div><div class="dz-stats is-2"><i></i><i></i></div></div></div>
    <div class="dz-cursor"><svg viewBox="0 0 256 256"><path fill="currentColor" d="M168,132.69,214.08,115l.33-.13A16,16,0,0,0,213,85.07L52.92,32.8A15.95,15.95,0,0,0,32.8,52.92L85.07,213a15.82,15.82,0,0,0,14.41,11l.78,0a15.84,15.84,0,0,0,14.61-9.59l.13-.33L132.69,168,184,219.31a16,16,0,0,0,22.63,0l12.68-12.68a16,16,0,0,0,0-22.63Z"/></svg><b>Vous</b></div>
    <div class="dz-comment"><i>V</i>Le bouton en orange, parfait !</div>
  </div>
</div>"""


def novexia_bureau():
    """Fenêtre de navigateur : la vraie page d'accueil de Novexia Construction."""
    return fenetre("novexia", "novexia-construction.fr", "nx", "Site Novexia Construction sur ordinateur")


def refonte_carte():
    """Carte Refonte : l'ancien site à gauche du curseur, le nouveau à droite ; le curseur suit la souris."""
    return """<div class="viz viz-ba2" data-ba-survol aria-hidden="true">
  <div class="ba2-apres"><img src="assets/realisations/maryes.webp" alt="" width="1600" height="1000" loading="lazy"></div>
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
      <div class="dt-frame"><div class="dt-site"><div class="dz-nav"><b>NOVEXIA</b><i></i></div><div class="dt-hero"><span><b>Construisons</b><em>votre avenir</em><u></u></span><img src="assets/realisations/novexia.webp" alt="" width="1600" height="1000" loading="lazy"></div><div class="dt-cartes"><i></i><i></i><i></i></div></div><span class="dt-sel"></span></div>
      <div class="dz-cursor"><svg viewBox="0 0 256 256"><path fill="currentColor" d="M168,132.69,214.08,115l.33-.13A16,16,0,0,0,213,85.07L52.92,32.8A15.95,15.95,0,0,0,32.8,52.92L85.07,213a15.82,15.82,0,0,0,14.41,11l.78,0a15.84,15.84,0,0,0,14.61-9.59l.13-.33L132.69,168,184,219.31a16,16,0,0,0,22.63,0l12.68-12.68a16,16,0,0,0,0-22.63Z"/></svg><b>Vous</b></div>
    </div>
    <div class="dt-props"><small>Bouton</small><span class="dt-sw"><i></i>#E8661D</span><span>Rayon 8</span><span>Inter 600</span><div class="dt-com"><i>V</i>Parfait !</div></div>
  </div>
</div>"""
