"""Textes de voix off de la vidéo de lancement CognitX pour les 11 marchés.
Base : texte français fourni par le client et sa traduction anglaise validée ; la phrase sécurité
cite l'hébergement local (Canada, États-Unis, Royaume-Uni) ou l'Europe. Minutage long (58,25 s).
Le produit s'appelle désormais YouCraft : le nom est remplacé dans tous les textes (BRAND)."""

BRAND = 'YouCraft'

# Instant d'apparition de chaque plan (minutage long), une réplique par plan.
CUES = [3.4, 9.2, 13.15, 17.35, 21.05, 22.95, 24.35, 27.1, 33.95, 37.2, 44.1, 51.9, 53.6]
END = 58.25


def en(where, us=False):
    n750 = 'seven hundred fifty' if us else 'seven hundred and fifty'
    n350 = 'three hundred fifty' if us else 'three hundred and fifty'
    return [
        "What we've done for almost thirty years? Find the value companies don't see.",
        "And today, the biggest opportunity is hiding in your day-to-day work.",
        "Your teams are full of ideas for tools that would make them more productive.",
        "What they're missing is a way to build them, quickly and easily.",
        "That tool exists: it's DigiCraft.",
        "The idea is very simple.",
        "You describe what you need, in your own words.",
        "And DigiCraft builds the app right before your eyes: the screens, the data, the calculations, the code.",
        "One click, and your tool is live. On desktop and on mobile.",
        f"DigiCraft has already proven itself: more than 750 users, and 350 apps deployed.|DigiCraft has already proven itself: more than {n750} users, and {n350} apps deployed.",
        f"When it comes to security, your data stays in {where}, access is fully under control, and getting started is instant, no training needed.",
        "Because it's your tool.",
        "Truly yours.",
    ]


FR_CA = [
    "Notre métier, depuis près de trente ans ? Trouver la valeur que les entreprises ne voient pas.",
    "Et aujourd'hui, le plus gros gisement se cache dans votre quotidien.",
    "Des idées d'outils pour gagner en productivité, vos équipes en ont plein.",
    "Ce qui leur manque, c'est de pouvoir les construire rapidement et simplement.",
    "Cet outil existe : c'est DigiCraft.",
    "Le principe est très simple.",
    "Vous décrivez votre besoin avec vos propres mots.",
    "Et DigiCraft construit l'application sous vos yeux : les écrans, les données, les calculs, le code.",
    "Un clic, et votre outil est en ligne. Sur ordinateur comme sur mobile.",
    "DigiCraft a déjà fait ses preuves : plus de 750 utilisateurs, et 350 applications déployées.|DigiCraft a déjà fait ses preuves : plus de sept cent cinquante utilisateurs, et trois cent cinquante applications déployées.",
    "Côté sécurité, vos données restent au Canada, les accès sont maîtrisés, et la prise en main est immédiate, sans aucune formation.",
    "Parce que c'est votre outil.",
    "Vraiment le vôtre.",
]

DE = [
    "Unser Kerngeschäft seit fast dreißig Jahren? Den Wert finden, den Unternehmen nicht sehen.",
    "Und heute liegt das größte Potenzial in Ihrem Arbeitsalltag.",
    "Ideen für Tools, die produktiver machen, haben Ihre Teams mehr als genug.",
    "Was fehlt, ist eine Möglichkeit, sie schnell und einfach zu bauen.",
    "Dieses Tool gibt es: DigiCraft.",
    "Das Prinzip ist ganz einfach.",
    "Sie beschreiben Ihren Bedarf in Ihren eigenen Worten.",
    "Und DigiCraft baut die App vor Ihren Augen: die Oberflächen, die Daten, die Berechnungen, den Code.",
    "Ein Klick, und Ihr Tool ist online. Am Computer wie auf dem Smartphone.",
    "DigiCraft hat sich bereits bewährt: über 750 Nutzer und 350 eingesetzte Apps.|DigiCraft hat sich bereits bewährt: über siebenhundertfünfzig Nutzer und dreihundertfünfzig eingesetzte Apps.",
    "In Sachen Sicherheit bleiben Ihre Daten in Europa, die Zugriffe sind voll kontrolliert, und der Einstieg gelingt sofort, ganz ohne Schulung.",
    "Denn es ist Ihr Tool.",
    "Wirklich Ihres.",
]

ES = [
    "¿Nuestro oficio desde hace casi treinta años? Encontrar el valor que las empresas no ven.",
    "Y hoy, la mayor oportunidad se esconde en su día a día.",
    "Ideas de herramientas para ser más productivos, sus equipos tienen de sobra.",
    "Lo que les falta es poder construirlas, de forma rápida y sencilla.",
    "Esa herramienta existe: es DigiCraft.",
    "Es muy sencillo.",
    "Describa lo que necesita, con sus palabras.",
    "Y DigiCraft construye la aplicación ante sus ojos: las pantallas, los datos, los cálculos, el código.",
    "Un clic, y su herramienta está en línea, en el ordenador y en el móvil.",
    "DigiCraft ya ha demostrado su valor: más de 750 usuarios y 350 apps desplegadas.|DigiCraft ya ha demostrado su valor: más de setecientos cincuenta usuarios y trescientas cincuenta apps desplegadas.",
    "En seguridad, sus datos se quedan en Europa, los accesos están bajo control, y se empieza al instante, sin formación.",
    "Porque es su herramienta.",
    "De verdad, la suya.",
]

IT = [
    "Il nostro mestiere da quasi trent'anni? Trovare il valore che le aziende non vedono.",
    "E oggi, il potenziale più grande è nel vostro quotidiano.",
    "Idee per strumenti più produttivi? I vostri team ne hanno tante.",
    "Manca un modo per costruirli, rapidamente e con semplicità.",
    "Lo strumento esiste: è DigiCraft.",
    "Il principio è semplice.",
    "Descrivete cosa vi serve, con parole vostre.",
    "E DigiCraft crea l'applicazione sotto i vostri occhi: schermate, dati, calcoli, codice.",
    "Un clic, ed è online, su computer e su mobile.",
    "DigiCraft ha già dato prova di sé: oltre 750 utenti e 350 app distribuite.|DigiCraft ha già dato prova di sé: oltre settecentocinquanta utenti e trecentocinquanta app distribuite.",
    "Sicurezza: i vostri dati restano in Europa, gli accessi sono sotto controllo, e si parte subito, senza formazione.",
    "Perché è il vostro strumento.",
    "Davvero vostro.",
]

NL = [
    "Ons vak, al bijna dertig jaar? De waarde vinden die bedrijven niet zien.",
    "En vandaag ligt de grootste kans verborgen in uw dagelijkse werk.",
    "Uw teams hebben genoeg ideeën voor tools om productiever te werken.",
    "Wat ze missen, is een manier om ze snel en eenvoudig te bouwen.",
    "Die tool bestaat: het is DigiCraft.",
    "Het principe is heel eenvoudig.",
    "U beschrijft wat u nodig hebt, in uw eigen woorden.",
    "En DigiCraft bouwt de app voor uw ogen: de schermen, de gegevens, de berekeningen, de code.",
    "Eén klik, en uw tool staat online. Op de computer en op mobiel.",
    "DigiCraft heeft zich al bewezen: meer dan 750 gebruikers en 350 apps in gebruik.|DigiCraft heeft zich al bewezen: meer dan zevenhonderdvijftig gebruikers en driehonderdvijftig apps in gebruik.",
    "Wat beveiliging betreft, blijven uw gegevens in Europa, is de toegang volledig onder controle, en kunt u meteen aan de slag, zonder training.",
    "Omdat het uw tool is.",
    "Echt van u.",
]

PL = [
    "Czym zajmujemy się od prawie trzydziestu lat? Odkrywamy wartość, której firmy nie widzą.",
    "A dziś największy potencjał kryje się w waszej codzienności.",
    "Pomysłów na produktywne narzędzia wasze zespoły mają mnóstwo.",
    "Brakuje im sposobu, by je zbudować, szybko i prosto.",
    "To narzędzie istnieje: DigiCraft.",
    "Zasada jest prosta.",
    "Opisujecie potrzebę własnymi słowami.",
    "A DigiCraft buduje aplikację na waszych oczach: ekrany, dane, obliczenia, kod.",
    "Jedno kliknięcie i gotowe, na komputerze i w telefonie.",
    "DigiCraft sprawdził się w praktyce: ponad 750 użytkowników i 350 aplikacji.|DigiCraft sprawdził się w praktyce: ponad siedemset pięćdziesiąt użytkowników i trzysta pięćdziesiąt aplikacji.",
    "Bezpieczeństwo: wasze dane zostają w Europie, dostęp jest w pełni kontrolowany, a start jest natychmiastowy, bez szkoleń.",
    "Bo to wasze narzędzie.",
    "Naprawdę wasze.",
]

SV = [
    "Vad vi har gjort i nästan trettio år? Hittat värdet som företag inte ser.",
    "Och i dag finns den största möjligheten i din vardag.",
    "Idéer på verktyg som gör dem mer produktiva har dina team gott om.",
    "Det som saknas är ett sätt att bygga dem, snabbt och enkelt.",
    "Det verktyget finns: det heter DigiCraft.",
    "Principen är väldigt enkel.",
    "Du beskriver vad du behöver, med dina egna ord.",
    "Och DigiCraft bygger appen framför dina ögon: skärmarna, datan, beräkningarna och koden.",
    "Ett klick, och ditt verktyg är live. På datorn och i mobilen.",
    "DigiCraft har redan visat vad det går för: över 750 användare och 350 appar i drift.|DigiCraft har redan visat vad det går för: över sjuhundrafemtio användare och trehundrafemtio appar i drift.",
    "När det gäller säkerhet stannar dina data i Europa, åtkomsten är helt under kontroll, och du kommer i gång direkt, utan utbildning.",
    "För det är ditt verktyg.",
    "Verkligen ditt.",
]

# Version française : texte de la voix off enregistrée par le client, désormais lu par la voix de synthèse.
FR = [
    "Notre métier, depuis près de trente ans ? Trouver la valeur que les entreprises ne voient pas.",
    "Et aujourd'hui, le plus gros gisement se cache dans votre quotidien.",
    "Des idées d'outils pour gagner en productivité, vos équipes en ont plein.",
    "Ce qui leur manque, c'est de pouvoir les construire rapidement et simplement.",
    "Et cet outil existe : c'est DigiCraft.",
    "Le principe est très simple.",
    "Vous décrivez votre besoin avec vos propres mots.",
    "Et DigiCraft construit l'application sous vos yeux : les écrans, les données, les agents IA, les calculs, le code.",
    "Un clic, et votre outil est en ligne. Sur ordinateur comme sur mobile.",
    "DigiCraft a déjà fait ses preuves : plus de 750 utilisateurs, et plus de 350 applications déployées.|DigiCraft a déjà fait ses preuves : plus de sept cent cinquante utilisateurs, et plus de trois cent cinquante applications déployées.",
    "Côté sécurité, vos données restent en Europe, les accès sont maîtrisés, et la prise en main est immédiate, sans aucune formation.",
    "Parce que c'est votre outil.",
    "Vraiment le vôtre.",
]

# marché -> (moteur, langue, lignes, vidéo muette avec le nom YouCraft à l'écran)
MARKETS = {
    'fr': ('kokoro_fr', 'fr', FR, 'video_yc_fr_silent.mp4'),
    'en': ('kokoro', 'en', en('Europe'), 'video_yc_en_silent.mp4'),
    'uk': ('kokoro', 'en', en('the UK'), 'video_yc_uk_silent.mp4'),
    'us': ('kokoro', 'en', en('the United States', us=True), 'video_yc_us_silent.mp4'),
    'caen': ('kokoro', 'en', en('Canada'), 'video_yc_caen_silent.mp4'),
    'cafr': ('kokoro_fr', 'fr', FR_CA, 'video_yc_cafr_silent.mp4'),
    'de': ('supertonic', 'de', DE, 'video_yc_de_silent.mp4'),
    'es': ('supertonic', 'es', ES, 'video_yc_es_silent.mp4'),
    'it': ('supertonic', 'it', IT, 'video_yc_it_silent.mp4'),
    'nl': ('supertonic', 'nl', NL, 'video_yc_nl_silent.mp4'),
    'pl': ('supertonic', 'pl', PL, 'video_yc_pl_silent.mp4'),
    'sv': ('supertonic', 'sv', SV, 'video_yc_sv_silent.mp4'),
}
MARKETS = {k: (e, l, [x.replace('DigiCraft', BRAND) for x in lines], v) for k, (e, l, lines, v) in MARKETS.items()}


def split(line):
    """'sous-titre|texte lu' ou une seule chaîne pour les deux."""
    return line.split('|') if '|' in line else (line, line)
