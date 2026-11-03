"""Vidéo explicative YouCraft pour les consultants (environ 3 min) : texte de la voix off, découpé par scène.
text : texte lu (nombres en toutes lettres pour la voix) ; sub : texte des sous-titres s'il diffère ; scene : scène visuelle.
Chiffres et arguments repris des textes validés par le client (vidéo de lancement) : 750 utilisateurs, 350 applications,
connexion sécurisée, rôles et droits, hébergement en Europe, ordinateur et mobile."""
REF = 'vo_ref/ref_homme_piper_upmc_pierre.wav'
L = [
    # 1. Le constat
    dict(scene='constat', text="Vos clients ont tous des idées pour mieux travailler."),
    dict(scene='constat', text="Un outil pour suivre leurs équipes, piloter leurs livraisons, ou anticiper leurs pannes."),
    dict(scene='constat', text="Mais entre le manque de temps, les budgets informatiques, et des mois de développement… ces idées restent souvent dans un tableur."),
    dict(scene='logo', text="YouCraft change la donne.", ex=0.75),
    # 2. Le produit
    dict(scene='produit', text="YouCraft est une plateforme de création d'applications par l'intelligence artificielle, pensée pour les entreprises."),
    dict(scene='produit', text="Vos clients décrivent leur besoin avec leurs mots… et YouCraft construit l'application complète, en quelques minutes."),
    # 3. Comment ça marche
    dict(scene='etape1', text="Première étape : décrire. Un processus, un tableau de bord, un portail client : il suffit de l'expliquer, comme à un collègue."),
    dict(scene='etape2', text="Deuxième étape : YouCraft construit tout. Les écrans, les données, les circuits de validation, et les droits d'accès."),
    dict(scene='etape2b', text="Chaque modification se fait en langage naturel : on demande, et l'application évolue."),
    dict(scene='etape3', text="Troisième étape : publier. En un clic, l'application est en ligne, sur ordinateur comme sur mobile."),
    # 4. Cas d'usage
    dict(scene='cas_intro', text="Prenons quelques exemples concrets."),
    dict(scene='cas_rh', text="Pour une direction des ressources humaines : un portail d'accueil des nouveaux arrivants."),
    dict(scene='cas_rh', text="Chaque recrue a son parcours, ses tâches, et son parrain. Les managers voient en direct qui est prêt pour son premier jour."),
    dict(scene='cas_log', text="Pour une direction logistique : une tour de contrôle des livraisons."),
    dict(scene='cas_log', text="Les camions sont suivis en temps réel, les retards sont détectés, et le client est prévenu automatiquement."),
    dict(scene='cas_ind', text="Pour un site industriel : la surveillance des machines."),
    dict(scene='cas_ind', text="Les capteurs alertent avant la panne, et l'ordre de travail est créé, puis assigné au bon technicien."),
    dict(scene='cas_autres', text="Et la liste continue : pipeline commercial, gestion des stocks, planning des équipes, notes de frais, audits…"),
    dict(scene='cas_autres', text="Chaque métier peut avoir son propre outil, sans écrire une seule ligne de code."),
    # 5. Les atouts
    dict(scene='preuves', text="YouCraft a déjà fait ses preuves : plus de sept cent cinquante utilisateurs, et trois cent cinquante applications déployées.",
         sub="YouCraft a déjà fait ses preuves : plus de 750 utilisateurs, et 350 applications déployées."),
    dict(scene='securite', text="Côté sécurité, tout est intégré : connexion sécurisée, rôles et droits d'accès, et données hébergées en Europe."),
    # 6. Comment le vendre
    dict(scene='vendre', text="Pour vous, consultants, c'est une vraie opportunité.", ex=0.8),
    dict(scene='vendre1', text="Repérez chez vos clients les processus qui reposent sur des tableurs, des courriels, ou des ressaisies."),
    dict(scene='vendre2', text="Proposez un atelier : en une séance, vous construisez avec eux une première application, sur leur propre cas."),
    dict(scene='vendre3', text="Démarrez par un projet pilote, mesurez le temps gagné… puis étendez à d'autres équipes."),
    # 7. Signature
    dict(scene='fin', text="YouCraft. Les idées de vos clients deviennent des applications.", ex=0.75),
    dict(scene='fin', text="Donnons-leur vie, ensemble.", ex=0.8),
]
for d in L:
    d.setdefault('sub', d['text']); d.setdefault('ex', 0.7); d.setdefault('cfg', 0.4)
