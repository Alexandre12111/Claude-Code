"""Visuels dessinés en HTML et CSS (téléphones, mini sites), réutilisés sur toutes les pages."""


def verrouillage(notifs, date="mardi 14 octobre", heure="09:41"):
    """Écran de verrouillage avec notifications. notifs : [(appli, titre, texte, quand)]."""
    n = "".join(f'<div class="notif" style="--i:{i}"><span class="notif-app{"" if a == "A" else " is-cal"}">{"A" if a == "A" else '<svg><use href="#i-calendar"/></svg>'}</span><div><p><b>{"Aurea · Site web" if a == "A" else "Agenda"}</b><em>{q}</em></p><strong>{t}</strong><span>{x}</span></div></div>' for i, (a, t, x, q) in enumerate(notifs))
    return f'<div class="lock"><div class="lock-statut"><span>{heure}</span><span class="lock-ico"><i></i><i></i><i></i></span></div><div class="lock-heure"><small>{date}</small><b>{heure}</b></div><div class="lock-notifs">{n}</div><div class="lock-bas"><i></i></div></div>'


DEMANDES = [("A", "Nouvelle demande de devis", "Cabinet Martin · site vitrine, budget validé", "maintenant"),
            ("A", "Demande de rappel", "BTP Rénov 92 · « Pouvez vous me rappeler ? »", "il y a 12 min"),
            ("C", "Rendez vous confirmé", "Visio découverte · jeudi 10:00", "il y a 1 h")]


def statut():
    return '<div class="m-statut"><span>9:41</span><span class="lock-ico"><i></i><i></i><i></i></span></div>'


def novexia_mobile():
    return f"""<div class="phone m-phone"><div class="phone-view m-novexia">
  {statut()}
  <div class="mn-nav"><b>NOVEXIA<small>CONSTRUCTION</small></b><span class="mn-burger"><i></i><i></i></span></div>
  <div class="mn-hero"><img src="assets/realisations/novexia.webp" alt="" width="1200" height="620" loading="lazy"><div><small>Construction · Rénovation</small><strong>Construisons <em>votre avenir</em></strong><span class="mn-btn o">Devis gratuit</span><span class="mn-btn">Nous appeler</span></div></div>
  <div class="mn-note"><span>★★★★★</span><b>4,9</b><em>127 avis</em></div>
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
  <div class="pa-nav"><span class="pa-logo">LP</span><span class="mn-burger"><i></i><i></i></span></div>
  <div class="pa-hero"><img src="assets/realisations/laurence-paoli.webp" alt="" width="1200" height="620" loading="lazy"><div><small>Maître Laurence Paoli Culioli</small><strong>Avocat spécialiste en droit de la famille</strong><span class="pa-btn">Me contacter</span></div></div>
  <div class="pa-cartes"><span><b>Divorce</b>Amiable ou contentieux</span><span><b>Successions</b>Partage, héritage</span><span><b>Médiation</b>Familiale</span></div>
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
