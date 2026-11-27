# -*- coding: utf-8 -*-
"""Pages territoires et établissements pénitentiaires."""
from pages_expertises import expertise_body

T = '<span class="todo">à vérifier</span>'

# ---------------------------------------------------------------------------- Hub interventions
REGIONS = [
    ("75", "Paris", "Cabinet principal. Tribunal judiciaire de Paris, cour d'appel de Paris, pôle financier, section cybercriminalité du parquet.", "/avocat-penaliste-paris/", "Page publiée"),
    ("91", "Essonne", "Tribunal judiciaire d'Évry-Courcouronnes, centre pénitentiaire de Fleury-Mérogis, commissariats et brigades du département.", "/avocat-penaliste-essonne/", "Page publiée"),
    ("92 · 93 · 94", "Petite couronne", "Nanterre, Bobigny, Créteil. Maisons d'arrêt de Nanterre, de Villepinte et centre pénitentiaire de Fresnes.", "", "Phase 2"),
    ("2A · 2B", "Corse", "Tribunaux judiciaires d'Ajaccio et de Bastia, cour d'appel de Bastia, centre pénitentiaire de Borgo, centre de détention de Casabianda.", "", "Proposition"),
    ("13 · 06", "Provence-Alpes-Côte d'Azur", "Marseille et Nice : juridiction interrégionale spécialisée (JIRS) de Marseille, dossiers transfrontaliers.", "", "Proposition"),
    ("69", "Lyon et Auvergne-Rhône-Alpes", "JIRS de Lyon, maison d'arrêt de Lyon-Corbas, dossiers économiques et financiers.", "", "Proposition"),
]


def interventions_body(p):
    rows = "".join(
        f'<tr><td><b>{d}</b></td><td><b>{n}</b></td><td>{t}</td><td>{f"<a href=\"{u}\">Voir la page</a>" if u else "—"}</td><td><span class="pill{" pill--on" if s == "Page publiée" else ""}">{s}</span></td></tr>'
        for d, n, t, u, s in REGIONS)
    return f"""<section class="night page-head"><div class="wrap">{{{{CRUMBS}}}}
<span class="cote">Interventions</span><h1 style="margin-top:1rem">Où intervient <em>le cabinet</em></h1>
<p class="intro">Inscrit au Barreau de Paris, <strong>Me Charles Culioli-Paoli</strong> peut plaider devant toutes les juridictions pénales françaises. Le cabinet concentre son activité sur Paris et l'Essonne, et intervient dans d'autres régions selon les dossiers.</p></div></section>
<section class="section"><div class="wrap">
<div class="table-wrap" style="margin-top:0"><table><thead><tr><th>Dép.</th><th>Territoire</th><th>Juridictions et établissements</th><th>Page</th><th>Statut</th></tr></thead><tbody>{rows}</tbody></table></div>
<div class="prose" style="margin-top:3rem">
<h2 style="margin-top:0">Pourquoi ces régions</h2>
<p><b>La Corse</b> : le nom du cabinet et la pratique de la langue corse créent une légitimité naturelle. Le marché local compte peu de cabinets pénalistes visibles en ligne sur les requêtes liées à Borgo ou à la cour d'appel de Bastia.</p>
<p><b>Provence-Alpes-Côte d'Azur</b> : Marseille et Nice concentrent des dossiers de criminalité organisée et des affaires transfrontalières (Italie, Monaco, Espagne) qui rejoignent les compétences du cabinet en extradition et en droit international.</p>
<p><b>Lyon</b> : second pôle économique du pays, avec une JIRS et un contentieux pénal des affaires important.</p>
<p class="ref">Ces propositions sont à confirmer avec Me Culioli-Paoli et à vérifier avec des données de volume et de concurrence (Google Search Console, outil de recherche de mots-clés) avant la création de toute page régionale.</p>
</div></div></section>"""


INTERVENTIONS = {
    "slug": "interventions", "nav": "Interventions", "prio": "P2", "schema_label": "CollectionPage",
    "kw": "avocat pénaliste Île-de-France", "kw2": ["avocat pénaliste Paris", "avocat pénaliste Essonne", "avocat pénaliste Corse"],
    "title": "Zones d'intervention | Avocat pénaliste Culioli-Paoli",
    "desc": "Paris, Essonne, Île-de-France, Corse, Marseille, Lyon : les territoires où intervient Me Charles Culioli-Paoli, avocat pénaliste au Barreau de Paris.",
    "h1_plain": "Où intervient le cabinet", "body": interventions_body,
}

# ---------------------------------------------------------------------------- Essonne
ESSONNE = {
    "slug": "avocat-penaliste-essonne", "nav": "Avocat pénaliste en Essonne", "crumbs": [("Interventions", "interventions")],
    "nav_slug": "interventions", "schema": "Service", "area": "Essonne", "prio": "P1", "cote": "Territoire · 91",
    "kw": "avocat pénaliste Essonne",
    "kw2": ["avocat pénaliste Évry", "avocat tribunal Évry-Courcouronnes", "avocat pénaliste Massy", "avocat garde à vue Essonne"],
    "title": "Avocat pénaliste Essonne | Évry, Massy, Fleury-Mérogis",
    "desc": "Avocat pénaliste en Essonne : garde à vue, tribunal judiciaire d'Évry-Courcouronnes, détenus de Fleury-Mérogis. Me Culioli-Paoli intervient dans tout le 91.",
    "h1": "Avocat pénaliste en Essonne, <em>d'Évry-Courcouronnes à Fleury-Mérogis</em>",
    "h1_plain": "Avocat pénaliste en Essonne, d'Évry-Courcouronnes à Fleury-Mérogis",
    "intro": "Garde à vue dans un commissariat de l'Essonne, audience au tribunal judiciaire d'Évry-Courcouronnes, proche détenu à Fleury-Mérogis&nbsp;: <strong>Me Charles Culioli-Paoli, avocat pénaliste</strong>, intervient dans tout le département.",
    "aside": [("Tribunal", "Tribunal judiciaire d'Évry-Courcouronnes"), ("Cour d'appel", "Paris"),
              ("Bureau", "Massy<span class=\"todo\">à confirmer</span>")],
    "sections": [
        ("tribunal", "Le tribunal judiciaire d'Évry-Courcouronnes", """<p>Le tribunal judiciaire d'Évry-Courcouronnes est la juridiction pénale de l'Essonne : tribunal correctionnel, juges d'instruction, juge des libertés et de la détention, juge de l'application des peines. Les appels sont portés devant la cour d'appel de Paris.</p>
<p>Son juge de l'application des peines est notamment compétent pour les personnes détenues au <a href="/avocat-centre-penitentiaire-fleury-merogis/">centre pénitentiaire de Fleury-Mérogis</a>.</p>"""),
        ("villes", "Les villes où le cabinet intervient", """<div class="fiche">
<div><dt>Évry-Courcouronnes</dt><dd>Tribunal judiciaire, préfecture, commissariat</dd></div>
<div><dt>Massy</dt><dd>Commissariat, bureau du cabinet<span class="todo">à confirmer</span></dd></div>
<div><dt>Fleury-Mérogis</dt><dd>Centre pénitentiaire</dd></div>
<div><dt>Corbeil-Essonnes</dt><dd>Commissariat</dd></div>
<div><dt>Palaiseau</dt><dd>Commissariat</dd></div>
<div><dt>Longjumeau</dt><dd>Commissariat</dd></div>
<div><dt>Étampes</dt><dd>Commissariat, brigades de gendarmerie du sud du département</dd></div>
</div>
<p class="ref" style="margin-top:1rem">Liste des services d'enquête à vérifier avant publication.</p>"""),
        ("situations", "Les situations les plus fréquentes", """<ul>
<li><a href="/avocat-garde-a-vue-paris/">Garde à vue</a> dans un commissariat ou une brigade de gendarmerie de l'Essonne.</li>
<li>Comparution immédiate devant le tribunal correctionnel d'Évry-Courcouronnes.</li>
<li>Instruction et détention provisoire, débat devant le juge des libertés et de la détention.</li>
<li><a href="/avocat-amenagement-de-peine/">Aménagement de peine</a> pour les personnes détenues à Fleury-Mérogis.</li></ul>"""),
    ],
    "faq": [("Un avocat parisien peut-il plaider à Évry-Courcouronnes&nbsp;?", "Oui. En matière pénale, un avocat inscrit à un barreau français peut plaider devant toutes les juridictions pénales, y compris le tribunal judiciaire d'Évry-Courcouronnes.")],
    "related": [("avocat-centre-penitentiaire-fleury-merogis", "Fleury-Mérogis", "Défendre une personne détenue"),
                ("avocat-garde-a-vue-paris", "Garde à vue", "Intervention en urgence"),
                ("avocat-penaliste-paris", "Avocat pénaliste à Paris", "Le cabinet principal")],
    "body": expertise_body,
}

# ---------------------------------------------------------------------------- Hub établissements
ETAB = [
    ("idf", "Centre pénitentiaire de Fleury-Mérogis", "Fleury-Mérogis (91)", "Centre pénitentiaire", "Très forte", "/avocat-centre-penitentiaire-fleury-merogis/", "Publiée"),
    ("idf", "Centre pénitentiaire de Fresnes", "Fresnes (94)", "Centre pénitentiaire", "Forte", "", "Phase 2"),
    ("idf", "Prison de Paris-La Santé", "Paris (75)", "Centre pénitentiaire", "Forte", "", "Phase 2"),
    ("idf", "Maison d'arrêt de Villepinte", "Villepinte (93)", "Maison d'arrêt", "Moyenne", "", "Phase 2"),
    ("idf", "Centre pénitentiaire Sud-Francilien", "Réau (77)", "Centre pénitentiaire", "Moyenne", "", "Phase 2"),
    ("idf", "Maison d'arrêt de Bois-d'Arcy", "Bois-d'Arcy (78)", "Maison d'arrêt", "Moyenne", "", "Phase 3"),
    ("idf", "Maison d'arrêt de Nanterre", "Nanterre (92)", "Maison d'arrêt", "Moyenne", "", "Phase 3"),
    ("idf", "Maison d'arrêt du Val-d'Oise", "Osny (95)", "Maison d'arrêt", "Moyenne", "", "Phase 3"),
    ("reg", "Maison d'arrêt de Lyon-Corbas", "Corbas (69)", "Maison d'arrêt", "Forte", "", "Phase 3"),
    ("reg", "Centre pénitentiaire de Marseille", "Marseille (13)", "Centre pénitentiaire", "Forte", "", "Phase 3"),
    ("reg", "Centre pénitentiaire de Châteauroux", "Châteauroux (36)", "Centre pénitentiaire", "Moyenne", "", "Phase 3"),
    ("cor", "Centre pénitentiaire de Borgo", "Borgo (2B)", "Centre pénitentiaire", "Faible, peu concurrencée", "", "Proposition"),
    ("cor", "Maison d'arrêt d'Ajaccio", "Ajaccio (2A)", "Maison d'arrêt", "Faible, peu concurrencée", "", "Proposition"),
    ("cor", "Centre de détention de Casabianda", "Aléria (2B)", "Centre de détention", "Faible", "", "Proposition"),
]


def etab_body(p):
    rows = "".join(
        f'<tr data-region="{r}"><td><b>{n}</b></td><td>{l}</td><td>{t}</td><td>{pot}</td><td>{f"<a href=\"{u}\">Voir la page</a>" if u else "—"}</td><td><span class="pill{" pill--on" if s == "Publiée" else ""}">{s}</span></td></tr>'
        for r, n, l, t, pot, u, s in ETAB)
    return f"""<section class="night page-head"><div class="wrap">{{{{CRUMBS}}}}
<span class="cote">Établissements pénitentiaires</span><h1 style="margin-top:1rem">Défendre une personne <em>détenue</em></h1>
<p class="intro">Détention provisoire, demande de mise en liberté, commission de discipline, aménagement de peine&nbsp;: <strong>Me Charles Culioli-Paoli</strong> se rend dans les établissements pénitentiaires pour rencontrer les personnes qu'il défend.</p></div></section>
<section class="section"><div class="wrap folio">
<div class="folio-margin"><span class="cote">Registre</span><span class="note">Une page par établissement, seulement quand elle apporte une information utile.</span></div>
<div class="folio-body">
<div class="filters" role="group" aria-label="Filtrer par zone"><button type="button" data-f="all" aria-pressed="true">Tous</button><button type="button" data-f="idf" aria-pressed="false">Île-de-France</button><button type="button" data-f="reg" aria-pressed="false">Régions</button><button type="button" data-f="cor" aria-pressed="false">Corse</button></div>
<div class="table-wrap"><table><thead><tr><th>Établissement</th><th>Commune</th><th>Type</th><th>Potentiel de recherche</th><th>Page</th><th>Statut</th></tr></thead><tbody>{rows}</tbody></table></div>
<p class="ref" style="margin-top:1rem">Potentiel estimé qualitativement, à confirmer avec des données réelles de volume. Dénominations officielles à vérifier sur le site du ministère de la Justice.</p>
<div class="prose" style="margin-top:3rem">
<h2 style="margin-top:0">Ce que l'avocat peut faire pour une personne détenue</h2>
<ul><li><b>Personne en détention provisoire</b> : demandes de mise en liberté, appel devant la chambre de l'instruction, préparation des interrogatoires.</li>
<li><b>Personne condamnée</b> : <a href="/avocat-amenagement-de-peine/">aménagement de peine</a>, libération conditionnelle, permissions de sortir.</li>
<li><b>Vie en détention</b> : assistance devant la commission de discipline, recours contre certaines décisions de l'administration pénitentiaire.</li></ul>
<h2>Une méthode éditoriale exigeante</h2>
<p>Chaque page d'établissement contient des informations propres : juridictions compétentes, modalités de visite des avocats, organisation de l'établissement, démarches utiles pour les proches. Aucune page n'est publiée si elle se limite à remplacer le nom d'une prison, conformément aux règles de Google sur les contenus à faible valeur ajoutée.</p>
</div></div></div></section>"""


ETABLISSEMENTS = {
    "slug": "etablissements-penitentiaires", "nav": "Établissements pénitentiaires", "prio": "P1", "schema_label": "CollectionPage",
    "kw": "avocat prison", "kw2": ["avocat détenu", "avocat centre pénitentiaire", "avocat maison d'arrêt"],
    "title": "Avocat pour détenu | Établissements pénitentiaires",
    "desc": "Avocat pour une personne détenue : détention provisoire, mise en liberté, discipline et aménagement de peine. Fleury-Mérogis, Fresnes, La Santé et ailleurs.",
    "h1_plain": "Défendre une personne détenue", "body": etab_body,
}

# ---------------------------------------------------------------------------- Fleury-Mérogis
FLEURY = {
    "slug": "avocat-centre-penitentiaire-fleury-merogis", "nav": "Fleury-Mérogis",
    "crumbs": [("Établissements pénitentiaires", "etablissements-penitentiaires")], "nav_slug": "etablissements-penitentiaires",
    "schema": "Service", "area": "Essonne", "prio": "P1", "cote": "Établissement · Essonne (91)",
    "kw": "avocat Fleury-Mérogis",
    "kw2": ["avocat centre pénitentiaire de Fleury-Mérogis", "avocat aménagement de peine Fleury-Mérogis", "avocat détenu Fleury-Mérogis"],
    "title": "Avocat Fleury-Mérogis | Détenus et aménagement de peine",
    "desc": "Avocat pour un détenu du centre pénitentiaire de Fleury-Mérogis : mise en liberté, discipline, aménagement de peine devant le juge d'Évry-Courcouronnes.",
    "h1": "Avocat au centre pénitentiaire <em>de Fleury-Mérogis</em>",
    "h1_plain": "Avocat au centre pénitentiaire de Fleury-Mérogis",
    "intro": "Un proche est détenu à Fleury-Mérogis&nbsp;? <strong>Me Charles Culioli-Paoli</strong> se rend au centre pénitentiaire pour rencontrer les personnes qu'il défend, qu'elles soient en détention provisoire ou condamnées.",
    "aside": [("Établissement", "Centre pénitentiaire de Fleury-Mérogis" + T), ("Juge de l'application des peines", "Tribunal judiciaire d'Évry-Courcouronnes"),
              ("Cour d'appel", "Paris")],
    "sections": [
        ("etablissement", "L'établissement", """<p>Ouvert à la fin des années 1960, le centre pénitentiaire de Fleury-Mérogis, en Essonne, est souvent présenté comme le plus grand établissement pénitentiaire d'Europe. Il regroupe plusieurs structures, dont une maison d'arrêt pour hommes, une maison d'arrêt pour femmes et un quartier pour les jeunes détenus.<span class="todo">à vérifier</span></p>
<p>Il accueille principalement des personnes en détention provisoire et des personnes condamnées à de courtes peines, dont beaucoup relèvent des juridictions de Paris et de l'Essonne.</p>"""),
        ("visite", "Rencontrer une personne détenue", """<h3>Personne en détention provisoire</h3>
<p>L'avocat obtient un permis de communiquer délivré par le magistrat en charge du dossier, généralement le juge d'instruction. Il peut ensuite rencontrer la personne au parloir avocat, sans limitation de fréquence et hors de la présence d'un surveillant.</p>
<h3>Personne condamnée</h3>
<p>L'avocat se présente avec sa carte professionnelle et le justificatif de sa désignation. L'entretien est confidentiel.</p>
<p class="ref">Horaires et modalités pratiques des parloirs avocats à vérifier auprès de l'établissement avant publication.</p>"""),
        ("demarches", "Les démarches que l'avocat peut engager", """<ol class="steps">
<li><div><b>Demande de mise en liberté</b><span>Adressée au juge d'instruction, avec un appel possible devant la chambre de l'instruction de la cour d'appel.</span></div></li>
<li><div><b>Commission de discipline</b><span>La personne détenue peut être assistée par un avocat lorsqu'elle est poursuivie devant la commission de discipline de l'établissement.</span></div></li>
<li><div><b>Aménagement de peine</b><span>Requête devant le juge de l'application des peines du tribunal judiciaire d'Évry-Courcouronnes, compétent pour l'établissement.</span></div></li>
<li><div><b>Libération conditionnelle et permissions de sortir</b><span>Préparation du projet de sortie avec le service pénitentiaire d'insertion et de probation.</span></div></li>
</ol>"""),
        ("proches", "Informations pour les proches", """<p>Les proches peuvent contacter le cabinet pour organiser la défense. Le permis de visite familial relève d'une procédure distincte : il est délivré par le magistrat saisi du dossier pour une personne prévenue, et par le chef d'établissement pour une personne condamnée.</p>
<p>Sources : <a href="https://www.justice.fr/" rel="noopener" target="_blank">justice.fr</a> et <a href="https://www.justice.gouv.fr/" rel="noopener" target="_blank">ministère de la Justice</a>.</p>"""),
    ],
    "faq": [
        ("Quel juge est compétent pour un aménagement de peine à Fleury-Mérogis&nbsp;?", "Pour une personne détenue à Fleury-Mérogis, le juge de l'application des peines compétent est celui du tribunal judiciaire d'Évry-Courcouronnes, juridiction dans le ressort de laquelle se trouve l'établissement."),
        ("L'avocat peut-il rendre visite à un détenu sans limite&nbsp;?", "L'avocat peut communiquer librement avec la personne qu'il défend. Pour une personne en détention provisoire, il doit disposer d'un permis de communiquer délivré par le magistrat saisi."),
    ],
    "related": [("avocat-amenagement-de-peine", "Aménagement de peine", "Bracelet, semi-liberté, libération conditionnelle"),
                ("avocat-penaliste-essonne", "Avocat pénaliste en Essonne", "Le tribunal d'Évry-Courcouronnes"),
                ("etablissements-penitentiaires", "Autres établissements", "Fresnes, La Santé, Réau, Villepinte")],
    "body": expertise_body,
}

LIEUX = [INTERVENTIONS, ESSONNE, ETABLISSEMENTS, FLEURY]
