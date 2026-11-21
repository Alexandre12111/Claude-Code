/* Aurea Studio : avis ajoutés à la main (ajouter, retirer une ligne) */
(() => {
  const liste = document.getElementById('aurea-manuels');
  const ajouter = document.getElementById('aurea-manuel-ajouter');
  if (!liste || !ajouter) return;
  ajouter.addEventListener('click', () => {
    const modele = liste.querySelector('.aurea-manuel:last-child');
    const ligne = modele.cloneNode(true);
    ligne.querySelectorAll('input, textarea').forEach(c => { c.value = ''; });
    ligne.querySelector('select').value = '5';
    liste.append(ligne);
    ligne.querySelector('input').focus();
  });
  liste.addEventListener('click', e => {
    const btn = e.target.closest('[data-retirer]');
    if (!btn) return;
    const ligne = btn.closest('.aurea-manuel');
    if (liste.children.length > 1) ligne.remove();
    else ligne.querySelectorAll('input, textarea').forEach(c => { c.value = ''; });
  });
})();
