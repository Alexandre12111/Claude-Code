<?php
/* Extrait tous les textes du thème (pages, carte du Tea Room, produits) en allemand et en français. */
define( 'WP_ADMIN', true );
require '/tmp/wpt/wp65/wp-load.php';
add_filter( 'pre_http_request', function () { return new WP_Error( 'hors_ligne', 'hors ligne' ); } ); // pas de téléchargement de photo

$NOMS = array(
	'surtitre' => 'Surtitre', 'titre' => 'Titre', 'texte' => 'Texte', 'note' => 'Note sous le titre', 'lead' => 'Introduction',
	'legende' => 'Légende de la photo', 'figure' => 'Numéro de figure', 'bouton1Texte' => 'Bouton 1', 'bouton2Texte' => 'Bouton 2',
	'b1Texte' => 'Bouton 1', 'b2Texte' => 'Bouton 2', 'boutonTexte' => 'Bouton', 'lienTexte' => 'Lien', 'imageAlt' => 'Description de la photo (référencement)',
	'avantAlt' => 'Description de la photo « avant »', 'apresAlt' => 'Description de la photo « après »', 'avantLibelle' => 'Étiquette « avant »', 'apresLibelle' => 'Étiquette « après »',
	'onglet' => 'Onglet', 'question' => 'Question', 'reponse' => 'Réponse', 'libelle' => 'Libellé', 'detail' => 'Détail', 'valeur' => 'Valeur',
	'encartTitre' => 'Titre de l’encart', 'sousTitre' => 'Sous-titre', 'mention' => 'Mention', 'conseil' => 'Conseil', 'conseilLibelle' => 'Libellé du conseil',
	'indication' => 'Indication', 'description' => 'Description', 'meta' => 'Date / précision', 'retenir' => 'À retenir', 'retenirLibelle' => 'Libellé « à retenir »',
	'annee' => 'Année', 'auteur' => 'Auteur', 'credit' => 'Crédit des photos', 'heure' => 'Heure', 'depart' => 'Départ', 'duree' => 'Durée', 'changements' => 'Changements',
	'marche' => 'Marche à pied', 'astuce' => 'Astuce', 'departLibelle' => 'Libellé « départ »', 'titreHoraires' => 'Titre des horaires', 'lNom' => 'Champ « nom »',
	'lEmail' => 'Champ « e-mail »', 'lTel' => 'Champ « téléphone »', 'lMessage' => 'Champ « message »', 'mentionLegale' => 'Mention sous le formulaire',
	'okTitre' => 'Message envoyé : titre', 'okTexte' => 'Message envoyé : texte', 'nombre' => 'Nombre', 'dates' => 'Dates', 'c1Libelle' => 'Colonne 1 : libellé',
	'c1Valeur' => 'Colonne 1 : valeur', 'c1Detail' => 'Colonne 1 : détail', 'c2Libelle' => 'Colonne 2 : libellé', 'c2Valeur' => 'Colonne 2 : valeur', 'c2Detail' => 'Colonne 2 : détail',
	'sSurtitre' => 'Suggestion : surtitre', 'sPied' => 'Suggestion : pied', 'sTitre' => 'Suggestion : titre', 'sTexte' => 'Suggestion : texte', 'avisTexte' => 'Lien vers les avis',
	'niveau' => 'Étage', 'lieu' => 'Lieu', 'nom' => 'Nom', 'prix' => 'Prix',
);
$IGNORER = array( 'numero', 'icone' );
$blocs_def = schiesser_mq_blocs();
$titre_bloc = function ( $nom ) use ( $blocs_def ) {
	$court = str_replace( 'schiesser/', '', $nom );
	if ( isset( $blocs_def[ $court ] ) ) { return $blocs_def[ $court ][0]; }
	$t = WP_Block_Type_Registry::get_instance()->get_registered( $nom );
	return $t && $t->title ? $t->title : $nom;
};

/* Textes d'une page : liste d'éléments [chemin, bloc, cle, texte]. */
$elements = function ( $html ) use ( $IGNORER ) {
	$out  = array();
	$walk = function ( $blocs, $base ) use ( &$walk, &$out, $IGNORER ) {
		$i = 0;
		foreach ( $blocs as $b ) {
			if ( empty( $b['blockName'] ) ) { continue; }
			$chemin = array_merge( $base, array( $i ) );
			if ( in_array( $b['blockName'], array( 'core/paragraph', 'core/heading', 'core/list-item', 'core/quote' ), true ) ) {
				$t = trim( preg_replace( '#^<(p|h\d|li)[^>]*>|</(p|h\d|li)>$#', '', trim( $b['innerHTML'] ) ) );
				if ( '' !== trim( wp_strip_all_tags( $t ) ) ) { $out[] = array( implode( '.', $chemin ), $b['blockName'], 'contenu', $t ); }
			}
			foreach ( (array) $b['attrs'] as $k => $v ) {
				if ( in_array( $k, $IGNORER, true ) || ! schiesser_tl_cle_texte( $k, $v ) || '' === trim( (string) $v ) ) { continue; }
				$out[] = array( implode( '.', $chemin ), $b['blockName'], $k, $v );
			}
			if ( ! empty( $b['innerBlocks'] ) ) { $walk( $b['innerBlocks'], $chemin ); }
			++$i;
		}
	};
	$walk( parse_blocks( $html ), array() );
	return $out;
};

$meta  = schiesser_tr_pages();
$codes = array( 'startseite' => 'A', 'confiserie' => 'C', 'tea-room' => 'T', 'geschichte' => 'H', 'besuch' => 'V', 'kontakt' => 'K', 'firmengeschenke' => 'F' );
$doc   = array( 'pages' => array(), 'carte' => array(), 'boutique' => array() );
foreach ( schiesser_demo_pages() as $p ) {
	$slug = $p['slug'];
	$code = $codes[ $slug ] ?? strtoupper( substr( $slug, 0, 1 ) );
	$de   = $elements( $p['contenu'] );
	$fr   = $elements( schiesser_traduire_blocs( $p['contenu'], 'fr' ) );
	$en   = $elements( schiesser_traduire_blocs( $p['contenu'], 'en' ) );
	$fr_i = array();
	$en_i = array();
	foreach ( $fr as $e ) { $fr_i[ $e[0] . '|' . $e[2] ][] = $e[3]; }
	foreach ( $en as $e ) { $en_i[ $e[0] . '|' . $e[2] ][] = $e[3]; }
	$page = array( 'code' => $code, 'slug' => $slug, 'titre_de' => $p['titre'], 'titre_fr' => $meta[ $slug ]['fr'][0] ?? '', 'slug_fr' => $meta[ $slug ]['fr'][1] ?? '',
		'titre_en' => $meta[ $slug ]['en'][0] ?? '', 'slug_en' => $meta[ $slug ]['en'][1] ?? '', 'statut' => $p['statut'] ?? 'publish', 'sections' => array() );
	$n = 0; $sec = null;
	foreach ( $de as $e ) {
		list( $chemin, $bloc, $cle, $texte ) = $e;
		$racine = explode( '.', $chemin )[0];
		if ( ! $sec || $sec['racine'] !== $racine ) {
			if ( $sec ) { $page['sections'][] = $sec; }
			$sec = array( 'racine' => $racine, 'bloc' => $titre_bloc( $bloc ), 'titre' => '', 'lignes' => array() );
		}
		if ( '' === $sec['titre'] && 'titre' === $cle && $chemin === $racine ) { $sec['titre'] = wp_strip_all_tags( $texte ); }
		$k = $chemin . '|' . $cle;
		$fr_t = isset( $fr_i[ $k ] ) ? array_shift( $fr_i[ $k ] ) : '';
		$en_t = isset( $en_i[ $k ] ) ? array_shift( $en_i[ $k ] ) : '';
		++$n;
		$sec['lignes'][] = array(
			'ref' => sprintf( '%s-%03d', $code, $n ), 'chemin' => $chemin, 'bloc' => $bloc, 'cle' => $cle,
			'element' => ( $chemin !== $racine ? $titre_bloc( $bloc ) . ' · ' : '' ) . ( 'contenu' === $cle ? 'Paragraphe' : ( $NOMS[ $cle ] ?? $cle ) ),
			'de' => $texte, 'fr' => $fr_t, 'en' => $en_t,
		);
	}
	if ( $sec ) { $page['sections'][] = $sec; }
	$doc['pages'][] = $page;
}

/* Carte du Tea Room */
$dico = schiesser_tr_carte(); $n = 0;
foreach ( schiesser_getraenkekarte() as $r ) {
	$rub = array( 'ref' => sprintf( 'M-%03d', ++$n ), 'de' => $r['nom'], 'fr' => schiesser_tr( $dico, $r['nom'], 'fr' ), 'plats' => array() );
	foreach ( $r['plats'] as $pl ) {
		$rub['plats'][] = array( 'ref' => sprintf( 'M-%03d', ++$n ), 'nom_de' => $pl[0], 'nom_fr' => schiesser_tr( $dico, $pl[0], 'fr' ), 'prix' => $pl[1],
			'desc_de' => $pl[2], 'desc_fr' => '' === $pl[2] ? '' : schiesser_tr( $dico, $pl[2], 'fr' ), 'mention_de' => $pl[3] ?? '', 'mention_fr' => empty( $pl[3] ) ? '' : schiesser_tr( $dico, $pl[3], 'fr' ) );
	}
	$doc['carte'][] = $rub;
}
$doc['suggestion'] = schiesser_getraenkekarte_suggestion();

/* Boutique */
$dico = schiesser_tr_boutique(); $n = 0;
foreach ( schiesser_preisliste() as $rubrique => $produits ) {
	$rub = array( 'ref' => sprintf( 'P-%03d', ++$n ), 'de' => $rubrique, 'fr' => schiesser_tr( $dico, $rubrique, 'fr' ), 'produits' => array() );
	foreach ( $produits as $p ) {
		list( $nom, $formats, $accroche ) = $p;
		$rub['produits'][] = array( 'ref' => sprintf( 'P-%03d', ++$n ), 'nom_de' => $nom, 'nom_fr' => schiesser_tr( $dico, $nom, 'fr' ),
			'accroche_de' => $accroche, 'accroche_fr' => '' === $accroche ? '' : schiesser_tr( $dico, $accroche, 'fr' ),
			'formats' => array_map( function ( $f ) use ( $dico ) { return array( $f[0], '' === $f[0] ? '' : schiesser_tr( $dico, $f[0], 'fr' ), $f[1] ); }, $formats ) );
	}
	$doc['boutique'][] = $rub;
}
file_put_contents( $argv[1], wp_json_encode( $doc, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
$total = 0; foreach ( $doc['pages'] as $p ) { foreach ( $p['sections'] as $s ) { $total += count( $s['lignes'] ); } }
echo count( $doc['pages'] ) . " pages, $total textes de pages, " . count( $doc['carte'] ) . " rubriques de carte, " . count( $doc['boutique'] ) . " catégories\n";
