"""Pages secteur (santé, architecte, restaurant, artisan BTP, coach consultant) et page locale Hauts de Seine.

Même gabarit que la page avocat. Les références citées sont de vrais projets d'Aurea Media (liste REAL) :
aucun chiffre ni témoignage n'est inventé.
"""
from composants import *

# fichier : réglages de la page
PAGES = {
    "site-internet-professions-sante.html": dict(
        nom="Professions de santé", ariane="Santé", ref="xpert-medical",
        titre="Site internet professionnel de santé : médecin, kiné | Aurea",
        desc="Création de site internet pour médecin, kiné et centre de santé : prise de rendez vous, informations pratiques claires et respect des règles déontologiques.",
        surtitre="Site internet professionnel de santé",
        h1='Un site internet pour les <span class="gold">professionnels de santé</span>.',
        chapo="Médecins, kinésithérapeutes, ostéopathes, psychologues ou centres de soins : un site clair qui répond aux questions de vos patients, facilite la prise de rendez vous et respecte les règles de votre profession. Ci contre, le site du réseau de centres Xpert Medical.",
        puces=[("calendar", "Rendez vous pris", "Consultation · mardi 14:30", "top:-14px;left:-10px"), ("pin", "Cabinet trouvé", "Itinéraire et horaires", "bottom:6px;right:0")],
        av_surtitre="Pensé pour les patients",
        av_h2='Ce qu\'un patient cherche sur le site de son <span class="gold">praticien</span>.',
        av_chapo="Un patient veut savoir en quelques secondes si vous traitez son problème, quand vous êtes disponible et comment venir. Le reste vient ensuite.",
        avantages=[("calendar", "Prise de rendez vous", "Lien vers Doctolib ou votre outil de réservation, visible sur chaque page et sur mobile."),
                   ("pin", "Informations pratiques", "Adresse, accès, stationnement, horaires et accessibilité aux personnes à mobilité réduite."),
                   ("user", "Présentation des praticiens", "Parcours, spécialités et langues parlées, pour que le patient choisisse en confiance."),
                   ("shield", "Règles déontologiques", "Ton informatif, mentions obligatoires et aucun contenu promotionnel interdit par l'Ordre."),
                   ("search", "Référencement local", "Fiche Google Business Profile et pages par spécialité pour être trouvé près du cabinet.")],
        gal_h2='Des sites qui donnent envie de <span class="gold">prendre rendez vous</span>.',
        gal_chapo="Un centre médical, une profession réglementée et un salon avec réservation en ligne : trois parcours pensés pour la prise de rendez vous.",
        galerie=["xpert-medical", "laurence-paoli", "barber-korner"],
        faq_h2='Les questions des <span class="gold">praticiens</span>.',
        faq=[("Un médecin peut il avoir un site internet ?", "Oui. Depuis 2020, le code de déontologie médicale autorise les médecins à communiquer des informations sur leur activité, à condition qu'elles soient loyales, honnêtes et sans caractère commercial. Le site est rédigé en ce sens."),
             ("Peut on intégrer Doctolib au site ?", "Oui, avec un bouton de prise de rendez vous sur chaque page, ou un module intégré si votre outil le permet."),
             ("Combien coûte le site d'un cabinet ?", "À partir de 1 000 € HT pour un site vitrine sur mesure ; plus pour un centre avec plusieurs praticiens et plusieurs adresses."),
             ("Combien de temps pour être en ligne ?", "Environ 4 semaines, rédaction des pages de spécialités comprise.")],
        appel_s="Votre cabinet", appel_h2='Un site aussi rassurant que votre <span class="gold">cabinet</span>.', appel_c="Présentez nous votre activité : proposition détaillée sous 24h."),

    "site-internet-architecte.html": dict(
        nom="Architectes", ariane="Architectes", ref="portugal-expert",
        titre="Site internet architecte : portfolio sur mesure | Aurea",
        desc="Création de site internet pour architecte et architecte d'intérieur : portfolio de projets mis en scène, pages rapides malgré les grandes images, demandes qualifiées.",
        surtitre="Site internet pour architecte",
        h1='Un site d\'architecte qui met vos <span class="gold">projets</span> en scène.',
        chapo="Architectes, architectes d'intérieur et promoteurs : un portfolio aussi soigné que vos réalisations, qui se charge vite malgré les grandes photos et donne envie de vous confier un projet. Ci contre, le site de Portugal Expert Properties.",
        puces=[("mail", "Nouveau projet", "Rénovation d'une maison, 180 m²", "top:-14px;left:-10px"), ("lightning", "Photos en haute qualité", "chargées en moins d'une seconde", "bottom:6px;right:0")],
        av_surtitre="Pensé pour les créatifs",
        av_h2='Un portfolio qui vend votre <span class="gold">regard</span>.',
        av_chapo="Vos photos font le travail, à condition d'être bien mises en page et de s'afficher rapidement, y compris sur téléphone.",
        avantages=[("palette", "Portfolio mis en scène", "Chaque projet raconté comme un article : contexte, parti pris, matériaux, photos avant et après."),
                   ("lightning", "Images rapides", "Photos converties en WebP, tailles adaptées à chaque écran, chargement différé."),
                   ("seal", "Filtres par type de projet", "Résidentiel, tertiaire, rénovation, intérieur : le visiteur trouve ce qui le concerne."),
                   ("mail", "Demandes qualifiées", "Formulaire qui précise la surface, le budget et le calendrier avant le premier appel."),
                   ("search", "Référencement par projet", "Chaque projet est une page indexable, avec ses mots clés et sa localisation.")],
        gal_h2='Des sites qui laissent parler les <span class="gold">images</span>.',
        gal_chapo="Immobilier de prestige, lieu de charme et galerie photo : trois sites où l'image porte le message.",
        galerie=["portugal-expert", "villa-moniris", "eddy-bilou"],
        faq_h2='Les questions des <span class="gold">architectes</span>.',
        faq=[("Mes photos sont très lourdes, est ce un problème ?", "Non : elles sont converties et redimensionnées automatiquement à l'import. Vous gardez une qualité d'affichage parfaite et des pages rapides."),
             ("Pourrai je ajouter mes projets moi même ?", "Oui, chaque projet s'ajoute depuis l'administration avec ses photos, son texte et sa catégorie, sans toucher à la mise en page."),
             ("Combien coûte un site d'architecte ?", "À partir de 1 000 € HT pour un site vitrine sur mesure ; plus selon le nombre de projets à mettre en page au départ."),
             ("Le site peut il être en anglais ?", "Oui, en option, avec des adresses et un référencement propres à chaque langue.")],
        appel_s="Votre agence", appel_h2='Un site à la hauteur de vos <span class="gold">réalisations</span>.', appel_c="Envoyez nous quelques projets : proposition détaillée sous 24h."),

    "site-internet-restaurant.html": dict(
        nom="Restaurants et lieux", ariane="Restaurants", ref="villa-moniris",
        titre="Création site internet restaurant avec réservation | Aurea",
        desc="Création de site internet pour restaurant, hôtel et lieu de réception : carte à jour, réservation en ligne, privatisation et avis Google mis en avant.",
        surtitre="Site internet pour restaurant",
        h1='Un site de restaurant qui remplit vos <span class="gold">tables</span>.',
        chapo="Restaurants, hôtels, gîtes et lieux de réception : un site qui donne faim ou envie de venir, avec la carte toujours à jour, la réservation en un geste et les informations pratiques à portée de pouce. Ci contre, le site de Villa Moniris.",
        puces=[("calendar", "Réservation confirmée", "4 personnes · samedi 20:30", "top:-14px;left:-10px"), ("star", "Avis Google", "affichés sur le site", "bottom:6px;right:0")],
        av_surtitre="Pensé pour l'hôtellerie et la restauration",
        av_h2='Ce que vos clients cherchent <span class="gold">avant de venir</span>.',
        av_chapo="La carte, les horaires, l'adresse et un moyen de réserver : tout le reste doit les y conduire, surtout sur téléphone.",
        avantages=[("calendar", "Réservation en ligne", "Votre outil de réservation intégré (TheFork, Zenchef, Booking ou formulaire), visible partout."),
                   ("slides", "Carte modifiable", "Vous changez les plats, les prix et le menu du jour vous même, en quelques secondes."),
                   ("star", "Avis mis en avant", "Vos avis Google affichés sur le site et mis à jour automatiquement."),
                   ("pin", "Infos pratiques", "Horaires, accès, parking et téléphone cliquable, en tête de page sur mobile."),
                   ("seal", "Privatisation et événements", "Une page dédiée aux groupes, séminaires et réceptions, avec formulaire de demande.")],
        gal_h2='Des lieux qui donnent <span class="gold">envie de venir</span>.',
        gal_chapo="Propriété de charme, cercle privé et domaine de réception : trois sites pensés pour la réservation.",
        galerie=["villa-moniris", "cercle-la-raison", "duchentyls"],
        faq_h2='Les questions des <span class="gold">restaurateurs</span>.',
        faq=[("Pourrai je modifier la carte moi même ?", "Oui, la carte se modifie depuis l'administration comme un simple document. Le menu du jour peut être mis à jour depuis votre téléphone."),
             ("Peut on garder notre outil de réservation ?", "Oui. Nous intégrons l'outil que vous utilisez déjà, pour ne pas changer vos habitudes en salle."),
             ("Combien coûte le site d'un restaurant ?", "À partir de 1 000 € HT pour un site vitrine sur mesure ; plus avec la vente en ligne (click and collect, bons cadeaux)."),
             ("Peut on vendre des bons cadeaux en ligne ?", "Oui, avec une petite boutique WooCommerce et un paiement par carte, Apple Pay ou Google Pay.")],
        appel_s="Votre établissement", appel_h2='Un site qui donne envie de <span class="gold">réserver</span>.', appel_c="Parlez nous de votre lieu : proposition détaillée sous 24h."),

    "site-internet-artisan-btp.html": dict(
        nom="Artisans et BTP", ariane="Artisans et BTP", ref="novexia",
        titre="Site internet artisan et BTP : plus de devis | Aurea",
        desc="Création de site internet pour artisan, entreprise du bâtiment et industrie : chantiers photographiés, zone d'intervention claire et demandes de devis qualifiées.",
        surtitre="Site internet pour artisan et BTP",
        h1='Un site d\'artisan qui vous apporte des <span class="gold">chantiers</span>.',
        chapo="Plombiers, électriciens, couvreurs, entreprises de rénovation ou de travaux industriels : un site qui montre vos chantiers, rassure sur votre sérieux et transforme les visites en demandes de devis. Ci contre, le site de Novexia Construction.",
        puces=[("calendar", "Demande de devis", "Rénovation salle de bain, 92", "top:-14px;left:-10px"), ("phone", "Appel depuis le site", "bouton visible sur mobile", "bottom:6px;right:0")],
        av_surtitre="Pensé pour les métiers du bâtiment",
        av_h2='Ce qu\'un client vérifie avant de vous <span class="gold">appeler</span>.',
        av_chapo="Vos réalisations, votre zone d'intervention, vos assurances et la facilité à vous joindre : un site d'artisan doit répondre à ces quatre questions.",
        avantages=[("phone", "Appel en un geste", "Bouton d'appel et de devis toujours visible sur téléphone, là où vos clients vous cherchent."),
                   ("palette", "Chantiers en photos", "Galeries avant et après, ajoutées depuis votre téléphone à la fin de chaque chantier."),
                   ("pin", "Zone d'intervention", "Villes et départements couverts, avec une page par secteur quand la stratégie le justifie."),
                   ("shield", "Gages de sérieux", "Assurance décennale, labels RGE ou Qualibat, avis clients et années d'expérience."),
                   ("mail", "Devis qualifiés", "Formulaire qui demande le type de travaux, la surface et les photos du chantier.")],
        gal_h2='Des sites qui inspirent <span class="gold">confiance</span>.',
        gal_chapo="Construction et rénovation, isolation industrielle, travaux de soudure et de tuyauterie : trois entreprises de terrain.",
        galerie=["novexia", "normande-isolation", "tcm-grand-est"],
        faq_h2='Les questions des <span class="gold">artisans</span>.',
        faq=[("Je n'ai pas de photos de mes chantiers, est ce bloquant ?", "Non. Nous démarrons avec ce que vous avez et nous vous montrons comment photographier vos prochains chantiers avec votre téléphone."),
             ("Pourrai je ajouter mes réalisations moi même ?", "Oui, depuis votre téléphone ou votre ordinateur, en quelques minutes à la fin de chaque chantier."),
             ("Combien coûte le site d'un artisan ?", "À partir de 1 000 € HT pour un site vitrine sur mesure ; plus avec des pages par ville ou par type de travaux."),
             ("Le site m'aidera t il à être trouvé localement ?", "Oui : fiche Google Business Profile optimisée, pages par zone d'intervention et données structurées de votre entreprise.")],
        appel_s="Votre entreprise", appel_h2='Plus de demandes de <span class="gold">devis</span>, moins de démarchage.', appel_c="Présentez nous votre activité : proposition détaillée sous 24h."),

    "site-internet-coach-consultant.html": dict(
        nom="Coachs et consultants", ariane="Coachs et consultants", ref="sultan",
        titre="Création site internet coach et consultant | Aurea Media",
        desc="Création de site internet pour coach, consultant et formateur : expertise mise en valeur, offres claires, prise de rendez vous et articles qui attirent vos clients.",
        surtitre="Site internet pour coach et consultant",
        h1='Un site de consultant qui fait <span class="gold">autorité</span>.',
        chapo="Coachs, consultants, formateurs et cabinets de conseil : un site qui pose votre expertise, rend vos offres lisibles et transforme la lecture d'un article en prise de rendez vous. Ci contre, le site de Sultan Conseil & Leviers.",
        puces=[("calendar", "Appel découverte", "Réservé · jeudi 11:00", "top:-14px;left:-10px"), ("search", "Article lu", "trouvé sur Google", "bottom:6px;right:0")],
        av_surtitre="Pensé pour les métiers du conseil",
        av_h2='Votre expertise, rendue <span class="gold">visible</span>.',
        av_chapo="On ne choisit pas un consultant sur un prix, mais sur la confiance : votre site doit montrer comment vous pensez avant le premier échange.",
        avantages=[("seal", "Positionnement clair", "Pour qui, sur quels sujets, avec quelle méthode : votre promesse comprise en quelques secondes."),
                   ("slides", "Offres lisibles", "Accompagnements, formats et tarifs ou fourchettes, présentés sans jargon."),
                   ("calendar", "Rendez vous en ligne", "Appel découverte réservé directement dans votre agenda (Calendly, Google Agenda)."),
                   ("pen", "Articles et publications", "Un blog simple à alimenter, pour être trouvé sur vos sujets et cité par les moteurs IA."),
                   ("user", "Preuves et références", "Clients accompagnés, interventions, publications et avis, avec leur accord.")],
        gal_h2='Des sites qui installent une <span class="gold">expertise</span>.',
        gal_chapo="Cabinet de conseil international, coaching de dirigeants et think tank : trois façons de faire autorité.",
        galerie=["sultan", "enzym", "inas"],
        faq_h2='Les questions des <span class="gold">consultants</span>.',
        faq=[("Faut il afficher ses tarifs ?", "Ce n'est pas obligatoire, mais une fourchette ou un tarif de départ qualifie les demandes et rassure. Nous en discutons selon votre positionnement."),
             ("Pourrai je publier des articles moi même ?", "Oui, l'éditeur est simple et chaque article est préparé pour le référencement : titre, description, sommaire et auteur."),
             ("Combien coûte le site d'un consultant ?", "À partir de 1 000 € HT pour un site vitrine sur mesure ; plus avec un espace client ou la vente de formations."),
             ("Le site peut il être bilingue ?", "Oui, en option, avec une version anglaise complète et un référencement propre à chaque langue.")],
        appel_s="Votre activité", appel_h2='Un site qui parle aussi bien que <span class="gold">vous</span>.', appel_c="Présentez nous votre activité : proposition détaillée sous 24h."),
}


def secteur(fichier, c):
    visuel = f"""<div class="vis-secteur">
      {navigateur(c["ref"], lazy=False)}
      {"".join(puce(*p) for p in c["puces"])}
    </div>"""
    corps = hero([("Secteurs", "creation-site-vitrine.html"), (c["ariane"], "")], c["surtitre"], c["h1"], c["chapo"], visuel,
                 [("Obtenir mon devis", "contact.html", True), ("Voir les tarifs", "tarifs.html", False)])
    corps += avantages(c["av_surtitre"], c["av_h2"], c["av_chapo"], c["avantages"])
    corps += galerie("Références", c["gal_h2"], c["gal_chapo"], c["galerie"])
    corps += autres_secteurs(fichier)
    corps += faq(c["faq_h2"], c["faq"])
    corps += appel(c["appel_s"], c["appel_h2"], c["appel_c"])
    page(fichier, c["titre"], c["desc"], corps)


def hauts_de_seine():
    villes = ["Boulogne-Billancourt", "Issy-les-Moulineaux", "Neuilly-sur-Seine", "Levallois-Perret", "Clamart", "Nanterre", "Rueil-Malmaison", "Courbevoie", "Puteaux", "Montrouge", "Sèvres", "Saint-Cloud"]
    visuel = f"""<div class="vis-secteur vis-92">
      <div class="villes-92" aria-hidden="true">{"".join(f'<span style="--i:{i}">{v}</span>' for i, v in enumerate(villes))}</div>
      {puce("pin", "Rendez vous sur place", "partout dans le 92", "top:-14px;left:-10px", ok=True)}
      {puce("clock", "Devis sous 24h", "sans engagement", "bottom:6px;right:0")}
    </div>"""
    corps = hero([("Agence web Hauts de Seine", "")], "Agence web dans les Hauts de Seine",
                 'Agence web pour les entreprises des <span class="gold">Hauts de Seine</span>.',
                 "Basée à Paris, à quelques minutes de Boulogne, Neuilly ou Issy, Aurea Media crée des sites vitrines et e-commerce sur mesure pour les indépendants, TPE et PME du 92. Rendez vous chez vous, à Paris ou en visio, et un devis détaillé sous 24h.",
                 visuel)
    corps += LOGOS
    corps += avantages("Pourquoi travailler avec nous", 'Un studio proche, un <span class="gold">interlocuteur unique</span>.', "Vous travaillez directement avec Alexandre Coury, de l'audit à la mise en ligne, sans commercial ni sous traitance.",
                       [("pin", "Rendez vous dans le 92", "Dans vos locaux à Boulogne, Issy, Neuilly, Clamart ou ailleurs dans le département, ou en visio."),
                        ("search", "Référencement local", "Fiche Google Business Profile, pages par ville et données structurées pour être trouvé près de chez vous."),
                        ("clock", "Délais tenus", "Un site vitrine en ligne en 4 semaines, planning validé dès le départ."),
                        ("shield", "Vous restez propriétaire", "Site, contenus, nom de domaine et accès à votre nom, sans abonnement imposé.")])
    corps += galerie("Réalisations", 'Des projets en <span class="gold">Île de France</span> et au delà.', "Entreprises franciliennes, institutions parisiennes et clients partout en France et en Europe.", ["barber-korner", "inas", "massy"])
    corps += section(entete("Avis clients", 'Ils en parlent <span class="gold">mieux</span> que nous.') + "\n" + AVIS)
    corps += faq('Les questions sur l\'agence dans les <span class="gold">Hauts de Seine</span>.', [
        ("Vous déplacez vous dans les Hauts de Seine ?", "Oui, pour le rendez vous de lancement et la présentation des maquettes si vous le souhaitez. Le reste du projet se fait à distance, avec des points réguliers en visio."),
        ("Intervenez vous à Boulogne-Billancourt et à Clamart ?", "Oui, comme dans tout le département : Boulogne-Billancourt, Clamart, Issy-les-Moulineaux, Neuilly-sur-Seine, Levallois-Perret, Nanterre et les autres communes du 92."),
        ("Vos tarifs sont ils différents en Île de France ?", "Non, nos tarifs sont les mêmes partout : site vitrine dès 1 000 € HT, e-commerce dès 1 650 € HT."),
        ("Pouvez vous améliorer notre visibilité locale ?", "Oui : optimisation de la fiche Google Business Profile, pages par ville quand elles ont un vrai contenu, et suivi dans la Search Console.")])
    corps += appel("Votre projet dans le 92", 'On se <span class="gold">rencontre</span> ?', "Proposez un créneau : rendez vous dans vos locaux, à Paris ou en visio, devis sous 24h.", "Prendre rendez vous")
    page("agence-web-hauts-de-seine.html", "Agence web Hauts de Seine (92) : création de site | Aurea",
         "Agence web pour les entreprises des Hauts de Seine : sites vitrines et e-commerce sur mesure, rendez vous à Boulogne, Issy, Neuilly ou en visio. Devis sous 24h.", corps)


def page_texte(fichier, ariane, titre_page, chapo, contenu, titre, desc):
    """Page de texte simple (mentions légales, confidentialité, plan du site)."""
    corps = f"""<header class="phero phero--court">
  <div class="gridbg" aria-hidden="true"></div><div class="orb orb--a" aria-hidden="true"></div>
  <div class="wrap wrap--etroit">
    {fil((ariane, ""))}
    <h1 class="split-words">{titre_page}</h1>
    <p class="lead rv" style="margin-top:22px">{chapo}</p>
  </div>
</header>
<div class="sec paper article-zone">
  <div class="wrap wrap--etroit"><div class="prose">{contenu}</div></div>
</div>"""
    page(fichier, titre, desc, corps)


# Champs à vérifier par Alexandre avant la mise en ligne : entre crochets
MENTIONS = """<h2>Éditeur du site</h2>
<p>Le site aurea-media.fr est édité par Aurea Media, [forme juridique à compléter], immatriculée sous le numéro SIREN 992 693 473, dont le siège est situé 60 rue François Ier, 75008 Paris.</p>
<p>Téléphone : <a href="tel:+33651287761">06 51 28 77 61</a><br>E-mail : <a href="mailto:alexandre.coury@aurea-media.fr">alexandre.coury@aurea-media.fr</a></p>
<h2>Directeur de la publication</h2>
<p>Alexandre Coury, fondateur d'Aurea Media.</p>
<h2>Hébergement</h2>
<p>[Nom de l'hébergeur], [adresse postale], [téléphone].</p>
<h2>Propriété intellectuelle</h2>
<p>Les textes, visuels, vidéos et le code du site sont la propriété d'Aurea Media, sauf mention contraire. Les captures des sites réalisés sont présentées avec l'accord des clients concernés et restent la propriété de leurs titulaires. Toute reproduction sans autorisation écrite est interdite.</p>
<h2>Icônes et typographies</h2>
<p>Icônes Phosphor (licence MIT). Typographies Bricolage Grotesque, Geist et Geist Mono (licence SIL Open Font License).</p>
<h2>Données personnelles</h2>
<p>Le traitement des données transmises par le formulaire de contact est décrit dans la <a href="politique-confidentialite.html">politique de confidentialité</a>.</p>"""

CONFIDENTIALITE = """<h2>Responsable du traitement</h2>
<p>Aurea Media, 60 rue François Ier, 75008 Paris, représentée par Alexandre Coury. Contact : <a href="mailto:alexandre.coury@aurea-media.fr">alexandre.coury@aurea-media.fr</a>.</p>
<h2>Données collectées</h2>
<p>Le formulaire de contact recueille votre nom, votre adresse e-mail, votre téléphone si vous le donnez, le type de projet et votre message. Aucune autre donnée n'est demandée.</p>
<h2>Finalité et base légale</h2>
<p>Ces données servent uniquement à répondre à votre demande et à vous adresser un devis. Le traitement repose sur des mesures précontractuelles prises à votre demande (article 6.1.b du RGPD).</p>
<h2>Durée de conservation</h2>
<p>Les demandes sans suite sont conservées trois ans à compter du dernier échange, puis supprimées. Les données des clients sont conservées pendant la durée de la relation commerciale, puis selon les obligations légales de conservation des pièces comptables.</p>
<h2>Destinataires</h2>
<p>Vos données sont lues par Alexandre Coury uniquement. Elles ne sont ni vendues, ni cédées, ni utilisées pour de la prospection par des tiers. Elles sont stockées chez l'hébergeur du site, [nom de l'hébergeur], dans l'Union européenne.</p>
<h2>Vos droits</h2>
<p>Vous pouvez accéder à vos données, les rectifier, les effacer, limiter leur traitement ou vous y opposer en écrivant à <a href="mailto:alexandre.coury@aurea-media.fr">alexandre.coury@aurea-media.fr</a>. Vous pouvez aussi adresser une réclamation à la CNIL (cnil.fr).</p>
<h2>Cookies et mesure d'audience</h2>
<p>Le site ne dépose aucun cookie publicitaire. [Si un outil de mesure d'audience est installé, le nommer ici et préciser s'il est exempté de consentement ou soumis au bandeau.]</p>"""


def pages_legales():
    page_texte("mentions-legales.html", "Mentions légales", "Mentions légales", "Informations légales sur l'éditeur et l'hébergeur du site aurea-media.fr.", MENTIONS,
               "Mentions légales | Aurea Media", "Mentions légales du site aurea-media.fr : éditeur, directeur de la publication, hébergement, propriété intellectuelle et crédits des icônes et typographies.")
    page_texte("politique-confidentialite.html", "Confidentialité", "Politique de confidentialité", "Quelles données nous recueillons, pourquoi, combien de temps, et comment exercer vos droits.", CONFIDENTIALITE,
               "Politique de confidentialité | Aurea Media", "Politique de confidentialité d'Aurea Media : données du formulaire de contact, finalité, durée de conservation, destinataires et exercice de vos droits RGPD.")
    groupes = [("Services", [("Création de site vitrine", "creation-site-vitrine.html"), ("Site e-commerce", "creation-site-ecommerce.html"), ("Refonte de site internet", "refonte-site-internet.html"), ("Logo et identité visuelle", "logo-identite-visuelle.html"), ("Motion design", "motion-design.html"), ("Maquettes de présentation", "maquettes-presentation.html")]),
               ("Secteurs", [(n, f) for f, n, _, _ in SECTEURS]),
               ("Île de France", [("Agence web Paris", "agence-web-paris.html"), ("Agence web Hauts de Seine", "agence-web-hauts-de-seine.html")]),
               ("Agence", [("Réalisations", "realisations.html"), ("Tarifs", "tarifs.html"), ("L'agence", "agence.html"), ("Conseils", "conseils.html"), ("Contact", "contact.html")]),
               ("Informations", [("Mentions légales", "mentions-legales.html"), ("Politique de confidentialité", "politique-confidentialite.html")])]
    puce_or = ico("check")
    contenu = ""
    for g, liens in groupes:
        items = "".join(f'<li>{puce_or}<a href="{u}">{n}</a></li>' for n, u in liens)
        contenu += f'<h2>{g}</h2><ul class="liste-or">{items}</ul>'
    page_texte("plan-du-site.html", "Plan du site", "Plan du site", "Toutes les pages du site Aurea Media, classées par rubrique.", contenu,
               "Plan du site | Aurea Media, agence web sur mesure", "Plan du site Aurea Media : pages services, secteurs, agence web Paris et Hauts de Seine, réalisations, tarifs, conseils, contact et informations légales.")


if __name__ == "__main__":
    pages_legales()
    for f, c in PAGES.items():
        secteur(f, c)
    hauts_de_seine()
