<?php
/**
 * Désinstallation d'Aurea Compta : aucune donnée comptable n'est supprimée.
 * Les pièces justificatives et les livres doivent être conservés 10 ans (C. com. L123-22).
 * Les tables (préfixe ac_) et le dossier privé des pièces restent sur l'hébergement ;
 * réinstaller l'extension les retrouve intacts.
 */
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;
wp_clear_scheduled_hook( 'aurea_compta_hourly' );
