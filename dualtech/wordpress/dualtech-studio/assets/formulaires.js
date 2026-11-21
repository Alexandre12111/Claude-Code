/* DualTech Studio : message affiché dans le formulaire après l'envoi (retour de la page avec ?envoi=…). */
(() => {
  const p = new URLSearchParams(location.search);
  const etat = p.get('envoi'), type = p.get('formulaire');
  if (!etat || !type) return;
  const form = document.querySelector(`form[data-formulaire="${type}"]`) || document.getElementById('formulaire-' + type);
  if (!form) return;
  const textes = {
    ok: 'Merci, votre demande a bien été transmise à l’équipe DualTech. Nous revenons vers vous rapidement.',
    incomplet: 'Certains champs obligatoires sont vides ou l’adresse e-mail est incorrecte. Merci de vérifier puis de renvoyer le formulaire.',
    limite: 'Plusieurs demandes ont déjà été envoyées depuis votre connexion. Merci de réessayer dans une heure ou d’écrire à l’équipe DualTech.',
    erreur: 'Votre demande n’a pas pu être enregistrée. Merci de réessayer dans quelques instants.',
  };
  const m = document.createElement('p');
  m.className = 'envoi-maquette envoi-' + (etat === 'ok' ? 'ok' : 'erreur');
  m.setAttribute('role', etat === 'ok' ? 'status' : 'alert');
  m.textContent = textes[etat] || textes.erreur;
  form.prepend(m);
  if (etat === 'ok') form.querySelectorAll('input:not([type=hidden]), textarea, select').forEach((c) => { c.value = ''; });
  setTimeout(() => form.scrollIntoView({ block: 'center' }), 200);
  p.delete('envoi'); p.delete('formulaire');
  history.replaceState(null, '', location.pathname + (p.toString() ? '?' + p : '') + location.hash);
})();
