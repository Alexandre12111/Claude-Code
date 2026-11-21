/* Aurea Studio : écran Logos clients */
(($) => {
  const cfg = window.aureaLogos;
  let logos = cfg.logos.map(l => ({ ...l }));
  let supprimes = [];
  let modifie = false;

  const $grille = $('#aurea-grille'), $vide = $('#aurea-vide'), $etat = $('#aurea-etat');
  const $enregistrer = $('#aurea-enregistrer'), $compteur = $('#aurea-compteur');
  const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const cle = l => l.id || l.tmp;

  const etat = (texte, type = '') => { $etat.text(texte).attr('data-type', type); };
  const marquer = () => {
    modifie = true; $enregistrer.prop('disabled', false); etat('Modifications non enregistrées', 'attente');
  };

  // Visuel d'un logo : silhouette (masque) ou couleurs d'origine
  const visuel = l => (l.couleur || l.opaque)
    ? `<img class="aurea-logo is-couleur" src="${esc(l.url)}" alt="">`
    : `<span class="aurea-logo" style="--logo:url('${esc(l.url)}')"></span>`;

  const carte = l => `
    <li class="aurea-carte${l.visible ? '' : ' is-masque'}" data-cle="${esc(cle(l))}">
      <div class="aurea-carte-visuel" title="Glisser pour déplacer">
        <span class="aurea-poignee" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i></span>
        ${visuel(l)}
        ${l.visible ? '' : '<span class="aurea-pastille">Masqué</span>'}
      </div>
      <div class="aurea-carte-champs">
        <label>Nom<input type="text" data-champ="nom" value="${esc(l.nom)}" placeholder="Nom du client"></label>
        <label>Lien (facultatif)<input type="url" data-champ="lien" value="${esc(l.lien)}" placeholder="https://"></label>
        <div class="aurea-options">
          <label class="aurea-interrupteur"><input type="checkbox" data-champ="visible" ${l.visible ? 'checked' : ''}><span></span>Visible</label>
          <label class="aurea-interrupteur"><input type="checkbox" data-champ="couleur" ${l.couleur ? 'checked' : ''} ${l.opaque ? 'disabled checked' : ''}><span></span>Couleurs d’origine</label>
        </div>
        ${l.opaque ? '<p class="aurea-note">Fichier sans transparence : affiché dans ses couleurs.</p>' : ''}
      </div>
      <div class="aurea-carte-actions">
        <button type="button" class="aurea-lien" data-action="remplacer">Changer l’image</button>
        <button type="button" class="aurea-lien is-danger" data-action="supprimer">Supprimer</button>
      </div>
    </li>`;

  const apercu = () => {
    const vis = logos.filter(l => l.visible && l.url);
    const items = vis.map(visuel).join('');
    $('#aurea-apercu-zone').html(vis.length
      ? `<div class="aurea-logos"><div class="aurea-logos-rang">${items}<div class="aurea-logos-copie" aria-hidden="true">${items}</div></div></div>`
      : '<p class="aurea-apercu-vide">Aucun logo visible.</p>');
  };

  const rendre = () => {
    $grille.html(logos.map(carte).join(''));
    $vide.prop('hidden', logos.length > 0);
    const n = logos.filter(l => l.visible).length;
    $compteur.text(`${n} visible${n > 1 ? 's' : ''} sur ${logos.length}`);
    apercu();
  };

  const trouver = el => logos.find(l => String(cle(l)) === String($(el).closest('.aurea-carte').data('cle')));

  // Champs
  $grille.on('input change', '[data-champ]', function () {
    const l = trouver(this), champ = this.dataset.champ;
    l[champ] = this.type === 'checkbox' ? this.checked : this.value;
    marquer();
    if (this.type === 'checkbox') rendre();
  });

  // Glisser déposer
  $grille.sortable({
    items: '.aurea-carte', handle: '.aurea-carte-visuel', placeholder: 'aurea-carte is-place', tolerance: 'pointer',
    update: () => {
      const ordre = $grille.children('.aurea-carte').map((i, el) => String($(el).data('cle'))).get();
      logos.sort((a, b) => ordre.indexOf(String(cle(a))) - ordre.indexOf(String(cle(b))));
      marquer(); apercu();
    },
  });

  // Médiathèque
  const choisir = (multiple, titre, rappel) => {
    const cadre = wp.media({ title: titre, library: { type: 'image' }, multiple, button: { text: multiple ? 'Ajouter ces logos' : 'Utiliser cette image' } });
    cadre.on('select', () => rappel(cadre.state().get('selection').toJSON()));
    cadre.open();
  };
  const depuisMedia = m => ({
    url: (m.sizes && m.sizes.medium ? m.sizes.medium.url : m.url),
    image: m.id,
    opaque: m.mime === 'image/jpeg',
  });

  $('#aurea-ajouter').on('click', () => choisir(true, 'Ajouter des logos clients', sel => {
    sel.forEach(m => logos.push({ id: 0, tmp: 'n' + m.id + '-' + Date.now(), nom: (m.alt || m.title || '').replace(/^Logo\s+/i, ''), lien: '', couleur: false, visible: true, ...depuisMedia(m) }));
    rendre(); marquer();
  }));

  $grille.on('click', '[data-action]', function () {
    const l = trouver(this);
    if (this.dataset.action === 'supprimer') {
      const $carte = $(this).closest('.aurea-carte');
      if ($carte.hasClass('is-confirmer')) {
        if (l.id) supprimes.push(l.id);
        logos = logos.filter(x => x !== l); rendre(); marquer();
      } else {
        $carte.addClass('is-confirmer'); $(this).text('Confirmer la suppression');
        setTimeout(() => { $carte.removeClass('is-confirmer'); $(this).text('Supprimer'); }, 4000);
      }
    }
    if (this.dataset.action === 'remplacer') {
      choisir(false, 'Changer l’image du logo', sel => { Object.assign(l, depuisMedia(sel[0])); rendre(); marquer(); });
    }
  });

  // Enregistrer
  $enregistrer.on('click', () => {
    $enregistrer.prop('disabled', true); etat('Enregistrement…', 'attente');
    $.post(cfg.ajax, {
      action: 'aurea_logos_enregistrer', _ajax_nonce: cfg.nonce,
      logos: JSON.stringify(logos.map(({ id, nom, image, lien, couleur, visible }) => ({ id, nom, image, lien, couleur, visible }))),
      supprimes: JSON.stringify(supprimes),
    }).done(r => {
      if (!r.success) throw new Error(r.data);
      logos = r.data; supprimes = []; modifie = false; rendre(); etat('Enregistré', 'ok');
    }).fail(x => {
      $enregistrer.prop('disabled', false);
      etat((x.responseJSON && x.responseJSON.data) || 'Échec de l’enregistrement, réessayez.', 'erreur');
    });
  });

  // Import des logos du site actuel
  const $importer = $('#aurea-importer');
  if (cfg.aImporter > 0) $importer.prop('hidden', false).text(`Importer les ${cfg.aImporter} logos du site actuel`);
  $importer.on('click', () => {
    if (modifie) { etat('Enregistrez d’abord vos modifications.', 'erreur'); return; }
    $importer.prop('disabled', true).text('Import en cours…');
    $.post(cfg.ajax, { action: 'aurea_logos_importer', _ajax_nonce: cfg.nonce }).done(r => {
      if (!r.success) throw new Error(r.data);
      logos = r.data.logos; rendre(); $importer.prop('hidden', true);
      etat(`${r.data.ajoutes} logo${r.data.ajoutes > 1 ? 's' : ''} importé${r.data.ajoutes > 1 ? 's' : ''}`, 'ok');
    }).fail(x => {
      $importer.prop('disabled', false).text('Réessayer l’import');
      etat((x.responseJSON && x.responseJSON.data) || 'Échec de l’import.', 'erreur');
    });
  });

  window.addEventListener('beforeunload', e => { if (modifie) { e.preventDefault(); e.returnValue = ''; } });
  rendre();
})(jQuery);
