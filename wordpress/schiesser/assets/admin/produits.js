/* Formulaire produit : ajout et suppression des lignes de la fiche détaillée. */
(function () {
  var corps = document.querySelector('.js-fiche-lignes');
  var ajouter = document.querySelector('.js-fiche-ajouter');
  if (!corps || !ajouter) return;

  ajouter.addEventListener('click', function () {
    var tr = document.createElement('tr');
    tr.innerHTML =
      '<td><input type="text" name="s_fiche_k[]" value=""></td>' +
      '<td><input type="text" name="s_fiche_v[]" value=""></td>' +
      '<td><button type="button" class="button-link js-fiche-retirer" aria-label="Retirer la ligne">✕</button></td>';
    corps.appendChild(tr);
    tr.querySelector('input').focus();
  });

  corps.addEventListener('click', function (e) {
    var b = e.target.closest('.js-fiche-retirer');
    if (b) b.closest('tr').remove();
  });
})();
