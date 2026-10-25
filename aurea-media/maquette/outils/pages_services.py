"""Pages services : e-commerce, refonte, identité visuelle, motion design, maquettes de présentation."""
from composants import *
from visuels import *


def ecommerce():
    visuel = f"""<div class="vis-shop">
      {navigateur("sunrock", lazy=False, classe="vs-desk")}
      {perle_mobile().replace('class="phone m-phone"', 'class="phone m-phone vs-phone"')}
      {puce("cart", "Commande reçue", "89,00 € · Stripe", "top:-6px;right:0")}
      {puce("lightning", "Paiement en 1 clic", "Apple Pay, carte, PayPal", "bottom:40px;left:-20px", ok=True)}
      {carte_prix("1 650 €", "HT, à partir de", ["Boutique WooCommerce sur mesure", "Paiement Stripe et PayPal", "Aucune commission sur vos ventes"])}
    </div>"""
    corps = hero([("Services", "index.html#services"), ("Site e-commerce", "")], "Site e-commerce",
                 'Création de site e-commerce <span class="gold">sur mesure</span>, sans commission.',
                 "Vendez en ligne avec une boutique WooCommerce rapide, belle sur mobile et pensée pour la conversion. Vous gardez 100 % de vos ventes et la main sur votre catalogue.",
                 visuel)
    corps += avantages("Ce qui est inclus", 'Une boutique qui <span class="gold">vend</span>, pas seulement une vitrine.',
                       "Chaque étape du parcours d'achat est travaillée : de la fiche produit au paiement, jusqu'au mail de confirmation.",
                       [("bag", "Fiches produits qui convertissent", "Photos zoomables, variantes, avis, livraison et retours clairs. Tout ce qui lève les doutes avant l'achat."),
                        ("cart", "Tunnel de commande court", "Commande invité, paiement en une page, Apple Pay et Google Pay. Moins d'étapes, moins de paniers abandonnés."),
                        ("shield", "Paiement sécurisé", "Stripe et PayPal, conformes 3D Secure. L'argent arrive directement sur votre compte."),
                        ("chart", "Référencement Google Shopping", "Données produits structurées, flux Merchant Center et pages catégories optimisées."),
                        ("user", "Autonomie complète", "Ajoutez un produit, gérez le stock et les commandes depuis votre téléphone. Formation incluse.")])
    corps += galerie("Exemples", 'Des boutiques <span class="gold">en ligne</span> qui tournent.', "Défense, beauté : deux univers, deux parcours d'achat pensés pour leurs clients.", ["sunrock", "perle-hair"]).replace('<div class="gal">', '<div class="gal gal--2">')
    corps += section(entete("Comparatif", 'WooCommerce ou <span class="gold">Shopify</span> ?', "Pourquoi nous construisons vos boutiques sous WooCommerce.", centre=True) + """
<div class="rv" style="overflow-x:auto"><table class="compare">
<thead><tr><th scope="col">Critère</th><th scope="col">Shopify</th><th scope="col" class="hl">WooCommerce par Aurea</th></tr></thead>
<tbody>
<tr><td>Frais par vente</td><td class="no">0 à 2 % selon le moyen de paiement</td><td class="hl">Aucune</td></tr>
<tr><td>Abonnement mensuel</td><td class="no">Dès 36 € par mois</td><td class="hl">Hébergement seul</td></tr>
<tr><td>Design sur mesure</td><td class="no">Thème adapté</td><td class="hl"><svg><use href="#i-check"/></svg></td></tr>
<tr><td>Propriété du site et des données</td><td class="no">Plateforme fermée</td><td class="hl"><svg><use href="#i-check"/></svg></td></tr>
<tr><td>Contenus et SEO avancés</td><td class="no">Limités</td><td class="hl"><svg><use href="#i-check"/></svg></td></tr>
</tbody></table></div>""", papier=True)
    corps += methode('Votre boutique en <span class="nw"><span class="gold">6 à 8 semaines</span>.</span>', "Le délai dépend surtout de la taille du catalogue. Nous pouvons importer vos produits existants.")
    corps += faq('Tout savoir sur votre <span class="gold">boutique</span>.', [
        ("Combien coûte un site e-commerce ?", "À partir de 1 650 € HT. Le prix dépend du nombre de produits, des moyens de paiement et de livraison et des fonctionnalités (abonnements, B2B, multilingue)."),
        ("Pouvez vous reprendre ma boutique existante ?", "Oui. Nous migrons produits, clients et commandes depuis Shopify, Wix ou PrestaShop, avec des redirections pour garder votre référencement."),
        ("Quels moyens de livraison ?", "Colissimo, Mondial Relay, Chronopost, retrait en boutique ou livraison locale, avec calcul automatique des frais."),
        ("Le site sera t il rapide ?", "Oui : thème sur mesure, images optimisées et cache. Une boutique rapide vend plus, surtout sur mobile.")])
    corps += appel("Votre boutique", 'Prêt à <span class="gold">vendre</span> en ligne ?', "Parlez nous de vos produits : vous recevez une estimation et un planning sous 24h.", ecrans=("perle-hair", "sunrock", "perle-hair"))
    page("creation-site-ecommerce.html", "Création site e-commerce sur mesure | WooCommerce, Aurea",
         "Création de site e-commerce sur mesure sous WooCommerce : boutique rapide, paiement sécurisé, aucune commission sur vos ventes. Dès 1 650 € HT.", corps, "assets/realisations/sunrock.webp")


def refonte():
    ancien = maryes_ancien()
    visuel = f"""<div class="ba" data-ba>
      <div class="ba-apres">{navigateur("maryes", lazy=False)}</div>
      <div class="ba-avant"><div class="browser"><div class="browser-bar"><i></i><i></i><i></i><span>ancien-site.fr</span></div>{ancien}</div></div>
      <input type="range" min="0" max="100" value="50" aria-label="Comparer l'ancien et le nouveau site" class="ba-range">
      <span class="ba-poignee" aria-hidden="true"><i></i></span>
      <span class="ba-lbl l">Avant</span><span class="ba-lbl r">Après</span>
    </div>
    {puce("chart", "Positions conservées", "Redirections 301 testées", "bottom:-10px;right:10px", ok=True)}"""
    corps = hero([("Services", "index.html#services"), ("Refonte de site", "")], "Refonte de site internet",
                 'Refonte de site internet, <span class="gold">sans perdre</span> votre référencement.',
                 "Votre site a vieilli, il est lent ou ne génère plus de contacts ? Nous le reconstruisons avec un design actuel, une vitesse de premier ordre et une migration maîtrisée de vos positions Google.",
                 visuel, [("Demander un audit", "contact.html", True), ("Voir des refontes", "realisations.html", False)])
    corps += avantages("Notre méthode", 'Une refonte <span class="gold">sans casse</span>.', "Le plus grand risque d'une refonte, c'est de perdre son trafic. Nous le traitons avant même de dessiner.",
                       [("search", "Audit de l'existant", "Pages qui reçoivent des visites, mots clés positionnés, liens entrants : on sait ce qu'il faut protéger."),
                        ("pen", "Nouveau design sur mesure", "Une identité modernisée, plus claire, pensée pour le mobile et pour la conversion."),
                        ("refresh", "Plan de redirections", "Chaque ancienne adresse renvoie vers la bonne nouvelle page. Aucune redirection vers l'accueil par défaut."),
                        ("lightning", "Vitesse multipliée", "Thème sur mesure sans constructeur lourd, images optimisées, Core Web Vitals au vert."),
                        ("chart", "Suivi après la mise en ligne", "Search Console surveillée pendant 30 jours : erreurs corrigées, positions suivies.")])
    corps += galerie("Résultats", 'Des sites <span class="gold">transformés</span>.', "Quelques sites que nous avons repensés de fond en comble.", ["maryes", "eddy-bilou", "verisol"])
    corps += methode()
    corps += faq('Les questions sur la <span class="gold">refonte</span>.', [
        ("Vais je perdre mes positions Google ?", "Non, si la migration est préparée : audit des pages qui rankent, conservation des contenus utiles, redirections 301 page par page et suivi dans la Search Console."),
        ("Faut il tout réécrire ?", "Pas forcément. Nous gardons les contenus qui fonctionnent, réécrivons ceux qui ne convertissent pas et créons les pages manquantes."),
        ("Combien coûte une refonte ?", "Comme une création, à partir de 1 000 € HT pour un site vitrine. L'audit de départ est offert avant le devis."),
        ("Mon site reste t il en ligne pendant les travaux ?", "Oui. Le nouveau site est construit à part puis mis en ligne en une fois, sans coupure.")])
    corps += appel("Votre refonte", 'Votre site mérite <span class="gold">mieux</span>.', "Envoyez nous l'adresse de votre site : audit offert et proposition sous 24h.", "Demander mon audit")
    page("refonte-site-internet.html", "Refonte site internet sans perdre son SEO | Aurea Media",
         "Refonte de site internet : nouveau design, site plus rapide et redirections maîtrisées pour garder vos positions Google. Audit offert avant devis.", corps, "assets/realisations/maryes.webp")


def identite():
    visuel = """<div class="brand" aria-hidden="true">
      <div class="br-logo"><img src="assets/logo-mark-clair.png" alt="" width="135" height="142"><span>AUREA MEDIA<small>WEB AGENCY</small></span></div>
      <div class="br-pal"><i style="--c:#0A0A0A"><b>Encre</b>#0A0A0A</i><i style="--c:#C9A961"><b>Or</b>#C9A961</i><i style="--c:#E6CB8C"><b>Or clair</b>#E6CB8C</i><i style="--c:#F5F5F0;--t:#0A0A0A"><b>Crème</b>#F5F5F0</i></div>
      <div class="br-type"><span class="aa">Aa</span><div><b>Bricolage Grotesque</b><span>Titres · 600 à 700</span><b>Geist</b><span>Texte · 300 à 600</span></div></div>
      <div class="br-carte"><img src="assets/logo-mark-clair.png" alt="" width="135" height="142"><div><b>Alexandre Coury</b><span>Fondateur</span><span>aurea-media.fr</span></div></div>
    </div>"""
    corps = hero([("Services", "index.html#services"), ("Identité visuelle", "")], "Identité visuelle",
                 'Création d\'identité visuelle et de <span class="gold">logo</span> d\'entreprise.',
                 "Un logo, une palette, des typographies et des règles d'usage : une identité cohérente qui vous rend reconnaissable sur votre site, vos documents et vos réseaux.", visuel)
    corps += avantages("Livrables", 'Une identité <span class="gold">complète</span>, prête à l\'emploi.', "Voici, par exemple, la charte que nous avons créée pour Aurea Media.",
                       [("pen", "Logo et ses déclinaisons", "Version principale, monogramme, versions claire et foncée, favicon."),
                        ("palette", "Palette de couleurs", "Couleurs principales et secondaires avec leurs codes HEX, RVB et CMJN."),
                        ("seal", "Typographies", "Une paire de polices choisie pour le web et l'impression, avec la hiérarchie des titres."),
                        ("slides", "Charte graphique PDF", "Les règles d'usage, les marges, les interdits, les exemples d'application."),
                        ("magic", "Fichiers sources", "SVG, PNG, PDF vectoriel et fichiers modifiables, livrés et à vous.")])
    corps += section(entete("Process", 'De la première idée au <span class="gold">fichier final</span>.', "Trois pistes créatives, puis une direction affinée avec vous.") + """
<div class="steps steps--txt">
  <div class="step rv"><small>Étape 1</small><h3>Questionnaire</h3><p>Vos valeurs, votre clientèle, vos concurrents et les identités que vous aimez.</p></div>
  <div class="step rv" style="--d:.1s"><small>Étape 2</small><h3>Trois pistes</h3><p>Trois directions créatives différentes, présentées en situation.</p></div>
  <div class="step rv" style="--d:.2s"><small>Étape 3</small><h3>Affinage</h3><p>La piste choisie est travaillée jusqu'au moindre détail, allers retours inclus.</p></div>
  <div class="step rv" style="--d:.3s"><small>Étape 4</small><h3>Charte et fichiers</h3><p>Charte graphique complète et tous les fichiers sources livrés.</p></div>
</div>""", papier=True)
    corps += LOGOS
    corps += faq('Les questions sur l\'<span class="gold">identité</span>.', [
        ("Combien coûte un logo ?", "Sur devis, selon le périmètre : logo seul, logo et charte, ou identité complète avec supports. Le devis détaillé est envoyé sous 24h."),
        ("Combien de propositions ?", "Trois pistes créatives au départ, puis des allers retours sur la piste choisie jusqu'à validation."),
        ("Puis je moderniser mon logo actuel ?", "Oui. Une refonte d'identité garde ce qui fait votre reconnaissance et modernise le reste."),
        ("Les fichiers m'appartiennent ils ?", "Oui, tous les droits et fichiers sources vous sont cédés à la livraison.")])
    corps += appel("Votre identité", 'Une marque qu\'on <span class="gold">reconnaît</span>.', "Décrivez votre activité : nous revenons vers vous avec une proposition sous 24h.")
    page("logo-identite-visuelle.html", "Création identité visuelle et logo d'entreprise | Aurea",
         "Création d'identité visuelle : logo, charte graphique, typographies et couleurs déclinés sur tous vos supports. Fichiers sources livrés, devis 24h.", corps)


def motion():
    visuel = f"""<div class="player">
      <video controls muted loop playsinline preload="metadata" poster="assets/creations/motion-affiche.webp" aria-label="Film de marque Aurea Media">
        <source src="assets/creations/motion-aurea-media-1080.webm" type="video/webm"><source src="assets/creations/motion-aurea-media-1080.mp4" type="video/mp4">
      </video>
      <div class="player-strip" aria-hidden="true"><span>00:00</span><i></i><i></i><i></i><i></i><i></i><span>00:14</span></div>
    </div>
    {puce("film", "Film de marque Aurea Media", "Réalisé en interne · 14 s · 1080p", "top:-18px;left:-14px")}"""
    corps = hero([("Services", "index.html#services"), ("Motion design", "")], "Motion design",
                 'Motion design pour entreprise : des vidéos qui <span class="gold">retiennent</span> l\'attention.',
                 "Animation de logo, vidéo explicative, contenus pour vos réseaux et votre site : le mouvement fait passer votre message en quelques secondes. Ci contre, notre propre film de marque.",
                 visuel)
    corps += avantages("Formats", 'Un format pour chaque <span class="gold">usage</span>.', "Tous nos films sont livrés en MP4 et WebM, optimisés pour le web, avec une version carrée et verticale si besoin.",
                       [("play", "Animation de logo", "Votre logo prend vie en 3 à 6 secondes, pour vos vidéos, présentations et réseaux."),
                        ("film", "Vidéo explicative", "60 à 90 secondes pour expliquer votre offre simplement, avec voix off et sous titres."),
                        ("devices", "Contenus réseaux sociaux", "Formats courts verticaux pour LinkedIn, Instagram et TikTok."),
                        ("window", "Animations de site", "Vidéo de fond de hero, micro animations et illustrations animées, légères et rapides."),
                        ("slides", "Habillage de présentation", "Transitions et visuels animés pour vos pitchs et salons.")])
    corps += section(entete("Comment on travaille", 'Du script à la <span class="gold">vidéo finale</span>.', "Vous validez chaque étape avant de passer à la suivante.") + """
<div class="steps steps--txt">
  <div class="step rv"><small>Étape 1</small><h3>Script</h3><p>Le message, la durée et le ton, écrits avec vous.</p></div>
  <div class="step rv" style="--d:.1s"><small>Étape 2</small><h3>Storyboard</h3><p>Les scènes clés dessinées, pour valider le rythme.</p></div>
  <div class="step rv" style="--d:.2s"><small>Étape 3</small><h3>Animation</h3><p>Mise en mouvement, musique et sound design.</p></div>
  <div class="step rv" style="--d:.3s"><small>Étape 4</small><h3>Livraison</h3><p>Tous les formats web et réseaux, prêts à publier.</p></div>
</div>""", papier=True)
    corps += faq('Les questions sur le <span class="gold">motion design</span>.', [
        ("Combien coûte une vidéo en motion design ?", "Sur devis selon la durée et la complexité : une animation de logo coûte bien moins qu'une vidéo explicative de 90 secondes avec voix off."),
        ("Quel délai ?", "Une à deux semaines pour une animation de logo, trois à quatre pour une vidéo explicative."),
        ("La vidéo ralentira t elle mon site ?", "Non : elle est compressée, chargée seulement quand elle devient visible et mise en pause hors écran, comme sur ce site."),
        ("Puis je l'utiliser partout ?", "Oui, les droits d'utilisation vous sont cédés pour tous vos supports.")])
    corps += appel("Votre vidéo", 'Donnez du <span class="gold">mouvement</span> à votre marque.', "Expliquez nous votre besoin : proposition et planning sous 24h.")
    page("motion-design.html", "Motion design pour entreprise : vidéos animées | Aurea",
         "Motion design pour entreprise : vidéos explicatives, animation de logo et contenus pour vos réseaux sociaux et votre site. Devis gratuit sous 24h.", corps, "assets/creations/motion-affiche.webp")


def maquettes():
    visuel = """<div class="deck">
      <img class="deck-main" src="assets/creations/presentation-1.webp" alt="Couverture de la présentation commerciale Aurea Media" width="960" height="540">
      <div class="deck-strip"><img src="assets/creations/presentation-2.webp" alt="Diapositive méthode" width="960" height="540"><img src="assets/creations/presentation-3.webp" alt="Diapositive chiffres clés" width="960" height="540"><img src="assets/creations/presentation-4.webp" alt="Diapositive réalisations" width="960" height="540"><img src="assets/creations/presentation-5.webp" alt="Diapositive tarifs" width="960" height="540"></div>
    </div>"""
    corps = hero([("Services", "index.html#services"), ("Maquettes de présentation", "")], "Maquettes de présentation",
                 'Des présentations qui <span class="gold">décrochent</span> le rendez vous.',
                 "Pitch deck, proposition commerciale, présentation d'entreprise : un support clair, beau et cohérent avec votre marque. Ci contre, la présentation que nous envoyons à nos propres prospects.",
                 visuel)
    corps += section(entete("Exemple réel", 'La présentation <span class="gold">Aurea Media</span>, diapositive par diapositive.', "Cinq diapositives, une idée par écran : la promesse, la méthode, les preuves, les réalisations et l'investissement.") + """
<div class="deck-grid">
  <figure class="rv"><img src="assets/creations/presentation-1.webp" alt="Couverture" width="960" height="540" loading="lazy"><figcaption><b>01</b> La promesse</figcaption></figure>
  <figure class="rv" style="--d:.1s"><img src="assets/creations/presentation-2.webp" alt="Méthode" width="960" height="540" loading="lazy"><figcaption><b>02</b> La méthode</figcaption></figure>
  <figure class="rv" style="--d:.2s"><img src="assets/creations/presentation-3.webp" alt="Chiffres" width="960" height="540" loading="lazy"><figcaption><b>03</b> Les preuves</figcaption></figure>
  <figure class="rv"><img src="assets/creations/presentation-4.webp" alt="Réalisations" width="960" height="540" loading="lazy"><figcaption><b>04</b> Les réalisations</figcaption></figure>
  <figure class="rv" style="--d:.1s"><img src="assets/creations/presentation-5.webp" alt="Tarifs" width="960" height="540" loading="lazy"><figcaption><b>05</b> L'investissement</figcaption></figure>
</div>""")
    corps += avantages("Ce que vous recevez", 'Un support <span class="gold">prêt à présenter</span>.', "Livré sous PowerPoint, Keynote ou Google Slides, avec les modèles pour vos prochaines présentations.",
                       [("slides", "Structure et storytelling", "Une histoire claire : le problème, votre solution, les preuves, l'appel à l'action."),
                        ("pen", "Design sur mesure", "Mise en page, iconographie et graphiques cohérents avec votre identité."),
                        ("chart", "Données lisibles", "Chiffres et graphiques retravaillés pour être compris en trois secondes."),
                        ("magic", "Modèles réutilisables", "Des gabarits de diapositives pour créer vos prochaines présentations seul.")])
    corps += faq('Les questions sur les <span class="gold">présentations</span>.', [
        ("Quel délai ?", "Environ une semaine pour une présentation de 10 à 20 diapositives, selon le contenu fourni."),
        ("Quels formats ?", "PowerPoint, Keynote, Google Slides ou PDF, au choix, avec les polices et images incluses."),
        ("Pouvez vous partir de mon document actuel ?", "Oui, c'est le cas le plus courant : nous restructurons et redessinons votre support existant."),
        ("Combien ça coûte ?", "Sur devis selon le nombre de diapositives et la part de rédaction.")])
    corps += appel("Votre présentation", 'Votre prochain rendez vous <span class="gold">commence ici</span>.', "Envoyez nous votre support actuel : proposition sous 24h.")
    page("maquettes-presentation.html", "Création de présentation et pitch deck sur mesure | Aurea",
         "Création de maquettes de présentation : pitch decks, propositions commerciales et supports de vente sur mesure. Fichiers modifiables livrés.", corps, "assets/creations/presentation-1.webp")


if __name__ == "__main__":
    ecommerce(); refonte(); identite(); motion(); maquettes()
