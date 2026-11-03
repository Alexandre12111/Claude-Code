"""Texte et direction de jeu de la voix off française dynamique (partagés par tts_bbfr_takes.py et build_bbfr_v4.py).
t0 : instant d'apparition du texte à l'écran (film d'origine) ; anchor : mot calé sur cet instant (None = début) ;
ex / cfg : expressivité et guidage Chatterbox ; rate : débit visé en syllabes par seconde ; emph : mots appuyés ;
fx : 'echo' (écho + réverbération) ou 'verb' (réverbération légère)."""
REF = 'vo_ref/ref_homme_piper_upmc_pierre.wav'
L = [
    dict(text="Imaginez… pouvoir créer n'importe quelle application,", t0=1.62, anchor='importe', ex=0.85, cfg=0.35, rate=6.0, emph=['imaginez', 'application']),
    dict(text="Pour n'importe quelle équipe…", t0=3.45, ex=0.85, cfg=0.35, rate=6.2, emph=['equipe']),
    dict(text="pour n'importe quel processus…", t0=4.85, ex=0.9, cfg=0.35, rate=6.2, emph=['processus']),
    dict(text="à n'importe quelle échelle !", t0=6.4, ex=1.0, cfg=0.32, rate=6.0, emph=['echelle']),
    dict(text="Simplement… en la décrivant.", t0=7.82, anchor='simplement', ex=0.8, cfg=0.35, rate=5.2, emph=['simplement', 'decrivant']),
    dict(text="Voici… YouCraft !", t0=11.1, anchor='youcraft', ex=1.15, cfg=0.3, rate=None, emph=['youcraft'], fx='echo'),
    dict(text="Une plateforme complète de création par l'intelligence artificielle, pensée pour les entreprises !", t0=12.65, ex=0.85, cfg=0.4, rate=6.6, emph=['complete', 'intelligence', 'artificielle', 'entreprises']),
    dict(text="Partez d'une idée… ou d'un modèle.", t0=16.95, ex=0.9, cfg=0.35, rate=6.0, emph=['idee', 'modele']),
    dict(text="Décrivez votre besoin… et regardez-le prendre forme !", t0=18.45, ex=0.9, cfg=0.35, rate=6.4, emph=['decrivez', 'forme']),
    dict(text="Créée en quelques minutes !", t0=22.45, ex=1.05, cfg=0.32, rate=6.6, emph=['minutes']),
    dict(text="Planifiez, personnalisez, faites-la évoluer… dans toutes les directions !", t0=23.45, ex=0.95, cfg=0.35, rate=6.6, emph=['planifiez', 'personnalisez', 'directions']),
    dict(text="Demandez… c'est prêt !", t0=26.45, anchor='demandez', ex=1.05, cfg=0.32, rate=None, emph=['pret']),
    dict(text="Des outils conçus pour une équipe… jusqu'aux plateformes prêtes pour toute l'entreprise.", t0=30.0, ex=0.9, cfg=0.38, rate=6.5, emph=['toute', 'entreprise']),
    dict(text="Des gains rapides… aux applications de niveau entreprise !", t0=35.9, anchor='niveau', ex=0.95, cfg=0.35, rate=6.4, emph=['rapides', 'niveau', 'entreprise']),
    dict(text="Tout est intégré, sécurisé par conception, et prêt à être lancé !", t0=38.15, anchor='securise', ex=1.0, cfg=0.33, rate=6.6, emph=['integre', 'securise', 'lance'], fx='echo'),
    dict(text="C'est une nouvelle façon de travailler… où vos équipes vont aussi loin que leurs idées !", t0=41.0, ex=0.95, cfg=0.35, rate=6.4, emph=['nouvelle', 'loin', 'idees']),
    dict(text="Vous l'imaginez ?", t0=46.35, ex=1.0, cfg=0.32, rate=None, emph=['imaginez'], fx='verb'),
    dict(text="Donnons-lui vie !", t0=48.45, ex=1.15, cfg=0.3, rate=None, emph=['vie'], fx='echo'),
    dict(text="Demandez votre démo.", t0=50.65, ex=0.85, cfg=0.35, rate=5.8, emph=['demo']),
]
for d in L:
    d.setdefault('anchor', None); d.setdefault('fx', None)
