# -*- coding: utf-8 -*-
"""Contenus de la maquette. Toute information biographique provient de la fiche Justifit
et reste marquée « à confirmer » tant que Me Culioli-Paoli ne l'a pas validée."""
from pages_expertises import EXPERTISES, GAV
from pages_lieux import LIEUX

SITE = {
    "url": "https://www.culioli-paoli-avocat.fr",  # domaine provisoire
    "tel": "+33600000000",                          # numéro provisoire
    "tel_display": "06 00 00 00 00",
    "whatsapp": "https://wa.me/33600000000",
    "address": "264 rue du Faubourg Saint-Honoré, 75008 Paris",
}

T = '<span class="todo">à confirmer</span>'

from home import home_body  # noqa: E402

SOMM = {
    "avocat-penaliste-paris": "Personnes mises en cause et victimes, de l'enquête à l'audience.",
    "avocat-droit-penal-des-affaires": "Abus de biens sociaux, blanchiment, fraude fiscale, corruption.",
    "avocat-extradition": "Mandat d'arrêt européen et demandes d'extradition.",
    "avocat-droit-penal-international": "Notice rouge Interpol, entraide pénale, dossiers transfrontières.",
    "avocat-amenagement-de-peine": "Bracelet électronique, semi-liberté, libération conditionnelle.",
    "avocat-cybercriminalite": "Cyberattaques, fraude informatique, escroqueries en ligne.",
    "avocat-plaidoirie-penale": "Tribunal correctionnel, cour criminelle départementale, cour d'assises.",
}
for _x in EXPERTISES:
    _x["somm"] = SOMM[_x["slug"]]

HOME = {
    "slug": "index", "nav": "Accueil", "prio": "P1", "schema_label": "LegalService, Person",
    "kw": "avocat pénaliste Paris", "kw2": ["Charles Culioli-Paoli", "avocat droit pénal Paris"],
    "title": "Charles Culioli-Paoli | Avocat pénaliste à Paris",
    "desc": "Me Charles Culioli-Paoli, avocat pénaliste à Paris, diplômé de Harvard Law School et de Sciences Po. Garde à vue, instruction, audience. Prendre contact.",
    "h1_plain": "Charles Culioli-Paoli, avocat pénaliste à Paris",
    "og": "charles-culioli-paoli-tribunal-paris.webp",
    "body": home_body,
}

# ---------------------------------------------------------------------------- Le cabinet
CABINET = {
    "slug": "charles-culioli-paoli-avocat", "nav": "Le cabinet", "prio": "P1", "schema_label": "ProfilePage, Person",
    "kw": "Charles Culioli-Paoli avocat", "kw2": ["Me Culioli-Paoli", "avocat Harvard Paris", "avocat pénaliste Sciences Po"],
    "title": "Charles Culioli-Paoli, avocat | Parcours et vision",
    "desc": "Le parcours de Me Charles Culioli-Paoli, avocat pénaliste à Paris : LL.M. de Harvard Law School, Master de Sciences Po, et sa vision de la défense pénale.",
    "h1_plain": "Charles Culioli-Paoli, avocat",
    "body": None,  # voir pages_cabinet.py
}

# ---------------------------------------------------------------------------- Hub expertises
EXP_HUB = {
    "slug": "expertises", "nav": "Expertises", "prio": "P1", "schema_label": "CollectionPage",
    "kw": "avocat droit pénal Paris", "kw2": ["expertises avocat pénaliste", "domaines d'intervention avocat"],
    "title": "Expertises en droit pénal | Me Charles Culioli-Paoli",
    "desc": "Droit pénal général, pénal des affaires, extradition, droit pénal international, aménagement de peine, cybercriminalité et plaidoirie : les expertises du cabinet.",
    "h1_plain": "Les domaines d'intervention", "body": None,
}

# ---------------------------------------------------------------------------- Ressources
RES = [
    ("Guides pratiques", [("Garde à vue : vos droits et le rôle de l'avocat", "/avocat-garde-a-vue-paris/", "Publié"),
                          ("Que faire après une convocation par la police&nbsp;?", "", "À rédiger")]),
    ("Comprendre une procédure pénale", [("Enquête, instruction, jugement : la chaîne pénale", "", "À rédiger"),
                                         ("La comparution immédiate en pratique", "", "À rédiger")]),
    ("Comprendre l'aménagement de peine", [("Aménagement de peine : conditions, procédure et délais", "/guide-amenagement-de-peine/", "Publié"),
                                           ("La libération sous contrainte", "", "À rédiger")]),
    ("Extradition et droit international", [("Mandat d'arrêt européen : les premières heures", "/avocat-extradition/", "Publié"),
                                            ("Contester une notice rouge Interpol", "", "À rédiger")]),
    ("Droit pénal des affaires", [("Perquisition dans l'entreprise : les bons réflexes", "", "À rédiger"),
                                  ("CRPC et CJIP : les issues négociées", "", "À rédiger")]),
    ("Cybercriminalité", [("Entreprise victime d'un rançongiciel : la démarche pénale", "", "À rédiger")]),
]


RESSOURCES = {
    "slug": "ressources", "nav": "Ressources", "prio": "P2", "schema_label": "CollectionPage",
    "kw": "guide procédure pénale", "kw2": ["comprendre la procédure pénale", "droits garde à vue", "aménagement de peine guide"],
    "title": "Ressources en droit pénal | Guides de Me Culioli-Paoli",
    "desc": "Guides pratiques et analyses en droit pénal : garde à vue, aménagement de peine, extradition, pénal des affaires et cybercriminalité, classés par thème.",
    "h1_plain": "Comprendre le droit pénal, thème par thème", "body": None,
}

# ---------------------------------------------------------------------------- Guide
GUIDE = {
    "slug": "guide-amenagement-de-peine", "nav": "Guide de l'aménagement de peine", "crumbs": [("Ressources", "ressources")],
    "nav_slug": "ressources", "prio": "P1", "schema": "Article",
    "kw": "aménagement de peine", "kw2": ["conditions aménagement de peine", "bracelet électronique conditions", "libération conditionnelle"],
    "title": "Aménagement de peine : conditions, procédure et délais",
    "desc": "Bracelet électronique, semi-liberté, libération conditionnelle : conditions de l'aménagement de peine, procédure devant le juge et conseils pour la demande.",
    "h1_plain": "Aménagement de peine : conditions, procédure et délais",
    "faq": [("Combien de temps dure la procédure&nbsp;?", "Les délais varient selon les juridictions et la situation de la personne. Il faut compter plusieurs semaines, parfois plusieurs mois, entre le dépôt de la requête et le débat contradictoire."),
            ("Peut-on demander un aménagement sans avocat&nbsp;?", "Oui, mais l'assistance d'un avocat permet de construire un dossier complet et de défendre le projet lors du débat devant le juge.")],
    "body": f"""<section class="page-head"><div class="wrap">{{{{CRUMBS}}}}
<p class="eyebrow">Guide · Exécution des peines</p>
<h1>Aménagement de peine : <em>conditions, procédure et délais</em></h1>
<p class="intro">Ce guide présente les principales mesures d'<strong>aménagement de peine</strong>, les conditions prévues par le Code de procédure pénale et la manière de préparer une demande devant le juge de l'application des peines.</p>
<p class="small muted" style="margin-top:1.4rem">Par Me Charles Culioli-Paoli · Mis à jour le 27 novembre 2026 · Lecture : 6 minutes</p></div></section>
<section class="section"><div class="wrap article">
<article class="prose">
<h2 id="principe" style="margin-top:0">Le principe</h2>
<p>L'aménagement de peine permet d'exécuter une peine d'emprisonnement autrement qu'en détention, ou de sortir de détention avant la fin de la peine, sous le contrôle du juge de l'application des peines. Il repose sur un projet de réinsertion et sur des obligations.</p>
<h2 id="quand">À quel moment le demander</h2>
<h3>Avant l'incarcération</h3>
<p>Depuis la réforme entrée en vigueur en mars 2020, la juridiction de jugement peut aménager elle-même les peines d'emprisonnement inférieures ou égales à un an. Lorsque la peine n'a pas été aménagée à l'audience, la personne condamnée libre peut être convoquée devant le juge de l'application des peines (article 723-15 du Code de procédure pénale).</p>
<h3>En cours de détention</h3>
<p>La personne détenue peut saisir le juge de l'application des peines compétent pour l'établissement où elle se trouve. Pour un détenu de <a href="/avocat-centre-penitentiaire-fleury-merogis/">Fleury-Mérogis</a>, il s'agit du juge du tribunal judiciaire d'Évry-Courcouronnes.</p>
<h2 id="mesures">Les mesures possibles</h2>
<div class="table-wrap"><table><thead><tr><th>Mesure</th><th>Principe</th><th>Point d'attention</th></tr></thead><tbody>
<tr><td><b>Détention à domicile sous surveillance électronique</b></td><td>La peine s'exécute au domicile, avec un bracelet et des horaires fixés.</td><td>Hébergement stable et accord de l'occupant du logement.</td></tr>
<tr><td><b>Semi-liberté</b></td><td>Activité à l'extérieur la journée, retour en établissement le soir.</td><td>Activité justifiée : emploi, formation, soins.</td></tr>
<tr><td><b>Placement à l'extérieur</b></td><td>Exécution de la peine hors de l'établissement, avec un accompagnement.</td><td>Souvent en lien avec une structure associative.</td></tr>
<tr><td><b>Libération conditionnelle</b></td><td>Sortie anticipée sous obligations, en principe à mi-peine (article 729).</td><td>Efforts sérieux de réinsertion à démontrer.</td></tr>
</tbody></table></div>
<h2 id="dossier">Préparer le dossier</h2>
<ol class="steps">
<li><div><b>Justifier d'une activité</b><span>Contrat de travail, promesse d'embauche, inscription en formation.</span></div></li>
<li><div><b>Justifier d'un hébergement</b><span>Attestation d'hébergement, bail, accord pour la surveillance électronique.</span></div></li>
<li><div><b>Montrer l'indemnisation des victimes</b><span>Versements effectués, échéancier, démarches engagées.</span></div></li>
<li><div><b>Documenter le suivi</b><span>Soins, suivi addictologique, attaches familiales.</span></div></li>
</ol>
<div class="callout">Le projet compte autant que les conditions légales : un dossier précis et vérifiable rassure le juge.<small>Pour un accompagnement, consultez la page <a href="/avocat-amenagement-de-peine/">avocat en aménagement de peine</a>.</small></div>
<h2 id="recours">Les recours</h2>
<p>Les décisions du juge de l'application des peines peuvent faire l'objet d'un appel devant la chambre de l'application des peines de la cour d'appel, dans des délais brefs fixés par le Code de procédure pénale.</p>
<p class="small muted">Sources : Code de procédure pénale, articles 707 et suivants, sur <a href="https://www.legifrance.gouv.fr/" rel="noopener" target="_blank">Légifrance</a> ; fiches pratiques sur <a href="https://www.justice.fr/" rel="noopener" target="_blank">justice.fr</a>.</p>
{{{{FAQ}}}}
</article>
<aside class="toc" aria-label="Sommaire"><p class="eyebrow">Sommaire</p><ol>
<li><a href="#principe">Le principe</a></li><li><a href="#quand">À quel moment</a></li><li><a href="#mesures">Les mesures</a></li>
<li><a href="#dossier">Préparer le dossier</a></li><li><a href="#recours">Les recours</a></li><li><a href="#faq">Questions fréquentes</a></li></ol>
<div class="aside-cta"><p class="eyebrow">Accompagnement</p><p>Préparer une requête et la défendre devant le juge.</p><a class="link" href="/avocat-amenagement-de-peine/">Avocat en aménagement de peine {{{{ARROW}}}}</a></div></aside>
</div></section>""",
}

# ---------------------------------------------------------------------------- Contact
CONTACT = {
    "slug": "contact", "nav": "Contact", "prio": "P1", "schema_label": "ContactPage",
    "kw": "contacter avocat pénaliste Paris", "kw2": ["rendez-vous avocat pénaliste", "avocat pénaliste Paris 8"],
    "title": "Contacter Me Charles Culioli-Paoli | Avocat à Paris 8e",
    "desc": "Prendre rendez-vous avec Me Charles Culioli-Paoli, avocat pénaliste à Paris 8e : formulaire, WhatsApp ou ligne d'urgence pénale pour une garde à vue.",
    "h1_plain": "Contacter le cabinet",
    "body": f"""<section class="page-head"><div class="wrap">{{{{CRUMBS}}}}
<p class="eyebrow">Contact</p><h1>Contacter <em>le cabinet</em></h1>
<p class="intro">Pour une situation urgente, utilisez la <strong>ligne d'urgence pénale</strong>. Pour toute autre demande, le formulaire ci-dessous permet d'exposer brièvement votre situation.</p>
<div class="hero-actions"><button class="urg-link" type="button" data-urgence><span class="dot" aria-hidden="true"></span>Urgence pénale</button><a class="link" href="{SITE["whatsapp"]}" target="_blank" rel="noopener">Écrire sur WhatsApp</a></div></div></section>
<section class="section"><div class="wrap article">
<div>
  <form id="contact-form" class="form-card" novalidate>
    <div class="form-grid">
      <div class="field"><label for="nom">Nom et prénom</label><input id="nom" name="nom" autocomplete="name" required><span class="err" aria-live="polite"></span></div>
      <div class="field"><label for="email">Adresse email</label><input id="email" name="email" type="email" autocomplete="email" required><span class="err" aria-live="polite"></span></div>
      <div class="field"><label for="tel">Téléphone</label><input id="tel" name="tel" type="tel" autocomplete="tel" inputmode="tel" required><span class="err" aria-live="polite"></span></div>
      <div class="field"><label for="objet">Objet de la demande</label><select id="objet" name="objet" required><option value="">Choisir</option><option>Premier rendez-vous</option><option>Convocation reçue</option><option>Proche détenu</option><option>Dossier en cours</option><option>Autre demande</option></select><span class="err" aria-live="polite"></span></div>
      <div class="field field--full"><label for="domaine">Domaine juridique concerné</label><select id="domaine" name="domaine" required><option value="">Choisir</option><option>Droit pénal général</option><option>Droit pénal des affaires</option><option>Extradition</option><option>Droit pénal international</option><option>Aménagement de peine</option><option>Cybercriminalité</option><option>Je ne sais pas</option></select><span class="err" aria-live="polite"></span></div>
      <div class="field field--full"><label for="message">Message</label><textarea id="message" name="message" required aria-describedby="msg-help"></textarea><span class="help" id="msg-help">Décrivez la situation en quelques lignes : nature de la procédure, juridiction, échéance éventuelle. Ne joignez aucune pièce.</span><span class="err" aria-live="polite"></span></div>
      <div class="field--full confid"><b>Confidentialité.</b> Ce formulaire sert uniquement à une première prise de contact. N'y indiquez pas d'informations détaillées sur les faits ni de documents sensibles : ils seront échangés directement avec l'avocat, dans un cadre couvert par le secret professionnel.</div>
      <div class="field--full"><label class="check"><input type="checkbox" id="rgpd" name="rgpd"> <span>J'accepte que mes données soient utilisées pour être recontacté par le cabinet. Elles ne sont jamais transmises à des tiers. <a href="/mentions-legales/#donnees">Politique de confidentialité</a></span></label><span class="err" id="rgpd-err" aria-live="polite" style="display:block;color:var(--danger);font-size:var(--fs-small)"></span></div>
      <div class="field--full"><button class="btn" type="submit">Envoyer la demande</button></div>
    </div>
  </form>
  <div class="form-success" id="form-success" tabindex="-1" hidden><p class="eyebrow">Maquette</p><h2 style="font-size:var(--fs-h3);margin-top:.6rem">Demande prête à être envoyée</h2><p>Dans la maquette, aucune donnée n'est transmise. Sur le site final, le cabinet confirmera la réception par email.</p></div>
</div>
<aside class="toc" aria-label="Coordonnées"><p class="eyebrow">Cabinet</p>
  <dl class="fiche" style="grid-template-columns:1fr">
    <div><dt>Adresse</dt><dd>264 rue du Faubourg Saint-Honoré<br>75008 Paris{T}</dd></div>
    <div><dt>Second bureau</dt><dd>Massy (91){T}</dd></div>
    <div><dt>Rendez-vous</dt><dd>Au cabinet ou en visioconférence</dd></div>
    <div><dt>Langues</dt><dd>Français, anglais, espagnol, arabe, corse</dd></div>
  </dl>
  <div class="aside-cta"><p class="eyebrow">Garde à vue en cours</p><p>N'attendez pas une réponse par email.</p><button class="urg-link" type="button" data-urgence><span class="dot" aria-hidden="true"></span>Urgence pénale</button></div>
</aside>
</div></section>""",
}

# ---------------------------------------------------------------------------- Mentions légales
MENTIONS = {
    "slug": "mentions-legales", "nav": "Mentions légales", "prio": "P3", "noindex": True,
    "kw": "", "title": "Mentions légales | Charles Culioli-Paoli, avocat", "desc": "Mentions légales du site.",
    "h1_plain": "Mentions légales",
    "body": f"""<section class="page-head"><div class="wrap">{{{{CRUMBS}}}}<h1>Mentions légales</h1></div></section>
<section class="section"><div class="wrap"><div class="prose">
<h2 style="margin-top:0">Éditeur du site</h2>
<p>Me Charles Culioli-Paoli, avocat au Barreau de Paris{T}. Structure d'exercice : à compléter (exercice individuel, SELARL ou autre). Adresse : 264 rue du Faubourg Saint-Honoré, 75008 Paris{T}. Numéro de toque, SIRET et numéro de TVA intracommunautaire : à compléter.</p>
<p>Avocat soumis aux règles de la profession d'avocat : loi n° 71-1130 du 31 décembre 1971, décret n° 2005-790 du 12 juillet 2005 et Règlement intérieur national (RIN), consultables sur le site du <a href="https://www.cnb.avocat.fr/" target="_blank" rel="noopener">Conseil national des barreaux</a>.</p>
<h2>Médiation de la consommation</h2><p>En cas de litige, le client consommateur peut saisir gratuitement le Médiateur de la consommation de la profession d'avocat, après une démarche préalable écrite auprès de l'avocat.</p>
<h2>Hébergement</h2><p>Hébergeur : à compléter (raison sociale, adresse, téléphone).</p>
<h2 id="donnees">Données personnelles</h2><p>Les données transmises via le formulaire sont utilisées uniquement pour répondre à la demande et ne sont pas transmises à des tiers. Durée de conservation, base légale et droits (accès, rectification, effacement, opposition) : à compléter. Réclamation possible auprès de la CNIL.</p>
<h2 id="cookies">Cookies</h2><p>Le site utilise une mesure d'audience respectueuse de la vie privée, sans dépôt de cookie publicitaire. Bandeau de consentement à configurer si un outil soumis à consentement est ajouté.</p>
<h2>Communication de l'avocat</h2><p>Les informations publiées sont générales et ne constituent pas une consultation juridique. Elles ne préjugent pas de l'issue d'un dossier. Les domaines présentés sont des domaines d'activité et non des mentions de spécialisation.</p>
</div></div></section>""",
}

from pages_cabinet import CABINET_BODY, HONORAIRES, expertises_hub as _eh, ressources_body as _rb  # noqa: E402
CABINET["body"] = CABINET_BODY
EXP_HUB["body"] = _eh
RESSOURCES["body"] = lambda p: _rb(p, RES)

PAGES = [HOME, CABINET, EXP_HUB] + EXPERTISES + [GAV] + LIEUX + [RESSOURCES, GUIDE, HONORAIRES, CONTACT, MENTIONS]
