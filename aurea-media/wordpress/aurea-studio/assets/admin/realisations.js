/* Aurea Studio : écran Réalisations */
(($) => {
  const cfg = window.aureaReal;
  let items = cfg.items.map(r => ({ ...r, type: r.type.slug, secteur: r.secteur.slug }));
  let supprimes = [];
  let modifie = false;
  let filtre = '';

  const $grille = $('#aurea-r-grille'), $etat = $('#aurea-r-etat'), $enregistrer = $('#aurea-r-enregistrer');
  const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const cle = r => r.id || r.tmp;
  const etat = (t, type = '') => $etat.text(t).attr('data-type', type);
  const marquer = () => { modifie = true; $enregistrer.prop('disabled', false); etat('Modifications non enregistrées', 'attente'); };
  const options = (liste, val) => '<option value="">Aucun</option>' + liste.map(t => `<option value="${esc(t.slug)}" ${t.slug === val ? 'selected' : ''}>${esc(t.nom)}</option>`).join('');
  const inter = (champ, label, on) => `<label class="aurea-interrupteur"><input type="checkbox" data-champ="${champ}" ${on ? 'checked' : ''}><span></span>${label}</label>`;
  const domaine = url => { try { return new URL(url).hostname.replace(/^www\./, ''); } catch (e) { return ''; } };

  const carte = r => `
    <li class="aurea-carte is-real-carte${r.visible ? '' : ' is-masque'}" data-cle="${esc(cle(r))}">
      <div class="aurea-carte-visuel is-capture" title="Glisser pour déplacer">
        <span class="aurea-poignee" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i></span>
        <span class="aurea-nav"><i></i><i></i><i></i><b>${esc(domaine(r.url) || 'site-du-client.fr')}</b></span>
        ${r.image_url ? `<img src="${esc(r.image_url)}" alt="">` : '<span class="aurea-sans">Pas encore de capture</span>'}
        <span class="aurea-pastilles">${r.une ? '<span class="aurea-pastille is-or">À la une</span>' : ''}${r.mur ? '<span class="aurea-pastille">Mur</span>' : ''}${r.visible ? '' : '<span class="aurea-pastille">Masquée</span>'}</span>
      </div>
      <div class="aurea-carte-champs">
        <label>Nom du projet<input type="text" data-champ="titre" value="${esc(r.titre)}"></label>
        <label>Adresse du site<input type="url" data-champ="url" value="${esc(r.url)}" placeholder="https://"></label>
        <div class="aurea-duo">
          <label>Type<select data-champ="type">${options(cfg.termes.type, r.type)}</select></label>
          <label>Secteur<select data-champ="secteur">${options(cfg.termes.secteur, r.secteur)}</select></label>
        </div>
        <label>Lieu<input type="text" data-champ="lieu" value="${esc(r.lieu)}" placeholder="Paris, Genève…"></label>
        <label>Accroche (une phrase)<textarea data-champ="accroche" rows="2">${esc(r.accroche)}</textarea></label>
        <div class="aurea-options">${inter('visible', 'Visible', r.visible)}${inter('une', 'À la une', r.une)}${inter('mur', 'Mur du hero', r.mur)}</div>
      </div>
      <div class="aurea-carte-actions is-col">
        <div><button type="button" class="aurea-lien" data-action="capture">Changer la capture</button>
        <button type="button" class="aurea-lien" data-action="mobile">${r.mobile ? 'Capture mobile ✓' : 'Ajouter la capture mobile'}</button></div>
        <div>${r.id ? `<a class="aurea-lien" href="post.php?post=${r.id}&action=edit">Fiche complète</a>` : '<span class="aurea-note">Fiche complète après enregistrement</span>'}
        <button type="button" class="aurea-lien is-danger" data-action="supprimer">Supprimer</button></div>
      </div>
    </li>`;

  const garder = r => !filtre || (filtre === 'masque' ? !r.visible : r[filtre]);
  const rendre = () => {
    $grille.html(items.filter(garder).map(carte).join(''));
    $('#aurea-r-vide').prop('hidden', items.length > 0);
    const v = items.filter(r => r.visible).length, u = items.filter(r => r.visible && r.une).length, m = items.filter(r => r.visible && r.mur).length;
    $('#aurea-r-compteur').text(`${v} visibles · ${u} à la une · ${m} dans le mur`);
    $grille.sortable(filtre ? 'disable' : 'enable');
  };
  const trouver = el => items.find(r => String(cle(r)) === String($(el).closest('.aurea-carte').data('cle')));

  $grille.on('input change', '[data-champ]', function () {
    const r = trouver(this);
    r[this.dataset.champ] = this.type === 'checkbox' ? this.checked : this.value;
    marquer();
    if (this.type === 'checkbox') rendre();
  });

  $grille.sortable({
    items: '.aurea-carte', handle: '.aurea-carte-visuel', placeholder: 'aurea-carte is-place', tolerance: 'pointer',
    update: () => {
      const ordre = $grille.children('.aurea-carte').map((i, el) => String($(el).data('cle'))).get();
      items.sort((a, b) => ordre.indexOf(String(cle(a))) - ordre.indexOf(String(cle(b))));
      marquer();
    },
  });

  $('#aurea-r-filtre').on('change', function () { filtre = this.value; rendre(); });

  const choisir = (titre, rappel) => {
    const cadre = wp.media({ title: titre, library: { type: 'image' }, multiple: false, button: { text: 'Utiliser cette image' } });
    cadre.on('select', () => rappel(cadre.state().get('selection').first().toJSON()));
    cadre.open();
  };
  const urlImage = m => (m.sizes && (m.sizes.large || m.sizes.medium) ? (m.sizes.large || m.sizes.medium).url : m.url);

  $('#aurea-r-ajouter').on('click', () => choisir('Capture d’écran du site', m => {
    items.unshift({ id: 0, tmp: 'n' + Date.now(), titre: (m.title || '').replace(/^(Capture|Screenshot)[\s-]*/i, '') || 'Nouvelle réalisation', url: '', type: 'site-vitrine', secteur: '', lieu: '', accroche: '', image: m.id, image_url: urlImage(m), mobile: 0, une: false, mur: true, visible: true });
    filtre = ''; $('#aurea-r-filtre').val(''); rendre(); marquer();
    $grille.find('.aurea-carte').first().find('[data-champ=titre]').trigger('focus').select();
  }));

  $grille.on('click', '[data-action]', function () {
    const r = trouver(this), action = this.dataset.action;
    if (action === 'capture') choisir('Capture d’écran du site', m => { r.image = m.id; r.image_url = urlImage(m); rendre(); marquer(); });
    if (action === 'mobile') choisir('Capture mobile (format téléphone)', m => { r.mobile = m.id; rendre(); marquer(); });
    if (action === 'supprimer') {
      const $c = $(this).closest('.aurea-carte');
      if ($c.hasClass('is-confirmer')) {
        if (r.id) supprimes.push(r.id);
        items = items.filter(x => x !== r); rendre(); marquer();
      } else {
        $c.addClass('is-confirmer'); $(this).text('Confirmer (corbeille)');
        setTimeout(() => { $c.removeClass('is-confirmer'); $(this).text('Supprimer'); }, 4000);
      }
    }
  });

  $enregistrer.on('click', () => {
    $enregistrer.prop('disabled', true); etat('Enregistrement…', 'attente');
    $.post(cfg.ajax, {
      action: 'aurea_realisations_enregistrer', _ajax_nonce: cfg.nonce,
      items: JSON.stringify(items.map(({ id, titre, url, type, secteur, lieu, accroche, image, mobile, une, mur, visible }) => ({ id, titre, url, type, secteur, lieu, accroche, image, mobile, une, mur, visible }))),
      supprimes: JSON.stringify(supprimes),
    }).done(r => {
      if (!r.success) throw new Error(r.data);
      items = r.data.map(x => ({ ...x, type: x.type.slug, secteur: x.secteur.slug })); supprimes = []; modifie = false; rendre(); etat('Enregistré, le site est à jour', 'ok');
    }).fail(x => { $enregistrer.prop('disabled', false); etat((x.responseJSON && x.responseJSON.data) || 'Échec de l’enregistrement, réessayez.', 'erreur'); });
  });

  const $imp = $('#aurea-r-importer');
  if (cfg.aImporter > 0) $imp.prop('hidden', false).text(`Importer les ${cfg.aImporter} projets du site actuel`);
  $imp.on('click', () => {
    if (modifie) { etat('Enregistrez d’abord vos modifications.', 'erreur'); return; }
    $imp.prop('disabled', true).text('Import en cours…');
    $.post(cfg.ajax, { action: 'aurea_realisations_importer', _ajax_nonce: cfg.nonce }).done(r => {
      if (!r.success) throw new Error(r.data);
      items = r.data.items.map(x => ({ ...x, type: x.type.slug, secteur: x.secteur.slug })); rendre(); $imp.prop('hidden', true);
      etat(`${r.data.ajoutes} projets importés`, 'ok');
    }).fail(x => { $imp.prop('disabled', false).text('Réessayer l’import'); etat((x.responseJSON && x.responseJSON.data) || 'Échec de l’import.', 'erreur'); });
  });

  window.addEventListener('beforeunload', e => { if (modifie) { e.preventDefault(); e.returnValue = ''; } });
  rendre();
})(jQuery);
