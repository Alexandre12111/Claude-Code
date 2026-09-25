#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Générateur de la maquette Aurea Media (Python 3, aucune dépendance).

    python3 build.py

Lit src/pages/*.html (en-tête JSON + contenu), remplace les composants
<!-- @nom clé="valeur" -->, ajoute l'en-tête, le pied de page, les métadonnées
et le JSON-LD, puis écrit les pages HTML à la racine de la maquette.

Les liens des sources sont écrits comme en production (/creation-de-site-vitrine-paris/).
Pour que la maquette s'ouvre d'un double clic, ils sont réécrits vers les
fichiers locaux ; une page non incluse dans la maquette pointe vers le site actuel.
Les balises canonical, Open Graph, JSON-LD et le sitemap gardent les vraies URL.

Produit aussi : sitemap.xml, robots.txt, llms.txt, redirections-301.htaccess
et SEO-PAGES.md (contrôle des règles Rank Math page par page).
"""
import datetime
import glob
import html
import json
import os
import re
import sys
import unicodedata

ROOT = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, os.path.join(ROOT, "src"))

import components as C  # noqa: E402
from data import SITE, BRIEF, SERVICES, PLANS, PROJECTS, POSTS  # noqa: E402

BASE = SITE["url"]
TODAY = datetime.date.today().isoformat()
E = html.escape


# ---------------------------------------------------------------------------
# Lecture des pages
# ---------------------------------------------------------------------------
def load_pages():
    pages = []
    for f in sorted(glob.glob(os.path.join(ROOT, "src", "pages", "*.html"))):
        raw = open(f, encoding="utf-8").read()
        m = re.match(r"\s*<!--(\{.*?\})-->\s*", raw, re.S)
        if not m:
            raise SystemExit(f"En-tête JSON manquant : {f}")
        meta = json.loads(m.group(1))
        meta["body"] = raw[m.end():]
        meta["src"] = os.path.basename(f)
        pages.append(meta)
    return pages


def out_name(path):
    if path == "/":
        return "index.html"
    if path == "/404/":
        return "404.html"
    return path.strip("/").replace("/", "--") + ".html"


# ---------------------------------------------------------------------------
# Composants <!-- @nom a="b" -->
# ---------------------------------------------------------------------------
def parse_args(s):
    return {k: v for k, v in re.findall(r'([\w-]+)="([^"]*)"', s)}


def listarg(v):
    return [x.strip() for x in v.split(",") if x.strip()] if v else None


def component(name, a, page):
    if name == "header":
        return C.header(page.get("section", ""))
    if name == "brief":
        return C.brief_block()
    if name == "marquee":
        return C.marquee()
    if name == "services":
        return C.services_rows(listarg(a.get("ids")))
    if name == "service-cards":
        return C.service_cards(a.get("exclude"), listarg(a.get("ids")))
    if name == "projects-h":
        return C.projects_h(listarg(a["ids"]))
    if name == "projects-grid":
        return C.projects_grid()
    if name == "project-pair":
        return C.project_pair(listarg(a["ids"]))
    if name == "stats":
        return C.stats()
    if name == "steps":
        return C.steps(a.get("set", "site"))
    if name == "plans":
        return C.plans(a["set"])
    if name == "assure":
        return C.assure(a["items"].split("|") if a.get("items") else None)
    if name == "reviews":
        return C.reviews_block()
    if name == "posts":
        return C.posts(listarg(a.get("slugs")), a.get("cat"), int(a.get("limit", 3)))
    if name == "posts-all":
        return C.posts_all()
    if name == "faq":
        return C.faq(FAQS[a["set"]], a.get("title", "Questions fréquentes"), a.get("idx", "09"), a.get("lede", ""))
    if name == "cta":
        kw = {}
        if a.get("title"):
            kw["title"] = a["title"]
        if a.get("text"):
            kw["text"] = a["text"]
        return C.cta(**kw)
    if name == "founder":
        return C.founder_block(quote=a.get("quote", "1") == "1", idx=a.get("idx", "06"))
    if name == "estimator":
        return C.estimator()
    if name == "contact-form":
        return C.contact_form(a.get("preset"), idp=a.get("id", "f"))
    if name == "contact-section":
        kw = {"preset": a.get("preset"), "idx": a.get("idx", "10")}
        if a.get("title"):
            kw["title"] = a["title"]
        if a.get("text"):
            kw["text"] = a["text"]
        return C.contact_section(**kw)
    if name == "before-after":
        return C.before_after(a.get("before", "avant-aurea"), a.get("after", "apres-aurea"), a.get("alt_before", ""), a.get("alt_after", ""), a.get("caption", ""))
    if name == "slots":
        return C.slot_picker()
    if name == "spiral":
        return C.golden_spiral(a.get("cls", "phero__spiral golden-svg"))
    if name == "crumbs":
        return C.crumbs(page["crumbs"])
    if name == "icon":
        return C.icon(a["name"], a.get("cls", ""))
    if name == "stars":
        return C.stars()
    if name == "logo":
        return C.logo_mark(a.get("cls", "brand__mark"), a.get("uid", "x"))
    if name == "site":
        return E(str(SITE[a["key"]]))
    if name == "brief-text":
        return E(BRIEF)
    raise SystemExit(f"Composant inconnu : {name} ({page['src']})")


def render_components(body, page):
    def rep(m):
        return component(m.group(1), parse_args(m.group(2) or ""), page)
    # Deux passes : un composant peut en contenir d'autres
    for _ in range(2):
        body = re.sub(r"<!--\s*@([\w-]+)((?:\s+[\w-]+=\"[^\"]*\")*)\s*-->", rep, body)
    return body


# ---------------------------------------------------------------------------
# FAQ (texte visible ; aucun balisage FAQPage : plus de résultat enrichi depuis mai 2026)
# ---------------------------------------------------------------------------
L = lambda href, text: f'<a href="{href}">{text}</a>'  # noqa: E731
FAQS = {
    "home": [
        ("Combien coûte la création d'un site internet à Paris ?",
         f"Chez Aurea Media, un site vitrine sur mesure démarre à 1 000 € HT (3 pages) et un site e-commerce à 1 650 € HT. Le prix dépend du nombre de pages, des fonctionnalités et des contenus à produire. Toutes les formules sont détaillées sur la page {L('/prix-site-internet-paris/', 'tarifs')}, et chaque projet reçoit un devis gratuit et détaillé sous 24 h."),
        ("Combien de temps faut-il pour créer un site web ?",
         "Comptez 4 semaines en moyenne pour un site vitrine : une semaine d'audit, deux semaines de design et de développement, une semaine de SEO et de mise en ligne. Une boutique en ligne demande 6 à 8 semaines selon la taille du catalogue."),
        ("Le référencement Google est-il inclus ?",
         "Oui, sans supplément : structure des balises, données structurées, Core Web Vitals, maillage interne, sitemap et Search Console sont compris dans chaque site. Pour aller plus loin, l'offre " + L('/seo-local-paris/', 'SEO local') + " travaille la fiche Google, les avis et les pages locales."),
        ("Le site m'appartient-il vraiment ?",
         "Oui. Le nom de domaine, l'hébergement, les fichiers et les accès administrateur sont à votre nom dès la livraison. Le site est développé sous WordPress, sans plateforme propriétaire : vous pouvez le confier à un autre prestataire à tout moment."),
        ("Puis-je modifier mon site moi-même ?",
         "Oui. Une formation d'une heure vous rend autonome pour modifier les textes, les photos et publier des articles. Pour les évolutions techniques, Alexandre reste joignable après la livraison."),
        ("Travaillez-vous en dehors de Paris ?",
         "Oui. L'agence accompagne des clients partout en France et à l'international, de Genève à Lisbonne, en visioconférence et par téléphone, avec le même suivi qu'en rendez-vous. En Île-de-France, un rendez-vous peut être organisé sur demande."),
    ],
    "services": [
        ("Quelle différence entre un site vitrine et un site e-commerce ?",
         "Un site vitrine présente votre activité et génère des demandes de contact ou de devis. Un site e-commerce permet de vendre et d'encaisser en ligne, avec panier, paiement et gestion des commandes. Si vous vendez un service, le site vitrine suffit presque toujours."),
        ("Quel budget prévoir pour un site professionnel ?",
         f"Dès 1 000 € HT pour un site vitrine de 3 pages, 1 800 € HT pour 5 à 7 pages avec blog, 1 650 € HT pour une boutique en ligne. Le {L('/prix-site-internet-paris/#simulateur', 'simulateur de budget')} indique la formule adaptée en trente secondes."),
        ("Faut-il refaire son site ou repartir de zéro ?",
         f"Si le site est lent, difficile à modifier ou ne génère plus de demandes, une {L('/refonte-site-internet-paris/', 'refonte')} est souvent plus rentable qu'une série de corrections. Elle se fait en conservant les pages qui apportent du trafic, grâce à un plan de redirections."),
        ("Proposez-vous la maintenance ?",
         "Oui. Mises à jour, sauvegardes et sécurité sont assurées après la mise en ligne, sans abonnement imposé. Le périmètre est précisé dans le devis."),
    ],
    "vitrine": [
        ("Combien coûte un site vitrine à Paris ?",
         f"Chez Aurea Media, un site vitrine coûte à partir de 1 000 € HT pour 3 pages (formule Essentiel) et 1 800 € HT pour 5 à 7 pages avec blog (formule Professionnel). Au delà de 8 pages ou pour un site bilingue, le prix est établi sur devis. Le guide {L('/prix-site-vitrine-paris/', 'prix d’un site vitrine à Paris')} détaille les prix du marché."),
        ("En combien de temps mon site vitrine sera-t-il en ligne ?",
         "3 semaines pour la formule Essentiel, 4 semaines pour la formule Professionnel. Le calendrier est fourni au démarrage ; le délai dépend surtout de la remise des textes et des photos."),
        ("Dois-je fournir les textes ?",
         "Vous pouvez les fournir, ou profiter de la rédaction assistée incluse dans la formule Professionnel : Alexandre structure vos contenus et les optimise pour les recherches de vos clients."),
        ("Quels frais après la mise en ligne ?",
         "Le nom de domaine et l'hébergement sont à votre nom et facturés directement par l'hébergeur. La maintenance technique est proposée sans abonnement imposé ; son périmètre figure dans le devis."),
        ("Pouvez-vous créer un site pour une profession réglementée ?",
         f"Oui : avocats, médecins, kinésithérapeutes, architectes. Chaque site respecte les règles déontologiques de la profession. Les guides {L('/site-vitrine-avocat/', 'avocat')} et {L('/site-vitrine-medecin/', 'médecin')} détaillent ces contraintes."),
    ],
    "ecommerce": [
        ("Combien coûte un site e-commerce à Paris ?",
         f"Une boutique WooCommerce sur mesure démarre à 1 650 € HT (jusqu'à 30 produits) et 3 200 € HT pour un catalogue illimité avec Google Shopping. Les projets B2B ou connectés à un ERP sont sur devis. Le guide {L('/combien-coute-un-site-e-commerce/', 'combien coûte un site e-commerce')} compare les options."),
        ("Pourquoi WooCommerce plutôt que Shopify ?",
         f"WooCommerce ne prélève aucune commission sur vos ventes et n'impose aucun abonnement : la boutique et le fichier client vous appartiennent. Shopify est plus rapide à lancer mais coûte plus cher quand les ventes progressent. Le comparatif {L('/shopify-vs-woocommerce/', 'Shopify ou WooCommerce')} détaille les coûts."),
        ("Quels moyens de paiement ?",
         "Carte bancaire via Stripe, PayPal, virement, et selon les besoins paiement en plusieurs fois. Les frais sont ceux des prestataires de paiement, sans commission supplémentaire."),
        ("Serai-je autonome pour gérer mes produits ?",
         "Oui. Une formation couvre l'ajout de produits, les stocks, les commandes et les codes promo. Le catalogue initial peut être importé pour vous."),
    ],
    "refonte": [
        ("Vais-je perdre mon référencement avec une refonte ?",
         "Non, si la refonte est préparée. Chaque URL qui reçoit du trafic ou des liens est conservée ou redirigée en 301 vers sa nouvelle version, les balises sont reprises et améliorées, puis les positions sont suivies pendant 8 semaines après la bascule."),
        ("Mon site sera-t-il coupé pendant la refonte ?",
         "Non. Le nouveau site est construit sur un environnement de préproduction. L'ancien reste en ligne jusqu'à la bascule, qui prend quelques minutes."),
        ("Combien coûte une refonte de site ?",
         f"Le prix dépend du nombre de pages à reprendre et des fonctionnalités. Une refonte suit les mêmes formules qu'une création (dès 1 000 € HT pour un site vitrine) ; le devis précise ce qui est repris et ce qui est créé. Voir les {L('/prix-site-internet-paris/', 'tarifs')}."),
        ("Pouvez-vous reprendre un site Wix, Webflow ou Elementor ?",
         "Oui. Les contenus sont migrés vers WordPress, les URL sont conservées quand c'est possible, et le nouveau site est développé sans constructeur de pages pour être plus rapide."),
    ],
    "seo": [
        ("Combien de temps pour voir des résultats en SEO local ?",
         "Les premiers effets sur la fiche Google apparaissent souvent en quelques semaines. Pour des positions stables sur des requêtes concurrentielles, comptez 3 à 6 mois selon votre secteur et votre quartier."),
        ("Qu'est-ce que le SEO local ?",
         "Le SEO local regroupe les actions qui font apparaître une entreprise dans les résultats géolocalisés de Google : le pack local de Google Maps, les recherches « près de moi » et les requêtes avec une ville. Il repose sur la fiche Google Business Profile, les avis, la cohérence des coordonnées et des pages locales utiles."),
        ("Et la visibilité dans ChatGPT ou les AI Overviews ?",
         "Les moteurs de réponse citent les sources claires, cohérentes et vérifiables. Nous travaillons la définition de votre entreprise, des réponses autonomes dans vos pages, les données structurées et vos mentions sur des sites tiers."),
        ("Faut-il un engagement ?",
         "Non. L'accompagnement mensuel se poursuit tant qu'il produit des résultats mesurables, avec un rapport clair chaque mois."),
    ],
    "branding": [
        ("Combien coûte la création d'un logo à Paris ?",
         "Chaque identité fait l'objet d'un devis gratuit sous 24 h, selon l'étendue de la charte et le nombre de déclinaisons. La formule Identité complète comprend logo, couleurs, typographies, guide de marque et déclinaisons commerciales."),
        ("Combien de propositions vais-je recevoir ?",
         "Trois pistes créatives différentes, présentées et argumentées. Les retouches sont illimitées sur la piste retenue."),
        ("Suis-je propriétaire du logo ?",
         "Oui. La cession de droits est totale, exclusive et sans limite de durée, actée par écrit. Les fichiers sources (SVG, AI, PDF, PNG) vous sont remis."),
    ],
    "maquettes": [
        ("Quel délai pour un pitch deck ?",
         "Une semaine pour un support de 10 à 30 slides, 3 à 5 jours pour un support court. Le calendrier est fixé au briefing."),
        ("Pourrai-je modifier la présentation moi-même ?",
         "Oui. Les fichiers sont livrés en PowerPoint, Keynote, Google Slides ou Canva, 100 % éditables, avec vos polices et vos couleurs."),
        ("Pouvez-vous aussi écrire le contenu ?",
         "La structure narrative est construite avec vous : ordre des arguments, messages clés, chiffres à mettre en avant. Vous apportez le fond, nous le rendons lisible et convaincant."),
    ],
    "tarifs": [
        ("Les prix sont-ils HT ou TTC ?",
         "Tous les prix affichés sont hors taxes. Le devis indique le montant TTC applicable à votre situation."),
        ("Y a-t-il des frais cachés ?",
         "Non. Le devis est détaillé ligne par ligne et il engage : aucun supplément n'est facturé sans votre accord écrit préalable. Seuls le nom de domaine et l'hébergement sont payés directement à l'hébergeur, à votre nom."),
        ("Comment se passe le paiement ?",
         "Un acompte au démarrage, puis le solde à la mise en ligne. L'échéancier précis figure dans le devis."),
        ("Pourquoi un site coûte-t-il plus de 500 € ?",
         "En dessous de ce prix, il s'agit presque toujours d'un modèle préfabriqué, sans stratégie ni référencement. Un site sur mesure inclut l'audit, le design, le développement, le SEO technique et la formation : c'est ce qui le rend rentable."),
    ],
    "local": [
        ("Vous déplacez-vous dans les Hauts-de-Seine ?",
         "Oui. Les rendez-vous en présentiel sont possibles sur demande à Boulogne-Billancourt, Issy-les-Moulineaux, Clamart et dans tout le 92. La plupart des échanges se font en visioconférence, avec le même suivi."),
        ("Un site local coûte-t-il plus cher ?",
         f"Non. Les formules sont identiques partout : site vitrine dès 1 000 € HT, e-commerce dès 1 650 € HT. Voir les {L('/prix-site-internet-paris/', 'tarifs')}."),
        ("Comment apparaître dans Google Maps dans ma ville ?",
         f"Grâce à une fiche Google Business Profile complète, des avis réguliers et des pages qui parlent de votre zone d'intervention. L'offre {L('/seo-local-paris/', 'SEO local')} s'en charge."),
    ],
    "contact": [
        ("Le devis est-il vraiment gratuit ?",
         "Oui, gratuit et sans engagement. Vous recevez une première analyse et un devis détaillé ligne par ligne sous 24 h ouvrées."),
        ("Qui me répond ?",
         "Alexandre Coury, fondateur de l'agence, qui construira votre site. Jamais un commercial ni un message automatique."),
        ("Que dois-je préparer avant le premier échange ?",
         "Rien d'obligatoire. Si vous les avez : quelques sites que vous aimez, vos concurrents, votre logo et une idée de budget."),
    ],
    "about": [
        ("Qui travaille concrètement sur mon projet ?",
         "Alexandre Coury, fondateur, prend en charge chaque projet : stratégie, design, développement et référencement. Aucune sous-traitance, aucun intermédiaire."),
        ("Que se passe-t-il si Alexandre est indisponible ?",
         "Vous détenez tous les accès, le code est documenté et développé sous WordPress, standard du marché. Votre site ne dépend jamais d'une seule personne pour continuer à fonctionner."),
        ("Quels types d'entreprises accompagnez-vous ?",
         "Des indépendants, des artisans, des professions libérales, des TPE, des PME et des associations : BTP, industrie, santé, droit, immobilier, commerce, conseil."),
    ],
}


# ---------------------------------------------------------------------------
# Données structurées (un seul @graph cohérent, @id stables)
# ---------------------------------------------------------------------------
ORG_ID = BASE + "/#organization"
WEB_ID = BASE + "/#website"
PERSON_ID = BASE + "/#alexandre-coury"
LOGO = BASE + "/wp-content/uploads/aurea-media-logo.png"


def org_node():
    return {
        "@type": "ProfessionalService",
        "@id": ORG_ID,
        "name": "Aurea Media",
        "description": BRIEF,
        "url": BASE + "/",
        "logo": {"@type": "ImageObject", "url": LOGO, "width": 512, "height": 512},
        "image": LOGO,
        "telephone": SITE["phone_href"],
        "email": SITE["email"],
        "priceRange": "€€",
        "currenciesAccepted": "EUR",
        "identifier": {"@type": "PropertyValue", "propertyID": "SIREN", "value": SITE["siren"].replace(" ", "")},
        "address": {"@type": "PostalAddress", "streetAddress": SITE["street"], "postalCode": SITE["zip"],
                    "addressLocality": SITE["city"], "addressRegion": "Île-de-France", "addressCountry": "FR"},
        "areaServed": [{"@type": "City", "name": "Paris"}, {"@type": "AdministrativeArea", "name": "Hauts-de-Seine"},
                       {"@type": "AdministrativeArea", "name": "Île-de-France"}, {"@type": "Country", "name": "France"}],
        "contactPoint": {"@type": "ContactPoint", "contactType": "customer service", "telephone": SITE["phone_href"],
                         "email": SITE["email"], "availableLanguage": ["fr", "en"],
                         "hoursAvailable": {"@type": "OpeningHoursSpecification",
                                            "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
                                            "opens": "09:00", "closes": "19:00"}},
        "founder": {"@id": PERSON_ID},
        "knowsAbout": ["Création de site internet", "WordPress", "WooCommerce", "Référencement naturel", "SEO local",
                       "Identité visuelle", "Generative Engine Optimization"],
        "sameAs": [SITE["linkedin_company"], SITE["gbp"]],
    }


def person_node():
    return {
        "@type": "Person", "@id": PERSON_ID, "name": "Alexandre Coury",
        "jobTitle": "Fondateur, développeur web et consultant SEO",
        "worksFor": {"@id": ORG_ID}, "url": BASE + "/a-propos/",
        "image": BASE + "/wp-content/uploads/alexandre-coury.jpg",
        "knowsAbout": ["WordPress", "WooCommerce", "SEO technique", "SEO local", "UX design"],
        "sameAs": [SITE["linkedin_person"]],
    }


def breadcrumb_node(page, url):
    items = []
    for i, (name, href) in enumerate(page["crumbs"]):
        items.append({"@type": "ListItem", "position": i + 1, "name": name, "item": BASE + href})
    return {"@type": "BreadcrumbList", "@id": url + "#breadcrumb", "itemListElement": items}


def service_node(page, url):
    s = next(x for x in SERVICES if x["id"] == page["service"])
    node = {
        "@type": "Service", "@id": url + "#service", "name": page["h1_plain"],
        "serviceType": s["name"], "description": page["description"], "url": url,
        "provider": {"@id": ORG_ID},
        "areaServed": [{"@type": "City", "name": "Paris"}, {"@type": "AdministrativeArea", "name": "Île-de-France"},
                       {"@type": "Country", "name": "France"}],
    }
    plans = PLANS.get(page["service"])
    if plans:
        offers = []
        for p in plans:
            o = {"@type": "Offer", "name": p["name"], "description": "; ".join(p["items"]), "url": url + "#formules",
                 "seller": {"@id": ORG_ID}}
            if p["price"]:
                o["priceSpecification"] = {"@type": "PriceSpecification", "minPrice": p["price"],
                                           "priceCurrency": "EUR", "valueAddedTaxIncluded": False}
            offers.append(o)
        node["hasOfferCatalog"] = {"@type": "OfferCatalog", "name": "Formules " + s["short"], "itemListElement": offers}
    elif s["price"]:
        node["offers"] = {"@type": "Offer", "priceSpecification": {"@type": "PriceSpecification", "minPrice": s["price"],
                                                                     "priceCurrency": "EUR", "valueAddedTaxIncluded": False}}
    return node


def schema(page):
    url = BASE + page["path"]
    t = page.get("type", "page")
    page_type = {"about": "AboutPage", "contact": "ContactPage", "collection": "CollectionPage",
                 "blog": "CollectionPage", "article": "WebPage"}.get(t, "WebPage")
    webpage = {
        "@type": page_type, "@id": url + "#webpage", "url": url, "name": page["title"],
        "description": page["description"], "isPartOf": {"@id": WEB_ID}, "inLanguage": "fr-FR",
        "dateModified": page.get("modified", TODAY),
    }
    if page.get("crumbs"):
        webpage["breadcrumb"] = {"@id": url + "#breadcrumb"}
    graph = [org_node(), {"@type": "WebSite", "@id": WEB_ID, "url": BASE + "/", "name": "Aurea Media",
                          "description": "Agence web indépendante à Paris", "publisher": {"@id": ORG_ID}, "inLanguage": "fr-FR"}]
    if t == "home":
        webpage["about"] = {"@id": ORG_ID}
        graph.append(person_node())
    if t in ("about",):
        webpage["about"] = {"@id": ORG_ID}
        webpage["mainEntity"] = {"@id": PERSON_ID}
        graph.append(person_node())
    if t == "contact":
        webpage["mainEntity"] = {"@id": ORG_ID}
    if t == "service":
        webpage["about"] = {"@id": url + "#service"}
        graph.append(service_node(page, url))
    if t == "hub":
        webpage["hasPart"] = [{"@type": "Service", "@id": BASE + s["path"] + "#service", "name": s["name"], "url": BASE + s["path"],
                               "provider": {"@id": ORG_ID}} for s in SERVICES]
    if t == "pricing":
        catalog = []
        for key, label in (("vitrine", "Site vitrine"), ("ecommerce", "Site e-commerce")):
            for p in PLANS[key]:
                o = {"@type": "Offer", "name": f"{label} {p['name']}", "seller": {"@id": ORG_ID}}
                if p["price"]:
                    o["priceSpecification"] = {"@type": "PriceSpecification", "minPrice": p["price"], "priceCurrency": "EUR", "valueAddedTaxIncluded": False}
                catalog.append(o)
        graph.append({"@type": "OfferCatalog", "@id": url + "#tarifs", "name": "Tarifs Aurea Media", "itemListElement": catalog})
        webpage["mainEntity"] = {"@id": url + "#tarifs"}
    if t == "collection":
        webpage["mainEntity"] = {"@type": "ItemList", "numberOfItems": len(PROJECTS), "itemListElement": [
            {"@type": "ListItem", "position": i + 1, "item": {"@type": "WebSite", "name": p["name"], "url": p["url"],
                                                              "creator": {"@id": ORG_ID}}} for i, p in enumerate(PROJECTS)]}
    if t == "blog":
        webpage["mainEntity"] = {"@type": "ItemList", "numberOfItems": len(POSTS), "itemListElement": [
            {"@type": "ListItem", "position": i + 1, "url": f"{BASE}/{p['slug']}/", "name": p["title"]} for i, p in enumerate(POSTS)]}
    if t == "article":
        graph.append(person_node())
        graph.append({
            "@type": "BlogPosting", "@id": url + "#article", "headline": page["h1_plain"], "description": page["description"],
            "mainEntityOfPage": {"@id": url + "#webpage"}, "author": {"@id": PERSON_ID}, "publisher": {"@id": ORG_ID},
            "datePublished": page["published"], "dateModified": page.get("modified", TODAY), "inLanguage": "fr-FR",
            "image": BASE + "/wp-content/uploads/og/" + out_name(page["path"]).replace(".html", ".jpg"),
            "articleSection": page.get("section_name", "Conseils"), "wordCount": page.get("_words", 0),
        })
    if t == "case":
        p = next(x for x in PROJECTS if x["id"] == page["project"])
        graph.append({"@type": "Article", "@id": url + "#case", "headline": page["h1_plain"], "description": page["description"],
                      "author": {"@id": PERSON_ID}, "publisher": {"@id": ORG_ID}, "mainEntityOfPage": {"@id": url + "#webpage"},
                      "datePublished": page.get("published", TODAY), "dateModified": page.get("modified", TODAY),
                      "about": {"@type": "WebSite", "name": p["name"], "url": p["url"], "creator": {"@id": ORG_ID}}})
    if t == "local":
        webpage["about"] = {"@id": ORG_ID}
        webpage["spatialCoverage"] = {"@type": "AdministrativeArea", "name": "Hauts-de-Seine"}
    if page.get("crumbs"):
        graph.append(breadcrumb_node(page, url))
    graph.insert(2, webpage)
    return json.dumps({"@context": "https://schema.org", "@graph": graph}, ensure_ascii=False, indent=1)


# ---------------------------------------------------------------------------
# Gabarit
# ---------------------------------------------------------------------------
GATE = ("(function(d){var h=d.documentElement;h.classList.add('js');"
        "if(!matchMedia('(prefers-reduced-motion: reduce)').matches){h.classList.add('js-anim');"
        "setTimeout(function(){if(!window.__aureaReady)h.classList.remove('js-anim')},3000)}})(document);")


def layout(page, body):
    url = BASE + page["path"]
    robots = page.get("robots", "index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1")
    og = BASE + "/wp-content/uploads/og/" + out_name(page["path"]).replace(".html", ".jpg")
    needs_gl = "data-gl" in body
    needs_flip = "data-filters" in body
    scripts = ['<script src="/assets/vendor/gsap.min.js" defer></script>',
               '<script src="/assets/vendor/ScrollTrigger.min.js" defer></script>',
               '<script src="/assets/vendor/SplitText.min.js" defer></script>']
    if needs_flip:
        scripts.append('<script src="/assets/vendor/Flip.min.js" defer></script>')
    scripts += ['<script src="/assets/vendor/lenis.min.js" defer></script>',
                '<script src="/assets/js/aurea.js" defer></script>']
    if needs_gl:
        scripts.append('<script src="/assets/js/aurea-gl.js" defer></script>')
    canonical = "" if page.get("type") == "404" else f'<link rel="canonical" href="{url}">'
    return f'''<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>{E(page["title"])}</title>
<meta name="description" content="{E(page["description"])}">
<meta name="robots" content="{robots}">
{canonical}
<meta property="og:locale" content="fr_FR">
<meta property="og:type" content="{"article" if page.get("type") == "article" else "website"}">
<meta property="og:site_name" content="Aurea Media">
<meta property="og:title" content="{E(page.get("og_title", page["title"]))}">
<meta property="og:description" content="{E(page["description"])}">
<meta property="og:url" content="{url}">
<meta property="og:image" content="{og}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="{E(page.get("og_alt", page["h1_plain"]))}">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#0b0a08">
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/assets/img/apple-touch-icon.png">
<link rel="preload" href="/assets/fonts/instrument-serif-latin-400-normal.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/instrument-serif-latin-400-italic.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/geist-latin-wght-normal.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/assets/css/aurea.min.css">
<script>{GATE}</script>
<script type="application/ld+json">
{schema(page)}
</script>
</head>
<body class="{page.get("body_class", "")}">
{C.header(page.get("section", ""))}
<main id="contenu">
{body}
</main>
{C.footer()}
<div class="grain" aria-hidden="true"></div>
{chr(10).join(scripts)}
</body>
</html>
'''


# ---------------------------------------------------------------------------
# Réécriture des liens pour l'aperçu local
# ---------------------------------------------------------------------------
def rewrite_links(doc, built):
    def href(m):
        attr, val = m.group(1), m.group(2)
        if not val.startswith("/") or val.startswith("//"):
            return m.group(0)
        if val.startswith("/assets/"):
            return f'{attr}="{val[1:]}"'
        path, sep, rest = re.match(r"([^?#]*)([?#]?)(.*)", val).groups()
        if not path.endswith("/"):
            path += "/"
        if path in built:
            return f'{attr}="{built[path]}{sep}{rest}"'
        return f'{attr}="{BASE}{val}"'
    doc = re.sub(r'(href|src|data-src|data-base)="([^"]*)"', href, doc)
    doc = re.sub(r'(srcset)="([^"]*)"', lambda m: f'{m.group(1)}="{m.group(2).replace(" /assets/", " assets/").lstrip("/")}"', doc)
    return doc


# ---------------------------------------------------------------------------
# Contrôle SEO (règles Rank Math du client)
# ---------------------------------------------------------------------------
def norm(s):
    s = unicodedata.normalize("NFD", s.lower())
    s = "".join(c for c in s if unicodedata.category(c) != "Mn")
    s = s.replace("'", " ").replace("’", " ").replace("-", " ")
    return re.sub(r"\s+", " ", s)


def kw_in(kw, text):
    """Le mot-clé est présent si tous ses mots significatifs le sont, dans l'ordre, à peu de distance."""
    words = [w for w in norm(kw).split() if w not in ("de", "a", "la", "le", "les", "d", "l", "du", "des", "en", "pour")]
    t = norm(text)
    pos = 0
    for w in words:
        i = t.find(w, pos)
        if i < 0:
            return False
        pos = i + len(w)
    return True


def strip_tags(s):
    return html.unescape(re.sub(r"<[^>]+>", " ", s))


def seo_check(page, body):
    h1s = re.findall(r"<h1[^>]*>(.*?)</h1>", body, re.S)
    h1 = re.sub(r"\s+", " ", strip_tags(h1s[0])).strip() if h1s else ""
    text = re.sub(r"<(script|style|svg)[^>]*>.*?</\1>", " ", body, flags=re.S)
    after = text.split("</h1>", 1)[1] if "</h1>" in text else text
    first_p = re.search(r"<p[^>]*>(.*?)</p>", after, re.S)
    intro = strip_tags(first_p.group(1)) if first_p else ""
    words = len(strip_tags(text).split())
    kw = page.get("keyword", "")
    slug = page["path"].strip("/")
    imgs = re.findall(r"<img[^>]*>", body)
    no_alt = [i for i in imgs if 'alt="' not in i]
    no_dim = [i for i in imgs if "width=" not in i]
    res = {
        "path": page["path"], "keyword": kw, "title": page["title"], "tlen": len(page["title"]),
        "description": page["description"], "dlen": len(page["description"]), "h1": h1, "h1n": len(h1s),
        "kw_title": kw_in(kw, page["title"]) if kw else None,
        "kw_title_start": norm(page["title"]).startswith(norm(kw)[:12]) if kw else None,
        "kw_desc": kw_in(kw, page["description"]) if kw else None,
        "kw_h1": kw_in(kw, h1) if kw else None,
        "kw_intro": kw_in(kw, intro) if kw else None,
        "kw_slug": kw_in(kw, slug.replace("-", " ")) if kw and slug else None,
        "words": words, "imgs": len(imgs), "no_alt": len(no_alt), "no_dim": len(no_dim),
        "h2": len(re.findall(r"<h2", body)),
    }
    return res


def seo_report(rows):
    ok = lambda b: "oui" if b else ("n/a" if b is None else "**non**")  # noqa: E731
    lines = [
        "# Contrôle SEO des pages de la maquette",
        "",
        f"Généré par `build.py` le {TODAY}. Règles du client : title de 50 à 60 caractères avec le mot-clé au début, "
        "meta description de 140 à 160 caractères, un seul H1 contenant le mot-clé, mot-clé dans l'introduction et dans l'URL. "
        "Score Rank Math visé : 80 à 90.",
        "",
        "| Page | Mot-clé principal | Title | Desc. | H1 | Mot-clé : title / début / desc. / H1 / intro / URL | Mots | Images sans alt |",
        "|---|---|---|---|---|---|---|---|",
    ]
    warn = 0
    for r in rows:
        tl = r["tlen"]; dl = r["dlen"]
        t_ok = 50 <= tl <= 60; d_ok = 140 <= dl <= 160
        if r["keyword"]:
            if not (t_ok and d_ok and r["h1n"] == 1): warn += 1
        lines.append(
            f"| `{r['path']}` | {r['keyword'] or '(aucun)'} | {tl}{'' if t_ok else ' ⚠'} | {dl}{'' if d_ok else ' ⚠'} | {r['h1n']} | "
            f"{ok(r['kw_title'])} / {ok(r['kw_title_start'])} / {ok(r['kw_desc'])} / {ok(r['kw_h1'])} / {ok(r['kw_intro'])} / {ok(r['kw_slug'])} | {r['words']} | {r['no_alt']} |")
    lines += ["", "## Titles et descriptions", ""]
    for r in rows:
        lines += [f"### `{r['path']}`", "", f"- **Mot-clé** : {r['keyword'] or '(aucun)'}", f"- **SEO Title** ({r['tlen']}) : {r['title']}",
                  f"- **Meta description** ({r['dlen']}) : {r['description']}", f"- **H1** : {r['h1']}", ""]
    return "\n".join(lines) + "\n", warn


# ---------------------------------------------------------------------------
# Fichiers annexes
# ---------------------------------------------------------------------------
REDIRECTS = [
    ("/agence-web-a-paris/", "/a-propos/"),
    ("/nos-services/", "/creation-site-internet-paris/"),
    ("/branding-identite-visuelle/", "/branding-identite/"),
    ("/logo-et-charte-graphique/", "/logo-charte-graphique-identite-visuelle/"),
    ("/google-my-business-artisans-tpe/", "/optimiser-sa-fiche-google-my-business/"),
    ("/creation-site-vitrine-tpe-paris/", "/prix-site-vitrine-paris/"),
    ("/agence-seo-clamart/", "/creation-site-internet-clamart/"),
    ("/creation-de-site-e-commerce-boulogne/", "/site-internet-boulogne-billancourt/"),
    ("/agence-web-collectivites-boulogne-billancourt/", "/agence-web-hauts-de-seine/"),
    ("/creation-site-web/", "/nos-conseils-web/"),
    # Archives de catégories : déplacées sous /conseils/ (et en noindex, follow dans Rank Math)
    ("/creation-site-vitrine/", "/conseils/site-vitrine/"),
    ("/creation-site-ecommerce/", "/conseils/site-e-commerce/"),
    ("/google-my-business-seo-local/", "/conseils/seo-local/"),
]


def minify_css():
    """Minification simple de la feuille de style (commentaires et espaces)."""
    src = open(os.path.join(ROOT, "assets", "css", "aurea.css"), encoding="utf-8").read()
    css = re.sub(r"/\*.*?\*/", "", src, flags=re.S)
    css = re.sub(r"\s+", " ", css)
    css = re.sub(r"\s*([{};,>])\s*", r"\1", css)
    css = re.sub(r":\s+", ":", css).replace(";}", "}")
    open(os.path.join(ROOT, "assets", "css", "aurea.min.css"), "w", encoding="utf-8").write(css.strip() + "\n")
    return len(src.encode()), len(css.encode())


def write_extras(pages):
    urls = [p for p in pages if p.get("type") != "404" and "noindex" not in p.get("robots", "")]
    sm = ['<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">']
    for p in urls:
        sm.append(f"  <url><loc>{BASE}{p['path']}</loc><lastmod>{p.get('modified', TODAY)}</lastmod></url>")
    sm.append("</urlset>")
    open(os.path.join(ROOT, "sitemap.xml"), "w", encoding="utf-8").write("\n".join(sm) + "\n")

    open(os.path.join(ROOT, "robots.txt"), "w", encoding="utf-8").write(
        "# robots.txt de la refonte : un seul groupe, tous les robots (moteurs et IA) autorisés\n"
        "User-agent: *\nDisallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php\nDisallow: /?s=\nDisallow: /search/\n\n"
        f"Sitemap: {BASE}/sitemap_index.xml\n")

    rules = ["# Redirections 301 de la refonte (à importer dans Rank Math > Redirections ou dans .htaccess)",
             "# Vérifier dans Search Console avant chaque fusion : l'URL cible doit être celle qui performe le mieux.",
             "<IfModule mod_rewrite.c>", "RewriteEngine On"]
    for a, b in REDIRECTS:
        rules.append(f"RewriteRule ^{a.strip('/')}/?$ {b} [R=301,L]")
    rules.append("</IfModule>")
    open(os.path.join(ROOT, "redirections-301.htaccess"), "w", encoding="utf-8").write("\n".join(rules) + "\n")

    svc = "\n".join(f"- [{s['name']} à Paris]({BASE}{s['path']}): {s['desc']} Prix : {s['price_label']}. Délai : {s['delay']}." for s in SERVICES)
    posts = "\n".join(f"- [{p['title']}]({BASE}/{p['slug']}/): {p['excerpt']}" for p in POSTS)
    llms = f"""# Aurea Media

> {BRIEF}

Informations clés (mises à jour le {TODAY}) :

- Entreprise : Aurea Media, SIREN {SITE['siren']}, {SITE['district']} ({SITE['street']}, {SITE['zip']} {SITE['city']}), rendez-vous sur demande
- Fondateur et interlocuteur unique : Alexandre Coury, développeur web et consultant SEO ; aucune sous-traitance
- Contact : {SITE['phone_intl']}, {SITE['email']}, {SITE['hours'].lower()}
- Zone d'intervention : Paris et Hauts-de-Seine (rendez-vous possibles), France entière et international en visioconférence
- Technologies : WordPress et WooCommerce, développés sur mesure
- Tarifs : site vitrine dès 1 000 € HT (3 pages, 3 semaines) ou 1 800 € HT (5 à 7 pages, 4 semaines) ; e-commerce dès 1 650 € HT (6 semaines) ou 3 200 € HT (8 semaines) ; refonte, SEO local, identité visuelle et présentations sur devis
- Inclus dans chaque site : SEO technique, données structurées, formulaire RGPD, formation ; le client est propriétaire du domaine, de l'hébergement et des fichiers
- Réponse aux demandes de devis : sous 24 h ouvrées, devis détaillé ligne par ligne
- Avis : {SITE['rating']}/5 sur {SITE['reviews']} avis Google

## Services

{svc}
- [Tarifs et simulateur de budget]({BASE}/prix-site-internet-paris/): toutes les formules, ce qu'elles comprennent et un simulateur.

## L'agence

- [À propos : Alexandre Coury et Aurea Media]({BASE}/a-propos/): parcours, méthode, engagements, zone d'intervention.
- [Réalisations]({BASE}/realisations-agence-web/): 20 sites en ligne (BTP, industrie, santé, droit, immobilier, institutions).
- [Étude de cas Novexia Construction]({BASE}/realisations-agence-web/novexia-construction/): site commercial de 11 pages pour une entreprise du bâtiment.
- [Agence web dans les Hauts-de-Seine]({BASE}/agence-web-hauts-de-seine/): Boulogne-Billancourt, Issy-les-Moulineaux, Clamart.
- [Contact et devis]({BASE}/contact-agence/): formulaire, rendez-vous de 20 minutes, téléphone.

## Guides

{posts}

## Optional

- [Tous les conseils]({BASE}/nos-conseils-web/)
- [Mentions légales]({BASE}/mentions-legales/)
- [Sitemap XML]({BASE}/sitemap_index.xml)
"""
    open(os.path.join(ROOT, "llms.txt"), "w", encoding="utf-8").write(llms)


# ---------------------------------------------------------------------------
def main():
    a, b = minify_css()
    print(f"CSS : {a // 1024} Ko -> {b // 1024} Ko minifié")
    pages = load_pages()
    built = {p["path"]: out_name(p["path"]) for p in pages}
    rows = []
    for p in pages:
        body = render_components(p["body"], p)
        # Délais échelonnés des entrées du héros, fusionnés avec un éventuel style existant
        n = iter(range(100))

        def stagger(m):
            tag = m.group(0)
            d = f"--d:{250 + next(n) * 90}ms"
            if ' style="' in tag:
                return tag.replace(' style="', f' style="{d};', 1)
            return tag.replace("data-hero-in", f'data-hero-in style="{d}"', 1)
        body = re.sub(r"<[a-z][^<>]*\bdata-hero-in\b[^<>]*>", stagger, body)
        h1 = re.search(r"<h1[^>]*>(.*?)</h1>", body, re.S)
        p["h1_plain"] = re.sub(r"\s+", " ", strip_tags(h1.group(1))).strip() if h1 else p["title"]
        p["_words"] = len(strip_tags(re.sub(r"<(script|style|svg)[^>]*>.*?</\1>", " ", body, flags=re.S)).split())
        rows.append(seo_check(p, body))
        doc = layout(p, body)
        doc = rewrite_links(doc, built)
        open(os.path.join(ROOT, out_name(p["path"])), "w", encoding="utf-8").write(doc)
        # Validation JSON-LD
        for block in re.findall(r'<script type="application/ld\+json">(.*?)</script>', doc, re.S):
            json.loads(block)
    report, warn = seo_report(rows)
    open(os.path.join(ROOT, "SEO-PAGES.md"), "w", encoding="utf-8").write(report)
    write_extras(pages)
    for r in rows:
        flag = ""
        if r["keyword"]:
            bad = []
            if not 50 <= r["tlen"] <= 60: bad.append(f"title {r['tlen']}")
            if not 140 <= r["dlen"] <= 160: bad.append(f"desc {r['dlen']}")
            if r["h1n"] != 1: bad.append(f"{r['h1n']} H1")
            for k in ("kw_title", "kw_desc", "kw_h1", "kw_intro"):
                if r[k] is False: bad.append(k)
            flag = ("  ⚠ " + ", ".join(bad)) if bad else "  ok"
        print(f"{out_name(r['path']):48} {r['words']:5} mots{flag}")
    print(f"{len(pages)} pages générées.")


if __name__ == "__main__":
    main()
