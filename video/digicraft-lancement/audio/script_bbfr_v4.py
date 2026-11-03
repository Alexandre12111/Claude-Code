"""Texte et direction de jeu de la voix off française (partagés par tts_bbfr_takes.py et build_bbfr_v4.py).
Version 6 : texte réécrit pour donner envie, sans écho ni effet, voix posée, « Une modification ? Elle est en ligne en quelques minutes. », fin sur « Donnons-lui vie ».
t0 : instant d'apparition du texte à l'écran (film d'origine) ; anchor : mot calé sur cet instant (None = début) ;
ex / cfg : expressivité et guidage Chatterbox ; rate : débit visé en syllabes par seconde (None = naturel) ; emph : mots appuyés."""
REF = 'vo_ref/ref_homme_piper_upmc_pierre.wav'
L = [
    dict(text="Imaginez… pouvoir créer n'importe quelle application,", t0=1.62, anchor='importe', ex=0.8, cfg=0.38, rate=6.0, emph=['imaginez']),
    dict(text="sans écrire une seule ligne de code…", t0=3.45, ex=0.75, cfg=0.4, rate=6.2, emph=['code']),
    dict(text="sans attendre le service informatique…", t0=4.85, ex=0.75, cfg=0.4, rate=6.2, emph=['attendre']),
    dict(text="et sans aucune limite.", t0=6.4, ex=0.8, cfg=0.38, rate=5.8, emph=['limite']),
    dict(text="Simplement… en la décrivant.", t0=7.82, anchor='simplement', ex=0.7, cfg=0.4, rate=5.2, emph=['simplement']),
    dict(text="Voici YouCraft.", t0=11.1, anchor='youcraft', ex=0.6, cfg=0.42, rate=None, emph=[]),
    dict(text="La plateforme qui transforme vos idées en applications professionnelles.", t0=12.65, ex=0.75, cfg=0.4, rate=None, emph=['idees', 'professionnelles']),
    dict(text="Partez d'une idée, ou d'un modèle.", t0=16.95, ex=0.75, cfg=0.4, rate=6.0, emph=['idee']),
    dict(text="Décrivez votre besoin… et regardez-la prendre forme sous vos yeux.", t0=18.45, ex=0.75, cfg=0.4, rate=6.2, emph=['forme']),
    dict(text="Ajustez, enrichissez, faites-la grandir.", t0=23.8, ex=0.75, cfg=0.42, rate=None, emph=['grandir']),
    dict(text="Une modification ? Elle est en ligne en quelques minutes.", t0=26.45, anchor='ligne', ex=0.8, cfg=0.4, rate=None, emph=['minutes']),
    dict(text="D'une équipe… à tout le groupe.", t0=30.7, ex=0.75, cfg=0.4, rate=None, emph=['groupe']),
    dict(text="Des résultats rapides… avec une qualité de niveau entreprise.", t0=35.9, anchor='niveau', ex=0.8, cfg=0.4, rate=6.2, emph=['rapides', 'entreprise']),
    dict(text="Tout est intégré, sécurisé par conception, et prêt à être lancé.", t0=38.15, anchor='securise', ex=0.8, cfg=0.4, rate=6.2, emph=['lance']),
    dict(text="Une nouvelle façon de travailler… où vos équipes vont aussi loin que leurs idées.", t0=41.0, ex=0.8, cfg=0.4, rate=6.0, emph=['loin']),
    dict(text="Vous l'imaginez ?", t0=46.35, ex=0.8, cfg=0.4, rate=None, emph=[]),
    dict(text="Donnons-lui vie.", t0=48.45, ex=0.8, cfg=0.4, rate=None, emph=['vie']),
]
for d in L:
    d.setdefault('anchor', None); d.setdefault('fx', None)
