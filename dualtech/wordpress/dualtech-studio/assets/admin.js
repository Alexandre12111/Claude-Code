/* DualTech Studio : choix et ordre des intervenants d'une séance (sans bibliothèque). */
(() => {
  document.querySelectorAll('.dts-choix').forEach((bloc) => {
    const liste = bloc.querySelector('.dts-choix__liste');
    const ajout = bloc.querySelector('.dts-choix__ajout');
    const nom = bloc.dataset.nom;
    ajout.addEventListener('change', () => {
      const o = ajout.selectedOptions[0];
      if (!o || !o.value) return;
      if (!liste.querySelector(`input[value="${o.value}"]`)) {
        const li = document.createElement('li');
        li.innerHTML = `<input type="hidden" name="${nom}" value="${o.value}"><span></span><button type="button" class="button-link" data-action="monter" aria-label="Monter">↑</button><button type="button" class="button-link" data-action="descendre" aria-label="Descendre">↓</button><button type="button" class="button-link dts-retirer" data-action="retirer" aria-label="Retirer">✕</button>`;
        li.querySelector('span').textContent = o.dataset.libelle;
        liste.appendChild(li);
      }
      ajout.value = '';
    });
    liste.addEventListener('click', (e) => {
      const b = e.target.closest('button'); if (!b) return;
      const li = b.closest('li');
      if (b.dataset.action === 'retirer') li.remove();
      if (b.dataset.action === 'monter' && li.previousElementSibling) li.parentNode.insertBefore(li, li.previousElementSibling);
      if (b.dataset.action === 'descendre' && li.nextElementSibling) li.parentNode.insertBefore(li.nextElementSibling, li);
    });
  });
})();
