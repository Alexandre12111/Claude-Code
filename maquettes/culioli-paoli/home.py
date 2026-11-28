# -*- coding: utf-8 -*-
"""Page d'accueil · direction « Éditorial parisien »."""
from pages_expertises import EXPERTISES

T = '<span class="todo">à confirmer</span>'
ROMAN = ["I", "II", "III", "IV", "V", "VI", "VII"]

ETABS = [
    ("Centre pénitentiaire de Fleury-Mérogis", "Essonne (91)", "/avocat-centre-penitentiaire-fleury-merogis/"),
    ("Centre pénitentiaire de Fresnes", "Val-de-Marne (94)", ""),
    ("Prison de Paris-La Santé", "Paris (75)", ""),
]


def home_body(p):
    domains = "".join(
        f'<li><a href="/{x["slug"]}/"><span class="num">{i+1:02d}</span><h3>{x["nav"]}</h3><p>{x["somm"]}</p>{{{{ARROW}}}}</a></li>'
        for i, x in enumerate(EXPERTISES))
    return f"""
<section class="hero">
  <div class="wrap">
    <div class="hero-grid">
      <div class="hero-text">
        <p class="eyebrow">Avocat au Barreau de Paris · Droit pénal</p>
        <h1>Charles Culioli-Paoli,<br><em>avocat pénaliste à Paris</em></h1>
        <p class="lead">Une défense pénale exigeante et discrète, de la garde à vue à l'exécution de la peine, pour les personnes mises en cause comme pour les victimes.</p>
        <div class="hero-actions">
          <a class="btn" href="/contact/">Prendre rendez-vous</a>
          <a class="link" href="/expertises/">Domaines d'intervention {{{{ARROW}}}}</a>
        </div>
      </div>
      <figure class="hero-figure">
        <img src="assets/img/charles-culioli-paoli-avocat-robe.webp" width="900" height="1317" alt="Me Charles Culioli-Paoli en robe d'avocat au tribunal judiciaire de Paris" fetchpriority="high">
        <figcaption><span>Me Charles Culioli-Paoli</span><span>Tribunal judiciaire de Paris</span></figcaption>
      </figure>
    </div>
    <dl class="creds">
      <div><dt>Formation</dt><dd>LL.M., Harvard Law School</dd></div>
      <div><dt>Formation</dt><dd>Master, École de droit de Sciences Po</dd></div>
      <div><dt>Inscription</dt><dd>Barreau de Paris{T}</dd></div>
      <div><dt>Langues</dt><dd>Français, anglais, espagnol, arabe, corse</dd></div>
    </dl>
  </div>
</section>

<section class="arch" aria-label="Approche de la défense">
  <img src="assets/img/tribunal-judiciaire-paris-architecture.webp" width="1600" height="520" loading="lazy" alt="Verrières elliptiques de la salle des pas perdus du tribunal judiciaire de Paris">
  <div class="wrap">
    <div class="arch-note reveal">
      <blockquote>« Chaque dossier pénal raconte une histoire incomplète. Le travail de l'avocat est de la lire entièrement, puis de la rendre audible devant le juge. »</blockquote>
      <p class="eyebrow">Charles Culioli-Paoli · formulation à valider</p>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap intro-grid">
    <figure class="intro-photo reveal">
      <img src="assets/img/charles-culioli-paoli-portrait.webp" width="400" height="400" loading="lazy" alt="Portrait de Me Charles Culioli-Paoli, avocat pénaliste à Paris">
      <figcaption>Me Charles Culioli-Paoli</figcaption>
    </figure>
    <div class="intro-text reveal">
      <p class="eyebrow">Le cabinet</p>
      <h2>Un avocat indépendant, <em>un interlocuteur unique</em></h2>
      <p class="lead">Charles Culioli-Paoli exerce en droit pénal à Paris. Chaque dossier est suivi personnellement, du premier échange jusqu'à la décision, avec la même exigence de rigueur et de discrétion.</p>
      <p class="muted">Formé à l'École de droit de Sciences Po puis à Harvard Law School, il a acquis son expérience auprès de cabinets pénalistes parisiens reconnus, au Parquet de Paris et au ministère de l'Économie.{T}</p>
      <a class="link" href="/charles-culioli-paoli-avocat/">Découvrir le parcours {{{{ARROW}}}}</a>
    </div>
  </div>
</section>

<section class="section navy">
  <div class="wrap sec-grid">
    <div class="sec-side"><div class="sticky">
      <p class="eyebrow">Domaines d'intervention</p>
      <h2>Le droit pénal, <em>dans toutes ses dimensions</em></h2>
      <p style="margin-top:1.2rem;max-width:30em">De l'enquête à l'exécution de la peine, en France et dans les dossiers internationaux.</p>
      <p style="margin-top:2rem"><a class="link link--light" href="/expertises/">Toutes les expertises {{{{ARROW}}}}</a></p>
    </div></div>
    <div class="sec-main"><ol class="domains">{domains}</ol></div>
  </div>
</section>

<section class="section">
  <div class="wrap sec-grid">
    <div class="sec-side"><div class="sticky">
      <p class="eyebrow">Méthode</p>
      <h2>Une défense construite <em>avec vous</em></h2>
      <p class="muted" style="margin-top:1.2rem;max-width:28em">La relation avec la personne défendue repose sur la clarté : comprendre, expliquer, décider ensemble.</p>
    </div></div>
    <div class="sec-main">
      <ol class="method stagger">
        <li><span class="num">01</span><div><h3>Le premier échange</h3><p>Un entretien confidentiel pour comprendre la situation, les échéances et les attentes. Les honoraires sont présentés et font l'objet d'une convention écrite.</p></div></li>
        <li><span class="num">02</span><div><h3>L'analyse du dossier</h3><p>Une lecture complète de la procédure, un contrôle de la régularité de chaque acte et une évaluation honnête des risques.</p></div></li>
        <li><span class="num">03</span><div><h3>La stratégie</h3><p>Les options sont exposées clairement : actes à demander, nullités, issue négociée ou défense au fond. La décision vous appartient.</p></div></li>
        <li><span class="num">04</span><div><h3>La défense et le suivi</h3><p>Présence aux auditions et aux audiences, information à chaque étape, puis accompagnement jusqu'à l'exécution de la décision.</p></div></li>
      </ol>
    </div>
  </div>
</section>

<section class="section tone">
  <div class="wrap sec-grid">
    <div class="sec-side"><div class="sticky">
      <p class="eyebrow">Parcours</p>
      <h2>Paris, Cambridge, <em>le Parquet</em></h2>
      <p class="muted" style="margin-top:1.2rem;max-width:28em">Une formation académique internationale, une expérience des deux côtés de la barre.</p>
      <p style="margin-top:2rem"><a class="link" href="/charles-culioli-paoli-avocat/">Le parcours complet {{{{ARROW}}}}</a></p>
    </div></div>
    <div class="sec-main">
      <div class="cv" style="margin-top:0">
        <h3>Formation</h3>
        <dl class="stagger">
          <div><dt>LL.M., Harvard Law School</dt><dd>Cambridge, Massachusetts{T}</dd></div>
          <div><dt>Master en droit, École de droit de Sciences Po</dt><dd>Paris{T}</dd></div>
        </dl>
      </div>
      <div class="cv">
        <h3>Expérience</h3>
        <dl class="stagger">
          <div><dt>Fedida &amp; Associés</dt><dd>Cabinet de défense pénale, Paris{T}</dd></div>
          <div><dt>Schapira Associés</dt><dd>Cabinet de défense pénale, Paris{T}</dd></div>
          <div><dt>Parquet de Paris</dt><dd>Ministère public{T}</dd></div>
          <div><dt>Ministère de l'Économie</dt><dd>Administration centrale{T}</dd></div>
          <div><dt>Barreau de Paris</dt><dd>Prestation de serment en 2025 · inscription au Barreau de New York{T}</dd></div>
        </dl>
      </div>
      <p class="langs">Français · English · Español · <span lang="ar">العربية</span> · Corsu</p>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="sec-grid" style="margin-bottom:var(--sp-4)">
      <div class="sec-side"><p class="eyebrow">Territoires</p><h2>Où le cabinet <em>intervient</em></h2></div>
      <div class="sec-main"><p class="lead muted" style="max-width:34em;padding-top:2.2rem">Paris en priorité, l'Essonne en second ancrage, et partout en France lorsque la défense l'exige.</p></div>
    </div>
    <div class="places stagger">
      <div class="place"><span class="num">75</span><h3>Paris</h3><p>Tribunal judiciaire et cour d'appel de Paris, commissariats parisiens, pôle économique et financier.</p><a class="link" href="/avocat-penaliste-paris/">Avocat pénaliste à Paris {{{{ARROW}}}}</a></div>
      <div class="place"><span class="num">91</span><h3>Essonne</h3><p>Tribunal judiciaire d'Évry-Courcouronnes, commissariats de Massy, Corbeil-Essonnes, Palaiseau, Longjumeau et Étampes.</p><a class="link" href="/avocat-penaliste-essonne/">Avocat pénaliste en Essonne {{{{ARROW}}}}</a></div>
      <div class="place"><span class="num">FR</span><h3>Établissements pénitentiaires</h3><p>Fleury-Mérogis, Fresnes, Paris-La Santé : visites, mise en liberté, discipline et aménagement de peine.</p><a class="link" href="/etablissements-penitentiaires/">Défendre une personne détenue {{{{ARROW}}}}</a></div>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="wrap">
    <div class="sec-grid" style="margin-bottom:var(--sp-4)">
      <div class="sec-side"><p class="eyebrow">Ressources</p><h2>Comprendre <em>avant d'agir</em></h2></div>
    </div>
    <div class="reads">
      <a class="read-main reveal" href="/guide-amenagement-de-peine/"><p class="eyebrow">Guide · Exécution des peines</p><h3>Aménagement de peine : conditions, procédure et délais</h3><p>Bracelet électronique, semi-liberté, libération conditionnelle : ce que prévoit le Code de procédure pénale et comment préparer la demande.</p></a>
      <div class="read-list reveal">
        <a href="/avocat-garde-a-vue-paris/"><p class="eyebrow">Procédure</p><h3>Garde à vue : les droits de la personne et le rôle de l'avocat</h3></a>
        <a href="/avocat-extradition/"><p class="eyebrow">International</p><h3>Mandat d'arrêt européen : les premières heures</h3></a>
        <a href="/ressources/" class="muted"><p class="eyebrow">Toutes les ressources</p><h3>Guides et repères de procédure</h3></a>
      </div>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="wrap">
    <div class="contact-home">
      <div class="c1"><p class="eyebrow">Contact</p><h2 style="margin-top:1rem">Parlons de <em>votre situation</em></h2>
        <p class="lead">Un premier échange permet d'analyser la situation et de définir la suite. Pour une garde à vue en cours, le cabinet peut être joint à tout moment.</p>
        <div class="hero-actions"><a class="btn" href="/contact/">Prendre rendez-vous</a><button class="urg-link" type="button" data-urgence><span class="dot" aria-hidden="true"></span>Urgence pénale</button></div></div>
      <dl class="c2"><dt>Cabinet</dt><dd>264 rue du Faubourg Saint-Honoré<br>75008 Paris{T}</dd><dt>Bureau</dt><dd>Massy, Essonne{T}</dd></dl>
      <dl class="c3"><dt>Rendez-vous</dt><dd>Au cabinet ou en visioconférence</dd><dt>Langues</dt><dd>Français, anglais, espagnol, arabe, corse</dd></dl>
    </div>
  </div>
</section>"""
