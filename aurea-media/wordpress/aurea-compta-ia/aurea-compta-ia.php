<?php
/**
 * Plugin Name:       Aurea Compta IA
 * Description:       Complément facultatif d'Aurea Compta : fournit le SDK officiel d'Anthropic (Claude) pour la lecture automatique des justificatifs et l'assistant. Chargé uniquement au moment d'un appel à l'IA.
 * Version:           1.0.0
 * Requires PHP:      8.1
 * Requires Plugins:  aurea-compta
 * Author:            Aurea Media
 * License:           Propriétaire (SDK Anthropic : licence Apache 2.0, voir vendor/)
 */

defined( 'ABSPATH' ) || exit;

/** Charge le SDK à la demande (évite tout conflit avec d'autres extensions le reste du temps). */
function aurea_compta_ia_autoload(): void {
	static $done = false;
	if ( ! $done ) {
		require_once __DIR__ . '/vendor/autoload.php';
		$done = true;
	}
}
