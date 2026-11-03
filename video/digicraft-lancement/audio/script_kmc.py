"""Vidéo YouCraft personnalisée pour le groupe KMC (chambres télécom et boîtes de branchement), voix clonée d'Alexandre.
text : texte lu (« KMC » écrit « Ka-Èm-Cé » pour être épelé, nombres en toutes lettres) ; sub : texte des sous-titres ;
scene : scène visuelle de src_kmc. Réglages de voix identiques à script_cons_alex (timbre au plus près de la référence).
Chiffres et garanties repris des textes déjà validés : 750 utilisateurs, 350 applications, connexion sécurisée, rôles et
droits d'accès, hébergement en Europe."""
from script_cons_alex import REFS, REF_DIR
KMC = 'Ka-Èm-Cé'
L = [
    # 1. Introduction personnalisée
    dict(scene='intro', text=f"Bonjour à toute l'équipe du groupe {KMC}, et merci pour votre temps."),
    dict(scene='intro', text="Cette vidéo a été préparée pour vous, pour vous montrer ce que YouCraft peut apporter à votre production."),
    # 2. Votre procédé
    dict(scene='procede', text="Chez vous, chaque chambre télécom et chaque boîte de branchement suit le même cycle."),
    dict(scene='procede', text="Le moulage, la chauffe et le séchage, le démoulage… puis la lubrification du moule, avant le cycle suivant."),
    dict(scene='procede', text="Savoir à chaque instant où en est chaque moule, c'est un vrai levier de productivité. Et vous avez déjà commencé à construire cet outil."),
    dict(scene='logo', text="Avec YouCraft, ce suivi peut être prêt en quelques heures.", ex=0.65),
    # 3. Décrire, YouCraft construit
    dict(scene='decrire', text="Il suffit de décrire votre besoin, avec vos propres mots."),
    dict(scene='decrire', text="Par exemple : suivre chaque moule, du coulage au démoulage, avec les temps de séchage et les températures de chauffe."),
    dict(scene='decrire', text="Et YouCraft construit alors l'application complète : les écrans, les données, les alertes, et les droits d'accès."),
    # 4. L'application de suivi
    dict(scene='atelier', text="Voici le résultat. Chaque moule apparaît avec son étape en cours, et le temps restant avant le démoulage."),
    dict(scene='atelier', text="Les températures de chauffe sont suivies en direct, et chaque cycle est enregistré automatiquement."),
    dict(scene='fiche', text="En un clic, vous ouvrez la fiche d'un moule : son historique, sa courbe de température, et le contrôle de lubrification."),
    dict(scene='mobile', text="Dans l'atelier, les opérateurs sont prévenus sur leur téléphone dès qu'une pièce peut être démoulée."),
    dict(scene='mobile', text="Ils valident l'étape en un instant, et le tableau de bord se met à jour pour tout le monde."),
    dict(scene='modif', text="Une nouvelle idée ? Une alerte si l'étuve dépasse sa température ? Vous la demandez… et elle est en ligne en quelques minutes."),
    # 5. L'outil existant
    dict(scene='existant', text="Et vous ne repartez pas de zéro."),
    dict(scene='existant', text="YouCraft peut compléter l'outil que vous avez déjà commencé, en se connectant à vos données existantes."),
    dict(scene='existant', text="Ce qui demande des mois de développement se construit en quelques jours, et vos équipes restent concentrées sur la production."),
    # 6. Présentations et site internet
    dict(scene='prez', text="Mais YouCraft ne s'arrête pas au suivi de production."),
    dict(scene='prez', text="Vos présentations commerciales aussi : en quelques minutes, une présentation claire, moderne, et dynamique."),
    dict(scene='site', text="Et pourquoi pas votre site internet ? Une vitrine moderne, pour présenter vos chambres télécom et vos boîtes de branchement."),
    # 7. Garanties
    dict(scene='securite', text="Le tout sur une plateforme sécurisée : connexion protégée, droits d'accès par rôle, et données hébergées en Europe."),
    dict(scene='securite', text="YouCraft est déjà utilisé par plus de sept cent cinquante personnes, avec trois cent cinquante applications déployées.",
         sub="YouCraft est déjà utilisé par plus de 750 personnes, avec 350 applications déployées."),
    # 8. Signature
    dict(scene='fin', text="Votre suivi de production, vos présentations, votre site : tout commence par une simple description.", ex=0.65),
    dict(scene='fin', text="Avec YouCraft, donnons vie à vos idées.", ex=0.65),
]
for d in L:
    d.setdefault('sub', d['text'].replace(KMC, 'KMC'))
    d['ex'] = d.get('ex', 0.55)
    d['cfg'] = 0.5
