"""Contenus du site DualTech, repris à l'identique du site actuel (dualtech-event.eu, octobre 2026)."""
import json, re, unicodedata
from pathlib import Path

ICI = Path(__file__).resolve().parent
SITE = "https://dualtech-event.eu"
LUMA = "https://luma.com/wr8t4i35"
LINKEDIN = "https://www.linkedin.com/company/dualtech-event/"
CANDIDATURE_PRIX = "https://form.jotform.com/262093495361057"
VIDEO_HERO = "Y6EgCjojpQw"
VIDEO_WORKSHOPS = "fMLbi6lVYv0"
CARTE = "https://www.google.com/maps?q=Campus%20Cyber%20La%20D%C3%A9fense&z=15&output=embed"
MAPS = "https://www.google.com/maps/search/?api=1&query=Campus+Cyber+La+D%C3%A9fense"


def slug(s):
    s = unicodedata.normalize("NFKD", s).encode("ascii", "ignore").decode().lower()
    return re.sub(r"[^a-z0-9]+", "-", s).strip("-")


# Pages : fichier de la maquette et adresse WordPress (adresses actuelles conservées, aucune redirection nécessaire)
PAGES = {
    "accueil": ("index.html", "/"),
    "programme": ("programme-2026.html", "/programme-2026/"),
    "intervenants": ("intervenants-2026.html", "/intervenants-2026/"),
    "workshops": ("ateliers-workshops.html", "/ateliers-workshops/"),
    "partenaires": ("devenir-partenaire.html", "/devenir-partenaire/"),
    "prix": ("prix-de-linnovation-duale.html", "/prix-de-linnovation-duale/"),
    "infos": ("infos-pratiques.html", "/infos-pratiques/"),
    "retrospective": ("retrospective-fevrier-2026.html", "/retrospective-fevrier-2026/"),
    "contact": ("contact.html", "/contact/"),
    "inscription": ("inscription.html", "/inscription/"),
}

# Intervenants 2026 : [nom, fonction, organisation, portrait d'origine]
INTERVENANTS = [dict(nom=n, fonction=f, orga=o, photo=f"assets/intervenants/{slug(n)}.webp") for n, f, o, _ in json.load(open(ICI / "intervenants.json"))]
PAR_NOM = {i["nom"]: i for i in INTERVENANTS}

FILIERES = [
    ("Aéronautique", "Technologies aériennes et systèmes de nouvelle génération"),
    ("Biotechnologies", "Applications duales des avancées biologiques"),
    ("Blockchain", "Sécurisation décentralisée des échanges"),
    ("Communication", "Nouvelles générations de systèmes d'information"),
    ("Construction & infrastructures", "BTP, génie civil et infrastructures résilientes"),
    ("Cybersécurité", "Protection des systèmes critiques, cyberdéfense et cybersurveillance"),
    ("Data", "Analyse et valorisation des données massives"),
    ("Électronique", "Semi-conducteurs, composants et systèmes embarqués"),
    ("Énergie", "Production, réseaux, stockage et autonomie énergétique"),
    ("Greentech", "Technologies durables à impact stratégique"),
    ("Infrastructures critiques", "Sécurité, résilience et systèmes de surveillance"),
    ("Intelligence artificielle", "Applications souveraines de l'IA, données, logiciels avancés et calcul haute performance"),
    ("Maritime", "Technologies navales, maritimes et systèmes en environnement marin"),
    ("Matières premières critiques", "Mines, ressources stratégiques et industries extractives"),
    ("Médical", "Solutions innovantes pour la santé et le bien-être"),
    ("Mobilité", "Véhicules terrestres et nouvelles solutions de déplacement"),
    ("Photonique & optronique", "Lasers, capteurs optiques et technologies photoniques"),
    ("Quantique", "Capteurs, communications et calcul quantique"),
    ("Robotique", "Systèmes autonomes et robotique de nouvelle génération"),
    ("Spatial", "Satellites, infrastructures orbitales et technologies spatiales de nouvelle génération"),
]

CHIFFRES = [(400, "", "Participants attendus"), (40, "", "Intervenants et animateurs"), (20, "+", "Filières dual-use représentées"), (30, "", "Partenaires média et institutionnels")]

PROFILS = [
    ("Industriels et dirigeants", "Rencontrer les donneurs d'ordre publics, comprendre les priorités capacitaires et identifier les financements adaptés à votre montée en cadence.", "usine"),
    ("Investisseurs et financeurs", "Accéder à un dealflow qualifié de startups, PME et ETI stratégiques, et confronter vos thèses d'investissement à celles des acteurs publics.", "graphique"),
    ("Institutions et recherche", "Porter vos orientations devant l'écosystème réuni et repérer les technologies matures pour accélérer leur transfert vers l'industrie.", "institution"),
]

INFOS_LIEU = [
    ("Adresse", "Campus Cyber, 5-7 rue Bellini, 92800 Puteaux"),
    ("Transports", "RER A et Transilien L, station La Défense Grande Arche. Métro 1, terminus La Défense."),
    ("Accès", "Sur accréditation nominative, pièce d'identité demandée à l'entrée."),
    ("Accessibilité", "Site accessible aux personnes à mobilité réduite."),
]

FAQ_ACCUEIL = [
    ("Qu'est ce que l'industrie duale ?", "L'industrie duale conçoit des technologies dont les applications servent à la fois les marchés civils et les besoins de défense et de sécurité. Le quantique, le spatial, la robotique, l'intelligence artificielle et les matériaux avancés en sont les principales filières."),
    ("Qui peut participer à DualTech 2026 ?", "L'événement s'adresse aux représentants étatiques, aux industriels, aux dirigeants de PME et ETI stratégiques, aux investisseurs, aux chercheurs et aux acteurs du financement de l'innovation. L'accès se fait sur inscription validée par l'organisation."),
    ("Comment devenir partenaire de DualTech ?", "Plusieurs niveaux de partenariat existent, du soutien institutionnel à la prise de parole en plénière. Le dossier est disponible sur demande depuis la <a href=\"{partenaires}\">page dédiée</a>."),
    ("Où se déroule DualTech 2026 ?", "L'édition 2026 se tient au Campus Cyber, à La Défense, le 26 novembre 2026. <a href=\"" + MAPS + "\" target=\"_blank\" rel=\"noopener\">Voir le Campus Cyber sur Google Maps</a>."),
]

FAQ_INFOS = [
    ("Qu'est ce que l'industrie duale ?", FAQ_ACCUEIL[0][1]),
    ("Qui peut participer à DualTech 2026 ?", "L'événement s'adresse aux représentants étatiques, aux industriels, aux dirigeants de PME et ETI stratégiques, aux investisseurs, aux chercheurs et aux acteurs du financement de l'innovation. L'accès se fait sur accréditation validée par l'organisation."),
    ("Faut il réserver son hébergement longtemps à l'avance ?", "La zone de La Défense connaît une forte demande en semaine. Il est recommandé de réserver plusieurs semaines avant l'événement, en particulier pour les hôtels les plus proches du Campus Cyber."),
    ("Comment se rendre au Campus Cyber en transports en commun ?", "Le Campus Cyber est desservi par le RER A, le Transilien L, la ligne 1 du métro et le tramway T2, tous à la station La Défense Grande Arche ou Esplanade de la Défense. Aucun parking n'est réservé sur place pour l'événement."),
    ("Quels documents faut il présenter à l'entrée ?", "Une pièce d'identité est demandée à l'entrée pour tous les participants accrédités, l'accès étant nominatif."),
    ("Où se déroule DualTech 2026 ?", "L'édition 2026 se tient au Campus Cyber, à La Défense, le 26 novembre 2026. La première édition s'était tenue au Palais du Luxembourg en février 2026."),
]

HOTELS = [
    ("À proximité immédiate", "Mama Shelter Paris La Défense", "Situé à quelques pas du Campus Cyber, l'établissement constitue l'une des solutions d'hébergement les plus proches du lieu de l'événement.", "https://fr.mamashelter.com/paris-la-defense/"),
    ("Puteaux · La Défense", "ibis Styles Puteaux Paris La Défense", "Établissement situé à Puteaux, à proximité du quartier d'affaires de La Défense et de ses principaux accès.", "https://all.accor.com/hotel/A9I7/index.fr.shtml"),
    ("Puteaux", "Best Western Seine West Hotel", "Établissement situé à Puteaux, à proximité du quartier de La Défense et des bords de Seine.", "https://www.bestwestern.fr/fr/hotel-Puteaux-BEST-WESTERN-Seine-West-Hotel-93832"),
    ("Appartements équipés", "Adagio Access La Défense Puteaux", "Résidence hôtelière proposant des studios et appartements équipés à proximité du quartier d'affaires de La Défense.", "https://www.adagio-city.com/fr/hotel-8987-aparthotel-adagio-access-la-defense-puteaux/index.shtml"),
]

# Programme du jeudi 26 novembre 2026 : (début, fin, type, titre, note, intervenants, modération)
# Types : keynote, table, atelier, networking, autres ; « temps-fort » pour la remise du Prix
PROGRAMME = [
    ("Matinée", [
        ("08h30", "09h00", "networking", "Accueil des participants", "Petit-déjeuner networking", [], []),
        ("09h00", "09h05", "autres", "Mot d'accueil de l'organisateur", "", ["Clément Leprince"], []),
        ("09h05", "09h10", "autres", "Mot d'accueil du partenaire territorial", "", ["Jacques Kossowski"], []),
        ("09h10", "09h20", "autres", "Allocution d'ouverture", "", ["Stéphane Boujnah"], []),
        ("09h20", "09h30", "autres", "Ouverture ministérielle", "", [], []),
        ("09h30", "10h15", "table", "Table ronde : « Industrie de défense, quelles innovations pour préparer les conflits de demain ? »", "", ["Eric Ottou", "Alexandre Papaemmanuel", "Marc Antoine", "Hubert Raymond"], ["Amélie Cindea", "Marine Le Châtelier"]),
        ("10h15", "10h30", "keynote", "Keynote : « La robotisation du champ de bataille : préparer les armées à la guerre de demain »", "", [], []),
        ("10h30", "11h00", "networking", "Pause networking", "", [], []),
        ("11h00", "11h15", "keynote", "Keynote : « Recherche, investissement et commande : quel nouveau modèle pour l'innovation de défense ? »", "", ["Emmanuel Chiva"], []),
        ("11h15", "12h00", "table", "Table ronde : « Du civil au militaire : réussir la bascule duale »", "", ["Jean-Louis Constanza", "Pascal Saubion", "Alexandre Medvedowsky"], ["Michael Bollet", "Eliot Bernard"]),
    ]),
    ("Mi-journée", [
        ("12h00", "12h15", "keynote", "Keynote : « Défense européenne : quel rôle pour les banques publiques de développement ? »", "", [], []),
        ("12h15", "14h00", "networking", "Déjeuner", "", [], []),
    ]),
    ("Après-midi", [
        ("14h00", "14h15", "keynote", "Keynote : « Scale up : comment financer le passage à l'échelle des startups stratégiques ? »", "", ["Jean-Christophe Lambert"], []),
        ("14h15", "15h00", "table", "Table ronde : « Investir dans la New defence : effet de mode ou tendance de fond ? »", "En parallèle, visite du Catalyseur Santé", ["Charles Beigbeder", "Benoist Grossmann", "Romain Lucken", "François Carlot"], ["Jeanne Tricot", "Paul Brun"]),
        ("15h00", "15h45", "atelier", "Workshops & Ateliers au Campus Cyber", "", [], []),
        ("15h45", "16h00", "keynote", "Keynote : « Biens à double usage : comment concilier contrôle des exportations et développement industriel ? »", "", ["Mathieu Fossat"], []),
        ("16h00", "16h15", "networking", "Pause networking", "", [], []),
        ("16h15", "16h30", "keynote", "Keynote : « Le territoire comme accélérateur d'innovation duale »", "", ["Jean-Christophe Fromantin"], []),
        ("16h30", "17h15", "table", "Table ronde : « Produire plus, plus vite : territoires, filières et deeptech au cœur de la réindustrialisation »", "", ["Benoit Vedel", "Xavier Desmaison", "Emmanuel Menier", "Jean Prévost"], ["Léonie Meister", "Arthur Binetti"]),
        ("17h15", "18h00", "temps-fort", "Remise du Prix de l'Innovation Duale", "", ["Alexandra André"], []),
        ("18h00", "18h15", "networking", "Pause networking", "", [], []),
        ("18h15", "18h30", "keynote", "Keynote : « Souveraineté technologique : le Dual-use au cœur de la nouvelle stratégie européenne d'innovation »", "", ["Stéphane Ouaki"], []),
        ("18h30", "18h45", "keynote", "Keynote : « Combattre demain : préparer et transformer nos forces armées »", "", [], []),
        ("18h45", "18h50", "autres", "Mot de clôture de l'organisateur", "", ["Alexis Toustou"], []),
        ("18h50", "19h00", "autres", "Allocution ministérielle de clôture", "", [], []),
    ]),
]
TYPES = {"keynote": "Keynote", "table": "Table ronde", "atelier": "Ateliers", "networking": "Networking", "autres": "Allocution", "temps-fort": "Temps fort"}
FILTRES = [("tous", "Tous"), ("keynote", "Keynotes"), ("table", "Tables rondes"), ("atelier", "Ateliers"), ("networking", "Networking"), ("autres", "Autres")]

# Partenaires 2026 (page Devenir partenaire)
PARTENAIRES_2026 = [
    ("Institutions publiques", [("European Innovation Council (EIC)", "eic"), ("Paris Ouest La Défense", "pold-horizontal")]),
    ("Financement et marchés", [("Bpifrance", "bpifrance"), ("Euronext", "euronext"), ("Défense Angels", "defense-angels"), ("Expansion", "expansion")]),
    ("Écosystèmes industriels et technologiques", [("GIFAS", "gifas"), ("France Deeptech", "france-deeptech"), ("La Place Stratégique", "la-place-strategique")]),
    ("Communication et médias", [("NSI Group", "nsi-group"), ("Décideurs Innovation", "decideurs")]),
]

PARTENAIRES_PRIX = [("INAS", "inas"), ("La Place Stratégique", "la-place-strategique-2"), ("Paris Ouest La Défense", "pold"), ("Defence Angels", "defence-angels"),
                    ("Generate", "generate"), ("Robotic Valley", "robotic-valley"), ("Cluster EDEN", "cluster-eden"), ("Le Village by CA", "village-by-ca"),
                    ("Le Catalyseur par Paris Ouest La Défense", "catalyseur"), ("StartAir by GIFAS", "starair"), ("Defence Angels European Network", "eban"), ("Wind Capital", None)]

# Rétrospective : partenaires de la première édition
PARTENAIRES_2025 = [
    ("Agence de l'Innovation de Défense (AID)", "aid", "Pilote, coordonne et soutient l'innovation au sein du ministère des Armées. Identifie les ruptures technologiques critiques, finance les projets innovants et accélère leur passage en capacité opérationnelle."),
    ("Bpifrance", "bpifrance", "Banque publique d'investissement chargée d'accompagner entreprises, startups et PME à chaque étape de leur croissance : financement, innovation, fonds propres, garantie et accompagnement stratégique."),
    ("Euronext", "euronext", "Principale place boursière paneuropéenne, opérant les marchés de Paris, Amsterdam, Bruxelles, Lisbonne, Oslo, Dublin et Milan. Joue un rôle fondamental dans l'accès des entreprises au financement via les marchés."),
    ("GICAT / GENERATE", "gicat", "Groupement des industries françaises de défense et de sécurité terrestres et aéroterrestres. Le programme GENERATE accompagne les startups duales et les intègre aux écosystèmes industriels et opérationnels."),
    ("NSI Group", "nsi-group", "Acteur industriel spécialisé dans la conception, la protection et l'intégration de solutions hautement sécurisées pour les environnements sensibles : défense, sûreté et infrastructures critiques."),
    ("Starburst", "starburst", "Accélérateur mondial dédié aux secteurs de l'aérospatial, de la défense et des technologies duales. Accompagne les entreprises en phase d'amorçage ou de croissance."),
    ("Quandela", "quandela", "Leader européen des technologies quantiques photoniques. Développe des calculateurs quantiques, des solutions de cybersécurité quantique et des briques technologiques essentielles au computing quantique."),
    ("Start Industrie", "start-industrie", "Accompagne les startups industrielles dans leur passage à l'échelle, leur structuration et leur industrialisation, notamment dans les secteurs stratégiques."),
    ("La French Fab", "french-fab", "Étendard de l'industrie française. Fédère les entreprises industrielles engagées dans la modernisation, la transformation numérique et la transition vers une industrie durable et souveraine."),
    ("Défense Angels", "defense-angels", "Premier réseau de business angels dédié aux technologies de défense, de sécurité et duales. Finance et accompagne des startups développant des solutions clés pour la souveraineté technologique."),
    ("La Place Stratégique", "la-place-strategique", "Média et plateforme spécialisée dans les enjeux de souveraineté, de défense, d'industrie et de stratégie."),
    ("ESTRADS Certification", "estrads", "Accompagne les acteurs industriels, institutionnels et technologiques dans la certification, la qualification et l'évaluation de systèmes critiques."),
    ("The Sov Company", "the-sov-company", "Développe des solutions et services dédiés à la souveraineté technologique et informative. Analyse stratégique et sécurisation des infrastructures critiques."),
]

# Rétrospective : personnalités et tables rondes du 5 février 2026
GRANDS_TEMOINS = [
    ("Emmanuel Chiva", "Président et Directeur général de l'ONERA, ancien Délégué général pour l'Armement.", "assets/intervenants/emmanuel-chiva.webp"),
    ("Jean-Louis Thiériot", "Député de Seine-et-Marne, en mission gouvernementale sur la stratégie industrielle de défense européenne.", "assets/intervenants/jean-louis-thieriot.webp"),
    ("Bertrand Besancenot", "Senior Advisor chez NSI Group, ancien Ambassadeur de France au Qatar puis en Arabie saoudite.", "assets/intervenants/bertrand-besancenot.webp"),
]
TABLES_2025 = [
    ("Table ronde 1", "Financement stratégique des filières industrielles duales",
     [("Jeanne Tricot", "Directrice des programmes", "INAS"), ("Paul Brun", "Président de la commission du financement de l'industrie de défense", "INAS")],
     [("Guillaume Morelli", "Head of Listing France & Iberia", "Euronext"), ("Grégoire Chauvière Le Drian", "Directeur du bureau du Groupe BEI en France", "Banque européenne d'investissement"),
      ("Nicolas Dubourg", "Associé fondateur et Président", "ISALT"), ("Cédric Lowenbach", "Directeur du Développement et référent défense", "Bpifrance")]),
    ("Table ronde 2", "Passage à l'échelle de l'industrie dual-use dans les territoires",
     [("Valentin Aubert", "Président de la commission de l'innovation duale", "INAS"), ("Aurélien Gohier", "Directeur de la communication", "Quandela")],
     [("Guy Gourevitch", "Président et cofondateur", "Défense Angels"), ("Thomas Gourlan", "Ancien Président d'agglomération et conseiller régional d'Île-de-France", "Communauté d'agglomération de Rambouillet Territoires"),
      ("Bertrand Gouillart", "Chef du pôle innovation ouverte", "Agence de l'innovation de défense (AID)"), ("Hubert Raymond", "Directeur de l'Innovation et de Generate", "GICAT")]),
]

# Prix de l'innovation duale
CATEGORIES_PRIX = [("Robotique", "Systèmes robotiques et autonomes"), ("Santé", "Innovation et résilience sanitaire"), ("Deeptech", "Technologies de rupture")]
CALENDRIER_PRIX = [
    ("Été", "Préparation", "Été 2026 · Préparation", "Préparation de l'appel à candidatures", "Finalisation du dispositif, des éléments de candidature et des supports de communication du Prix."),
    ("Septembre", "Lancement", "Septembre 2026 · Appel à candidatures", "Lancement de l'appel à candidatures", "Ouverture officielle des candidatures pour les catégories Robotique, Santé et Deeptech."),
    ("Mi-octobre", "Clôture", "Mi-octobre 2026 · Candidatures", "Clôture des candidatures", "Date limite de dépôt des dossiers pour participer à l'édition 2026 du Prix de l'innovation duale."),
    ("Octobre", "Présélection", "Mi-octobre à fin octobre · Sélection", "Analyse et présélection", "Étude des dossiers reçus et sélection des projets appelés à poursuivre le processus devant le jury."),
    ("Novembre", "Jury & pitch", "Novembre 2026 · Finale", "Jurys et sessions de pitch", "Les finalistes présenteront leur projet devant les jurys de sélection de chacune des trois catégories."),
    ("Fin novembre", "DualTech", "Fin novembre 2026 · DualTech 2026", "Remise des Prix à DualTech", "Annonce des trois lauréats et remise officielle des Prix à l'occasion de l'événement DualTech 2026."),
]


# Descriptions des partenaires 2026 (fenêtre ouverte au clic sur un logo, reprises du site actuel)
DESCRIPTIONS = {
    "European Innovation Council (EIC)": "Le Conseil européen de l’innovation soutient les innovations de rupture et le développement des entreprises innovantes en Europe.",
    "Paris Ouest La Défense": "Paris Ouest La Défense est l’établissement public territorial partenaire de DualTech.",
    "Bpifrance": "Bpifrance est la banque publique d'investissement chargée d'accompagner les entreprises, startups, PME et ETI à chaque étape de leur développement, à travers le financement, l'innovation, les fonds propres, la garantie et l'accompagnement stratégique.",
    "Euronext": "Euronext est la principale place boursière paneuropéenne. Elle joue un rôle central dans l'accès des entreprises au financement via les marchés de capitaux.",
    "Défense Angels": "Défense Angels est un réseau de business angels dédié aux technologies de défense, de sécurité et duales. Le réseau finance et accompagne des startups développant des solutions stratégiques.",
    "Expansion": "Expansion accompagne le financement des technologies stratégiques.",
    "GIFAS": "Groupement des Industries Françaises Aéronautiques et Spatiales.",
    "France Deeptech": "France Deeptech fédère les acteurs de l’écosystème deeptech français.",
    "La Place Stratégique": "La Place Stratégique est un média et une plateforme spécialisée dans les enjeux de souveraineté, de défense, d'industrie et de stratégie.",
    "NSI Group": "L'agence globale d'influence stratégique du groupe ADIT, pour accompagner dirigeants, entreprises et États.",
    "Décideurs Innovation": "Décideurs Innovation est un média consacré à l’innovation et à ses acteurs."
}
# Partenaires du Prix : rôle affiché quand le site actuel ne donne pas de description
for _n, _f, _d in PARTENAIRES_2025:
    DESCRIPTIONS.setdefault(_n, _d)
DESCRIPTIONS.setdefault("INAS", "L'Institut National des Affaires Stratégiques organise DualTech et s'associe au Prix de l'innovation duale.")
DESCRIPTIONS.setdefault("Defence Angels", DESCRIPTIONS["Défense Angels"])

# Vidéos présentes sur le site actuel
VIDEO_CAMPUS = "ZuQblYJsOiU"
MP4_AERO = SITE + "/wp-content/uploads/2026/08/YTDown.com_YouTube_Aerospace-Industry-Edit-Can-You-Hear-The_Media_eY8bwqf1nv8_001_1080p.mp4"
MP4_PRIX = SITE + "/wp-content/uploads/2026/08/WhatsApp-Video-2026-08-19-at-15.37.10.mp4"
MP4_RESEAU = "assets/video/fond-reseau.mp4"

# Photos libres de droits (Unsplash, licence gratuite) associées aux filières
PHOTOS_FILIERES = {
    "Aéronautique": "aeronautique-avion-de-chasse", "Communication": "communication-paraboles", "Cybersécurité": "cybersecurite-circuit",
    "Data": "data-particules", "Électronique": "electronique-wafer", "Intelligence artificielle": "intelligence-artificielle-processeur",
    "Maritime": "maritime-navire", "Quantique": "quantique-puce", "Robotique": "robotique-bras", "Spatial": "spatial-satellite",
    "Biotechnologies": "biotechnologies-microscope", "Blockchain": "blockchain-cubes", "Construction & infrastructures": "construction-pont",
    "Énergie": "energie-pylones", "Greentech": "greentech-eoliennes", "Infrastructures critiques": "infrastructures-salle-de-controle",
    "Matières premières critiques": "matieres-premieres-mine", "Médical": "medical-imagerie", "Mobilité": "mobilite-convoi",
    "Photonique & optronique": "photonique-laser",
}

# Univers des intervenants (filtres de la page Intervenants)
UNIVERS = [("tous", "Tous"), ("institutions", "Institutions et territoires"), ("industrie", "Industrie et technologies"), ("finance", "Financement et marchés"), ("inas", "Organisateur (INAS)")]
def univers(orga):
    o = orga.lower()
    if "inas" in o or "affaires stratégiques" in o: return "inas"
    if any(k in o for k in ("euronext", "eurazeo", "expansion", "rothschild")): return "finance"
    if any(k in o for k in ("onera", "eismea", "ministère", "mairie", "paris ouest", "french tech")): return "institutions"
    return "industrie"
for _i in INTERVENANTS:
    _i["univers"] = univers(_i["orga"])
    _i["sessions"] = [(d, f, t, titre, "modération" if _i["nom"] in mods else "intervention")
                      for _, cr in PROGRAMME for d, f, t, titre, _n, ivs, mods in cr if _i["nom"] in ivs or _i["nom"] in mods]
