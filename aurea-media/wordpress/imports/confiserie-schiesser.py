# Génère import-confiserie-schiesser.zip (Aurea Studio, Import / export) : python3 confiserie-schiesser.py <dossier>
import json, os, re, sys, shutil
sys.path.insert(0, '/home/user/Claude-Code/aurea-media/wordpress/outils')
from etudes import rendre, mots

d = {
 "cle": "confiserie-schiesser",
 "nom": "Confiserie Schiesser",
 "url": "https://confiserie-schiesser.ch/fr/accueil/",
 "domaine": "confiserie-schiesser.ch",
 "type": "site-vitrine", "type_nom": "Site vitrine",
 "lieu": "Bâle",
 "activite": "Confiserie et salon de thé depuis 1870",
 "fonctions": "Site trilingue, carte du Tea Room en ligne, horaires en direct, itinéraires, cadeaux d’entreprise",
 "mot_cle": "site internet de confiserie",
 "titre_seo": "Site internet de confiserie : Confiserie Schiesser à Bâle",
 "description": "Étude de cas Confiserie Schiesser : un site internet de confiserie trilingue pour une maison bâloise de 1870, avec Tea Room, horaires en direct et SEO local.",
 "accroche": "Confiserie et salon de thé sur le Marktplatz de Bâle depuis 1870, en français, allemand et anglais.",
 "accroche_en": "Confectionery and tea room on Basel’s Marktplatz since 1870, in French, German and English.",
 "intro": "La Confiserie Schiesser est installée sur le Marktplatz de Bâle, face à l’Hôtel de Ville, depuis le XIXe siècle. Nous avons conçu son site internet de confiserie pour faire revivre plus de 150 ans d’histoire, donner envie de pousser la porte de la boutique et du Tea Room, et aider les visiteurs de passage à trouver la maison, en français, en allemand et en anglais.",
 "bref": [
  "En bref : que présente le site de la Confiserie Schiesser ?",
  "Le site de la Confiserie Schiesser présente une maison bâloise fondée en 1870 par le confiseur glaronais Rudolf Schiesser, installée depuis 1873 au Marktplatz 19, face à l’Hôtel de Ville. Il réunit les trois espaces de la maison : la confiserie et son atelier au rez-de-chaussée, le Tea Room et le Rathstübli au premier étage. Le visiteur y découvre les spécialités maison, comme les Läckerli de Bâle, les pralinés ou le Schoggi-S, la carte complète du Tea Room avec ses prix, les horaires affichés en direct, les itinéraires depuis la gare et l’aéroport, une offre de cadeaux d’entreprise et l’histoire de la maison en photos d’archives. Le site est proposé en allemand, en français et en anglais, avec une adresse par langue, pour une clientèle locale autant que touristique. Une barre fixe sur mobile permet d’appeler la maison ou d’ouvrir l’itinéraire en un geste."
 ],
 "client": [
  "Une confiserie historique du Marktplatz",
  [
   "En 1870, Rudolf Schiesser achève sa formation de confiseur ; en 1873, il achète la maison du Marktplatz et y ouvre sa confiserie. Hans Schiesser fait ensuite reconstruire la maison avec une façade néogothique inspirée de l’Hôtel de Ville, et aménage le Tea Room et le Rathstübli au premier étage.",
   "Aujourd’hui encore, café, Tea Room et confiserie avec son propre atelier cohabitent sous le même toit. Le site s’adresse aussi bien aux Bâlois qu’aux entreprises à la recherche de cadeaux et aux voyageurs de passage, arrivés par la gare ou l’EuroAirport."
  ]
 ],
 "enjeux": [
  "Les enjeux du projet",
  "Le site devait servir à la fois une maison de tradition et des visiteurs pressés :",
  [
   "Transmettre plus de 150 ans d’histoire sans figer la marque dans le passé.",
   "Présenter clairement les deux univers de la maison : la confiserie et le Tea Room.",
   "Répondre tout de suite aux questions pratiques : horaires, accès, réservation.",
   "Parler aux Bâlois, aux frontaliers et aux touristes, dans leur langue.",
   "Être trouvé sur Google pour des recherches locales comme « confiserie à Bâle » ou « Läckerli Bâle »."
  ]
 ],
 "concu": "Nous avons conçu un site vitrine sur mesure, développé sous WordPress avec un thème propre à la maison. La page d’accueil raconte la maison étage par étage, du comptoir de la confiserie au Tea Room, puis déroule le savoir-faire de l’atelier et une chronologie illustrée. Chaque espace dispose de sa page, et la carte du Tea Room est publiée en clair sur le site, avec ses prix, en plus de sa version PDF.",
 "arbo_texte": "Cinq rubriques couvrent toutes les attentes : la confiserie, le salon de thé, l’histoire, la visite et le contact. Une page dédiée aux cadeaux d’entreprise complète l’ensemble pour la clientèle professionnelle.",
 "menu": ["Confiserie", ("Salon de thé", ["Carte du Tea Room", "Réservation"]), "Notre histoire", ("Nous rendre visite", ["Horaires", "Itinéraires"]), "Contact", "Cadeaux d’entreprise"],
 "cta": "Appeler et itinéraire",
 "da_texte": "La direction artistique s’appuie sur l’identité de la maison : un vert profond, une touche d’or et un papier crème qui rappelle les emballages Schiesser. Les titres en Bodoni Moda, une typographie à fort contraste d’esprit XIXe siècle, dialoguent avec Inter pour les textes. Les grandes photos de l’atelier et les archives donnent au site la chaleur d’une maison de famille.",
 "palette": [["Vert Schiesser", "#174633"], ["Or", "#C49A3C"], ["Papier", "#F1EADE"], ["Crème", "#E4D3B4"], ["Encre", "#2A1C12"]],
 "typo": "Bodoni Moda pour les titres, Inter pour les textes",
 "parcours_texte": "Le visiteur découvre la maison et ses deux étages, choisit entre la confiserie et le Tea Room, consulte la carte ou les spécialités, vérifie les horaires et l’itinéraire, puis appelle ou se rend sur place. Sur mobile, une barre fixe propose en permanence d’appeler, d’ouvrir l’itinéraire et de voir si la maison est ouverte.",
 "parcours": ["Découverte de la maison", "Confiserie ou Tea Room", "Carte et spécialités", "Horaires et itinéraire", "Appel ou visite"],
 "points": [
  "Les points clés du site",
  [
   "Un site trilingue, en allemand, en français et en anglais, avec une adresse par langue.",
   "Les horaires affichés en direct : ouvert, ferme dans tant de temps, horaires du jour et du lendemain.",
   "La carte complète du Tea Room en ligne, avec ses prix, et sa version PDF.",
   "Des itinéraires pas à pas depuis la gare de Bâle CFF, l’EuroAirport ou en voiture.",
   "Une barre d’action fixe sur mobile : appeler, itinéraire, ouverture.",
   "Une page de cadeaux d’entreprise avec demande d’offre, réponse sous un à deux jours ouvrables.",
   "Des données structurées de boulangerie et de salon de thé, de carte et de questions fréquentes pour le référencement local.",
   "Mesuré avec Lighthouse le 2 novembre 2026 : 95 en performance sur mobile, 99 sur ordinateur, 100 en accessibilité, bonnes pratiques et SEO."
  ]
 ],
 "conseil": [
  "Le site d’une confiserie doit donner faim et rassurer",
  [
   "Pour une confiserie ou un salon de thé, le site joue deux rôles : donner envie avec de belles images du savoir-faire, et répondre en quelques secondes aux questions pratiques que se pose un visiteur avant de se déplacer. Les horaires à jour, l’accès et la carte comptent autant que les photos.",
   "Retrouvez notre approche pour les [restaurants et lieux d’accueil](site-internet-restaurant), et d’autres réalisations du secteur comme [Villa Moniris](realisation:villa-moniris) ou le [Cercle La Raison](realisation:cercle-la-raison)."
  ]
 ],
 "faq": [
  ["Combien coûte le site internet d’une confiserie ?", "Chez Aurea Media, un site vitrine sur mesure démarre à 1 000 € HT et un site e-commerce à 1 650 € HT. Le prix final dépend du nombre de pages, des langues, des fonctionnalités (carte en ligne, horaires en direct, réservation, boutique) et de la part de contenus à rédiger. Chaque projet fait l’objet d’un [devis détaillé](tarifs), ligne par ligne, envoyé sous 24 h."],
  ["Un site multilingue est il bien référencé ?", "Oui, s’il est construit correctement : chaque langue a sa propre adresse, ses balises title et descriptions traduites, et les versions sont reliées entre elles par des balises hreflang. Google montre ainsi la bonne langue à chaque visiteur, ce qui est essentiel dans une ville frontalière comme Bâle."],
  ["Pourrai je mettre à jour la carte et les horaires moi même ?", "Oui. La carte, les prix, les horaires et les jours de fermeture se modifient depuis l’administration du site, sans toucher au code. L’affichage « ouvert » ou « fermé » se met alors à jour tout seul."]
 ],
 "appel": "Vous tenez une confiserie, une pâtisserie ou un salon de thé ?",
}
html = rendre(d)
for t in (' - ', ' – ', ' — '):
    assert t not in html, t
lt, ld = len(d['titre_seo']), len(d['description'])
print('mots', mots(html), 'titre', lt, 'meta', ld, 'bref', len(d['bref'][1].split()))
assert d['mot_cle'] in d['intro'].lower() and d['mot_cle'] in d['description'].lower() and d['mot_cle'] in d['titre_seo'].lower()
out = sys.argv[1]
os.makedirs(out + '/images', exist_ok=True)
cap = '/tmp/claude-0/-home-user-Claude-Code/d1516bda-ea4c-5a6c-8c35-caf032f10373/scratchpad/schiesser/cap/'
shutil.copy(cap + 'confiserie-schiesser.webp', out + '/images/confiserie-schiesser.webp')
shutil.copy(cap + 'confiserie-schiesser-mobile.webp', out + '/images/confiserie-schiesser-mobile.webp')
fiche = {
 "cle": d['cle'], "slug": "confiserie-schiesser", "titre": d['nom'], "url": "https://confiserie-schiesser.ch/fr/accueil/",
 "lieu": d['lieu'], "accroche": d['accroche'], "accroche_en": d['accroche_en'],
 "type": d['type'], "secteur": "restaurant", "une": False, "mur": True, "visible": True,
 "extrait": d['description'], "etude_html": html,
 "seo": {"titre": d['titre_seo'], "description": d['description'], "mot_cle": d['mot_cle']},
 "image": "images/confiserie-schiesser.webp", "mobile": "images/confiserie-schiesser-mobile.webp",
}
json.dump({"format": 1, "note": "Ajout de la réalisation Confiserie Schiesser (Bâle)", "realisations": [fiche]}, open(out + '/realisations.json', 'w'), ensure_ascii=False, indent=1)
open(out + '/etude.html', 'w').write(html)
