"""Vidéo explicative de l'appel à projets France 2030 « Métaux critiques 3 », voix clonée d'Alexandre.
Toutes les informations viennent du cahier des charges de l'appel à projets (2026_CDC_AAP_Métaux_critiques_3.pdf).
text : texte lu (nombres en toutes lettres, graphies phonétiques) ; sub : sous-titres ; scene : scène visuelle de src_aap."""
from script_cons_alex import REFS, REF_DIR  # noqa: F401
BPI = 'Bé-pé-i France'
L = [
    dict(scene='intro', text="France deux mille trente lance l'appel à projets Métaux critiques trois.", sub="France 2030 lance l'appel à projets Métaux critiques 3.", ex=0.65),
    dict(scene='intro', text="Son ambition : réduire la dépendance de l'industrie française aux métaux critiques."),
    dict(scene='contexte', text="La crise de l'aluminium en deux mille dix-huit, les restrictions chinoises sur les terres rares, la Covid dix-neuf, la pénurie de magnésium…",
         sub="La crise de l'aluminium en 2018, les restrictions chinoises sur les terres rares, la Covid 19, la pénurie de magnésium…"),
    dict(scene='contexte', text="Toutes ces crises l'ont montré : les risques d'approvisionnement en métaux sont permanents."),
    dict(scene='objectif', text="L'appel à projets soutient les meilleurs projets d'investissement et d'innovation, ambitieux et rapidement industrialisables sur le territoire."),
    dict(scene='objectif', text="Le but : réduire la dépendance aux fournisseurs extra européens, et créer de la valeur en France et en Europe.",
         sub="Le but : réduire la dépendance aux fournisseurs extra‑européens, et créer de la valeur en France et en Europe."),
    dict(scene='themes', text="Trois thématiques sont visées."),
    dict(scene='themes', text="La production de métaux primaires et issus du recyclage."),
    dict(scene='themes', text="La numérisation et l'automatisation de la production de matières premières."),
    dict(scene='themes', text="Et l'utilisation des métaux : consommation, volumes, qualités et substitutions, pour une même fonction fournie au client."),
    dict(scene='metaux', text="Ici, les métaux englobent aussi les métalloïdes, les minerais, et les composants intermédiaires comme les oxydes ou les sulfates."),
    dict(scene='metaux', text="Une liste indicative cible les métaux d'alliage, ceux de l'hydrogène, de l'électronique et des batteries, jusqu'aux terres rares."),
    dict(scene='dual', text="Chaque projet doit s'inscrire dans une chaîne de valeur duale stratégique pour la France."),
    dict(scene='dual', text="Énergie, transports et mobilité, santé, aéronautique, spatial, électronique et défense sont examinés en priorité."),
    dict(scene='dependance', text="Le projet doit viser une réduction d'au moins cinq points du taux de dépendance pour le métal considéré.",
         sub="Le projet doit viser une réduction d'au moins 5 points du taux de dépendance pour le métal considéré.", ex=0.65),
    dict(scene='dependance', text="Un premier effet significatif est attendu sous deux ans, et l'objectif complet dans les cinq ans qui suivent les investissements.",
         sub="Un premier effet significatif est attendu sous 2 ans, et l'objectif complet dans les 5 ans qui suivent les investissements."),
    dict(scene='environnement', text="L'empreinte environnementale compte aussi : un projet causant un préjudice important à l'environnement est exclu."),
    dict(scene='maturite', text="Les projets attendus ont dépassé la recherche amont : ils sont prêts pour une phase préindustrielle ou industrielle."),
    dict(scene='maturite', text="L'ingénierie industrielle est éligible, tout comme la recherche et développement, dès lors qu'elle s'inscrit dans un projet complet."),
    dict(scene='qui', text="Le projet peut être porté par une entreprise seule, immatriculée en France, quelle que soit sa taille, ou par un consortium jusqu'à cinq partenaires.",
         sub="Le projet peut être porté par une entreprise seule, immatriculée en France, quelle que soit sa taille, ou par un consortium jusqu'à 5 partenaires."),
    dict(scene='assiette', text="En projet individuel, il faut au moins deux millions d'euros de dépenses, et cinq millions pour une grande entreprise.",
         sub="En projet individuel, il faut au moins 2 millions d'euros de dépenses, et 5 millions pour une grande entreprise."),
    dict(scene='assiette', text="En projet collaboratif, le seuil passe à trois millions d'euros, ou cinq millions si le chef de file est une grande entreprise.",
         sub="En projet collaboratif, le seuil passe à 3 millions d'euros, ou 5 millions si le chef de file est une grande entreprise."),
    dict(scene='aide', text="L'aide associe des avances remboursables, au moins quarante pour cent, et une part de subvention, soixante pour cent au maximum.",
         sub="L'aide associe des avances remboursables, au moins 40 %, et une part de subvention, 60 % au maximum."),
    dict(scene='taux', text="Le taux d'aide dépend de la nature des travaux et de la taille de l'entreprise : jusqu'à soixante-dix pour cent pour la recherche industrielle d'une petite entreprise.",
         sub="Le taux d'aide dépend de la nature des travaux et de la taille de l'entreprise : jusqu'à 70 % pour la recherche industrielle d'une petite entreprise."),
    dict(scene='criteres', text="La sélection repose sur trois axes : l'innovation et la maturité du projet, son impact dual, économique et industriel en France, et son impact environnemental."),
    dict(scene='process', text=f"Le dossier se dépose en ligne sur la plateforme de {BPI}, qui conduit l'instruction, avant la décision finale du Premier ministre.",
         sub="Le dossier se dépose en ligne sur la plateforme de Bpifrance, qui conduit l'instruction, avant la décision finale du Premier ministre."),
    dict(scene='process', text=f"L'aide est ensuite versée en plusieurs tranches, dans le cadre d'une convention avec {BPI}.",
         sub="L'aide est ensuite versée en plusieurs tranches, dans le cadre d'une convention avec Bpifrance."),
    dict(scene='date', text="Date de relève : le vingt-trois février deux mille vingt-sept, à midi.", sub="Date de relève : le 23 février 2027, à midi.", ex=0.65),
    dict(scene='date', text="Les dossiers sont instruits jusqu'à épuisement des moyens financiers disponibles."),
    dict(scene='fin', text="Métaux critiques trois : réduire nos dépendances, et développer les filières industrielles d'avenir.",
         sub="Métaux critiques 3 : réduire nos dépendances, et développer les filières industrielles d'avenir.", ex=0.65),
]
for d in L:
    d.setdefault('sub', d['text'])
    d['ex'] = d.get('ex', 0.55)
    d['cfg'] = 0.5
