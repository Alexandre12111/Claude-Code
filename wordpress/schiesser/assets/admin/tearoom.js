/* Rubriques du Tea Room : choix de la photo dans la médiathèque. */
(function ($) {
  if (!window.wp || !wp.media) return;

  $(document).on('click', '.js-rubrique-choisir', function (e) {
    e.preventDefault();
    var bloc = $(this).closest('.js-rubrique-photo');
    var cadre = wp.media({ title: 'Photo de la rubrique', button: { text: 'Utiliser cette photo' }, library: { type: 'image' }, multiple: false });
    cadre.on('select', function () {
      var m = cadre.state().get('selection').first().toJSON();
      var url = (m.sizes && m.sizes.medium && m.sizes.medium.url) || m.url;
      bloc.find('input[name="rubrique_photo"]').val(m.id);
      bloc.find('.js-rubrique-apercu').html($('<img>', { src: url, alt: '' }).css({ maxWidth: '220px', height: 'auto', display: 'block', marginBottom: '8px' }));
      bloc.find('.js-rubrique-choisir').text('Changer la photo');
      bloc.find('.js-rubrique-retirer').prop('hidden', false);
    });
    cadre.open();
  });

  $(document).on('click', '.js-rubrique-retirer', function (e) {
    e.preventDefault();
    var bloc = $(this).closest('.js-rubrique-photo');
    bloc.find('input[name="rubrique_photo"]').val('');
    bloc.find('.js-rubrique-apercu').empty();
    bloc.find('.js-rubrique-choisir').text('Choisir une photo');
    $(this).prop('hidden', true);
  });

  // Après l'ajout d'une rubrique (formulaire de gauche, envoyé sans recharger la page), on vide le champ photo.
  $(document).ajaxComplete(function (evt, xhr, reglages) {
    if (reglages && reglages.data && String(reglages.data).indexOf('action=add-tag') !== -1 && xhr.responseText && xhr.responseText.indexOf('wp_error') === -1) {
      $('#addtag .js-rubrique-retirer').trigger('click');
    }
  });
})(jQuery);
