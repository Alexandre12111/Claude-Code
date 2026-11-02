<?php
/**
 * Codes courts des parties dynamiques : même balisage que la maquette (dualtech/maquette/outils/pages.py),
 * alimenté par DualTech Studio (partenaires, intervenants, programme).
 *
 * [dualtech_orateurs nombre="12"]                         accueil : grille des premiers intervenants
 * [dualtech_intervenants partie="mosaique|filtres"]       page Intervenants : mosaïque, filtres, cartes
 * [dualtech_nombre_intervenants]                          nombre d'intervenants
 * [dualtech_programme partie="filtres"]                   page Programme : filtres et journée
 * [dualtech_temps_forts partie="regle|horloge"]           L'événement : frise des temps forts
 * [dualtech_partenaires]                                  page Partenaires : une rangée par rubrique
 * [dualtech_logos rubrique="prix" libelle="…"]            logos d'une rubrique (page Prix)
 * [dualtech_logos_defile]                                 L'événement : deux rangées de logos défilants
 * [dualtech_partenaires_desc rubrique="edition-2025"]     Rétrospective : logos avec description
 */

defined( 'ABSPATH' ) || exit;

/** Typographie française de la maquette : espace insécable avant ? ! : ; » et après «. */
function dt_typo( $texte ) {
	$texte = preg_replace( '/ ([?!:;»])/u', "\u{00A0}$1", (string) $texte );
	$texte = str_replace( '« ', "«\u{00A0}", $texte );
	$texte = preg_replace( '/(\d) (\d{3})/u', "$1\u{00A0}$2", $texte );
	return preg_replace( '/(\d) (€|%|h\b|min\b)/u', "$1\u{00A0}$2", $texte );
}

/** Texte échappé et mis en forme (typographie française). */
function dt_e( $texte ) {
	return dt_typo( esc_html( $texte ) );
}

/** Clé de recherche d'un intervenant : comme slug() de la maquette, tirets remplacés par des espaces. */
function dt_cherche( $texte ) {
	$t = strtolower( remove_accents( (string) $texte ) );
	return str_replace( '-', ' ', trim( preg_replace( '/[^a-z0-9]+/', '-', $t ), '-' ) );
}

/** Icône flèche de la maquette (tracé Phosphor, licence MIT). */
function dt_ico_fleche() {
	return '<svg viewBox="0 0 256 256" fill="currentColor" aria-hidden="true" focusable="false"><path d="M221.66 133.66l-72 72a8 8 0 0 1-11.32-11.32L196.69 136H40a8 8 0 0 1 0-16h156.69l-58.35-58.34a8 8 0 0 1 11.32-11.32l72 72a8 8 0 0 1 0 11.32Z"/></svg>';
}

/* ------------------------------------------------------------------ */
/* Intervenants                                                        */
/* ------------------------------------------------------------------ */

function dt_bloc_orateur( $i ) {
	return '<li class="orateur" data-incline data-cherche="' . esc_attr( dt_cherche( $i['nom'] . ' ' . $i['fonction'] . ' ' . $i['orga'] ) ) . '">' . "\n"
		. '  <div class="orateur__photo"><img src="' . esc_url( $i['photo'] ) . '" alt="' . esc_attr( $i['nom'] . ', ' . $i['fonction'] . ', ' . $i['orga'] ) . '" width="480" height="480" loading="lazy"></div>' . "\n"
		. '  <h3>' . dt_e( $i['nom'] ) . '</h3>' . "\n"
		. '  <p>' . dt_e( $i['fonction'] ) . '<strong>' . dt_e( $i['orga'] ) . '</strong></p>' . "\n</li>";
}

add_shortcode( 'dualtech_orateurs', function ( $atts ) {
	$atts = shortcode_atts( array( 'nombre' => 12 ), $atts );
	return implode( '', array_map( 'dt_bloc_orateur', array_slice( array_values( dt_intervenants() ), 0, (int) $atts['nombre'] ) ) );
} );

add_shortcode( 'dualtech_nombre_intervenants', function () {
	return (string) count( dt_intervenants() );
} );

add_shortcode( 'dualtech_intervenants', function ( $atts ) {
	$atts  = shortcode_atts( array( 'partie' => 'cartes' ), $atts );
	$tous  = array_values( dt_intervenants() );
	$lib   = dualtech_univers();
	$types = dualtech_types();
	if ( 'mosaique' === $atts['partie'] ) {
		$liste = array_slice( array_merge( $tous, $tous ), 0, 30 );
		return implode( '', array_map( function ( $i ) {
			return '<img src="' . esc_url( $i['photo'] ) . '" alt="" width="120" height="120" loading="lazy">';
		}, $liste ) );
	}
	if ( 'filtres' === $atts['partie'] ) {
		$sortie = '<button type="button" data-univers="tous" aria-pressed="true">Tous<span class="n">' . count( $tous ) . '</span></button>';
		foreach ( $lib as $cle => $nom ) {
			$n       = count( array_filter( $tous, function ( $i ) use ( $cle ) {
				return $cle === $i['univers'];
			} ) );
			$sortie .= '<button type="button" data-univers="' . esc_attr( $cle ) . '" aria-pressed="false">' . esc_html( $nom ) . '<span class="n">' . $n . '</span></button>';
		}
		return $sortie;
	}
	$sortie = '';
	foreach ( $tous as $n => $i ) {
		$seances = dt_seances_de( $i['id'] );
		$sess    = array_map( function ( $s ) {
			return array( 'h' => $s['debut'] . ' à ' . $s['fin'], 't' => $s['titre'], 'r' => $s['role'] );
		}, $seances );
		$prem    = $seances[0] ?? null;
		$voile   = $prem ? '<small>' . esc_html( $prem['debut'] ) . ' · ' . esc_html( $types[ $prem['type'] ] ?? '' ) . '</small><span>' . dt_e( $prem['titre'] ) . '</span>' : '<small>Intervenant 2026</small>';
		$chips   = '';
		foreach ( $seances as $s ) {
			$chips .= '<span class="iv-chip iv-chip--' . esc_attr( $s['type'] ) . '"><b>' . esc_html( $s['debut'] ) . '</b>' . esc_html( $types[ $s['type'] ] ?? '' ) . '</span>';
		}
		$chips   = $chips ?: '<span class="iv-chip"><b>2026</b>Horaire à venir</span>';
		$sortie .= '<article class="iv-carte" data-univers="' . esc_attr( $i['univers'] ) . '" data-cherche="' . esc_attr( dt_cherche( $i['nom'] . ' ' . $i['fonction'] . ' ' . $i['orga'] ) ) . '" data-nom="' . esc_attr( $i['nom'] ) . '" data-fonction="' . esc_attr( $i['fonction'] ) . '" '
			. 'data-orga="' . esc_attr( $i['orga'] ) . '" data-photo="' . esc_url( $i['photo'] ) . '" data-sessions="' . esc_attr( wp_json_encode( $sess, JSON_UNESCAPED_UNICODE ) ) . '" data-incline>'
			. '<button type="button" class="iv-carte__ouvrir" aria-label="' . esc_attr( 'Voir la fiche de ' . $i['nom'] ) . '"></button>'
			. '<div class="iv-carte__photo"><img src="' . esc_url( $i['photo'] ) . '" alt="" width="480" height="480" loading="lazy"><span class="iv-carte__univers">' . esc_html( $lib[ $i['univers'] ] ?? '' ) . '</span>'
			. '<span class="iv-carte__plus" aria-hidden="true">' . dt_ico_fleche() . '</span><span class="iv-carte__voile" aria-hidden="true">' . $voile . '</span></div>'
			. '<div class="iv-carte__texte"><span class="iv-carte__num">' . sprintf( '%02d', $n + 1 ) . '</span><h2>' . dt_e( $i['nom'] ) . '</h2><p>' . dt_e( $i['fonction'] ) . '<strong>' . dt_e( $i['orga'] ) . '</strong></p></div><div class="iv-carte__passages">' . $chips . '</div><span class="iv-carte__fleche" aria-hidden="true">' . dt_ico_fleche() . '</span></article>';
	}
	return $sortie;
} );

/* ------------------------------------------------------------------ */
/* Programme                                                           */
/* ------------------------------------------------------------------ */

/** Intervenant dans le programme (photo ronde, nom, fonction). */
function dt_interv_html( $id ) {
	$tous = dt_intervenants();
	$i    = $tous[ $id ] ?? null;
	if ( ! $i ) {
		return '';
	}
	return '<div class="interv"><img src="' . esc_url( $i['photo'] ) . '" alt="" width="44" height="44" loading="lazy"><span><b>' . dt_e( $i['nom'] ) . '</b><span>' . dt_e( $i['fonction'] . ', ' . $i['orga'] ) . '</span></span></div>';
}

/** Partie de la journée d'après l'heure de début. */
function dt_partie_journee( $debut ) {
	if ( strcmp( $debut, '12h00' ) < 0 ) {
		return 'Matinée';
	}
	return strcmp( $debut, '14h00' ) < 0 ? 'Mi-journée' : 'Après-midi';
}

add_shortcode( 'dualtech_programme', function ( $atts ) {
	$atts    = shortcode_atts( array( 'partie' => 'journee' ), $atts );
	$seances = dt_seances();
	$types   = dualtech_types();
	$filtre  = function ( $type ) {
		return 'temps-fort' === $type ? 'autres' : $type;
	};
	if ( 'filtres' === $atts['partie'] ) {
		$filtres = array( 'tous' => 'Tous', 'keynote' => 'Keynotes', 'table' => 'Tables rondes', 'atelier' => 'Ateliers', 'networking' => 'Networking', 'autres' => 'Autres' );
		$sortie  = '';
		foreach ( $filtres as $cle => $nom ) {
			$n       = 'tous' === $cle ? count( $seances ) : count( array_filter( $seances, function ( $s ) use ( $cle, $filtre ) {
				return $filtre( $s['type'] ) === $cle;
			} ) );
			$sortie .= '<button type="button" data-filtre="' . esc_attr( $cle ) . '" aria-pressed="' . ( 'tous' === $cle ? 'true' : 'false' ) . '">' . esc_html( $nom ) . '<span class="n">' . $n . '</span></button>';
		}
		return $sortie;
	}
	$parties = array();
	foreach ( $seances as $s ) {
		$parties[ dt_partie_journee( $s['debut'] ) ][] = $s;
	}
	$sortie = '';
	foreach ( $parties as $nom => $liste ) {
		$items = '';
		foreach ( $liste as $s ) {
			$iv     = $s['intervenants'] ? '<div class="intervs">' . implode( '', array_map( 'dt_interv_html', $s['intervenants'] ) ) . '</div>' : '';
			$md     = $s['moderateurs'] ? '<div class="moderation"><p>Modération</p><div class="intervs">' . implode( '', array_map( 'dt_interv_html', $s['moderateurs'] ) ) . '</div></div>' : '';
			$nt     = $s['note'] ? '<p class="creneau__note">' . dt_e( $s['note'] ) . '</p>' : '';
			$items .= '<article class="creneau' . ( 'networking' === $s['type'] ? ' creneau--leger' : '' ) . '" data-type="' . esc_attr( $filtre( $s['type'] ) ) . '">' . "\n"
				. '  <p class="creneau__heure chiffre"><time datetime="2026-11-26T' . esc_attr( str_replace( 'h', ':', $s['debut'] ) ) . '">' . esc_html( $s['debut'] ) . '</time><span>' . esc_html( $s['fin'] ) . '</span></p>' . "\n"
				. '  <div><span class="badge badge--' . esc_attr( $s['type'] ) . '">' . esc_html( $types[ $s['type'] ] ?? '' ) . '</span><h3>' . dt_e( $s['titre'] ) . '</h3>' . $nt . $iv . $md . "</div>\n</article>";
		}
		$sortie .= '<div class="partie">' . "\n"
			. '  <div class="partie__titre"><h2>' . esc_html( $nom ) . '</h2><p>' . esc_html( $liste[0]['debut'] . ' à ' . end( $liste )['fin'] ) . '</p></div>' . "\n"
			. '  <div class="creneaux"><span class="creneaux__fil" aria-hidden="true"></span>' . $items . "</div>\n</div>";
	}
	return $sortie;
} );

/* ------------------------------------------------------------------ */
/* Temps forts (L'événement)                                           */
/* ------------------------------------------------------------------ */

/** « Table ronde : « Titre » » → « Titre » (la frise affiche le format dans un badge). */
function dt_titre_court( $titre ) {
	return preg_match( '/^(?:Table ronde|Keynote)\s*:\s*«\s*(.+?)\s*»$/u', $titre, $m ) ? $m[1] : $titre;
}

add_shortcode( 'dualtech_temps_forts', function ( $atts ) {
	$atts  = shortcode_atts( array( 'partie' => 'cartes' ), $atts );
	$liste = array_values( array_filter( dt_seances(), function ( $s ) {
		return $s['temps_fort'];
	} ) );
	if ( ! $liste ) {
		return '';
	}
	if ( 'horloge' === $atts['partie'] ) {
		return esc_html( $liste[0]['debut'] );
	}
	if ( 'regle' === $atts['partie'] ) {
		$n      = max( 1, count( $liste ) - 1 );
		$sortie = '';
		foreach ( $liste as $k => $s ) {
			$sortie .= '<span style="left:' . number_format( $k / $n * 100, 1, '.', '' ) . '%">' . esc_html( $s['debut'] ) . '</span>';
		}
		return $sortie;
	}
	$types  = dualtech_types();
	$tous   = dt_intervenants();
	$sortie = '';
	foreach ( $liste as $s ) {
		$texte = $s['resume'];
		if ( ! $texte ) {
			$noms  = array_filter( array_map( function ( $id ) use ( $tous ) {
				return $tous[ $id ]['nom'] ?? '';
			}, $s['intervenants'] ) );
			$texte = implode( ', ', $noms );
		}
		$sortie .= '<li data-heure="' . esc_attr( $s['debut'] ) . '"><span class="temps__grand" aria-hidden="true">' . esc_html( substr( $s['debut'], 0, 2 ) ) . '</span><span class="temps__heure">' . esc_html( $s['debut'] ) . '</span><span class="badge badge--' . esc_attr( $s['type'] ) . '">' . esc_html( $types[ $s['type'] ] ?? '' ) . '</span><h3>' . dt_e( dt_titre_court( $s['titre'] ) ) . '</h3><p>' . dt_e( $texte ) . '</p></li>';
	}
	return $sortie;
} );

/* ------------------------------------------------------------------ */
/* Partenaires                                                         */
/* ------------------------------------------------------------------ */

/** Logo cliquable qui ouvre la fiche du partenaire (fenêtre #fiche du pied de page). */
function dt_logo_btn( $p, $categorie, $lazy = true ) {
	if ( ! $p['logo'] ) {
		return '<span style="font:700 1.25rem var(--titre);color:var(--encre);letter-spacing:-.01em">' . esc_html( $p['nom'] ) . '</span>';
	}
	return '<button type="button" class="logo-btn" data-fiche data-nom="' . esc_attr( $p['nom'] ) . '" data-cat="' . esc_attr( $categorie ) . '" data-desc="' . esc_attr( $p['description'] ) . '" data-logo="' . esc_url( $p['logo'] ) . '"'
		. ( $p['lien'] ? ' data-lien="' . esc_url( $p['lien'] ) . '"' : '' ) . ' aria-label="' . esc_attr( $p['nom'] . ' : voir la présentation' ) . '">'
		. '<img src="' . esc_url( $p['logo'] ) . '" alt="' . esc_attr( 'Logo ' . $p['nom'] ) . '" width="200" height="64"' . ( $lazy ? ' loading="lazy"' : '' ) . '></button>';
}

function dt_items_logos( $liste, $categorie, $lazy = true ) {
	return implode( '', array_map( function ( $p ) use ( $categorie, $lazy ) {
		return '<li>' . dt_logo_btn( $p, $categorie, $lazy ) . '</li>';
	}, $liste ) );
}

add_shortcode( 'dualtech_partenaires', function () {
	$sortie = '';
	foreach ( array( 'institutions', 'financement', 'ecosystemes', 'medias' ) as $rubrique ) {
		$liste = dt_partenaires( $rubrique );
		if ( $liste ) {
			$nom     = dt_nom_rubrique( $rubrique );
			$sortie .= '<div class="logos-groupe" data-reveal><h3>' . esc_html( $nom ) . '</h3><ul class="logos">' . dt_items_logos( $liste, $nom ) . '</ul></div>';
		}
	}
	return $sortie;
} );

add_shortcode( 'dualtech_logos', function ( $atts ) {
	$atts = shortcode_atts( array( 'rubrique' => 'prix', 'libelle' => 'Partenaire de DualTech 2026' ), $atts );
	return dt_items_logos( dt_partenaires( $atts['rubrique'] ), $atts['libelle'] );
} );

add_shortcode( 'dualtech_logos_defile', function () {
	$vus   = array();
	$liste = array();
	foreach ( dt_partenaires( array( 'institutions', 'financement', 'ecosystemes', 'medias', 'prix' ) ) as $p ) {
		if ( $p['logo'] && ! isset( $vus[ $p['nom'] ] ) ) {
			$vus[ $p['nom'] ] = true;
			$liste[]          = $p;
		}
	}
	$moitie = intdiv( count( $liste ), 2 );
	$rang1  = dt_items_logos( array_slice( $liste, 0, $moitie ), 'Partenaire de DualTech 2026', false );
	$rang2  = dt_items_logos( array_slice( $liste, $moitie ), 'Partenaire de DualTech 2026', false );
	return '<ul class="logos-defile__rang">' . $rang1 . $rang1 . '</ul><ul class="logos-defile__rang logos-defile__rang--inverse">' . $rang2 . $rang2 . '</ul>';
} );

add_shortcode( 'dualtech_partenaires_desc', function ( $atts ) {
	$atts = shortcode_atts( array( 'rubrique' => 'edition-2025' ), $atts );
	return implode( '', array_map( function ( $p ) {
		$logo = $p['logo'] ? '<img src="' . esc_url( $p['logo'] ) . '" alt="' . esc_attr( 'Logo ' . $p['nom'] ) . '" width="200" height="64" loading="lazy">' : '';
		return '<article class="partenaire-desc projecteur" data-reveal>' . $logo . '<h3>' . dt_e( $p['nom'] ) . '</h3><p>' . dt_e( $p['description'] ) . '</p></article>';
	}, dt_partenaires( $atts['rubrique'] ) ) );
} );

/* Repli si l'extension n'est pas active : les formulaires gardent leurs champs cachés (envoi impossible, signalé). */
add_action( 'init', function () {
	if ( ! shortcode_exists( 'dualtech_formulaire' ) ) {
		add_shortcode( 'dualtech_formulaire', function () {
			return current_user_can( 'manage_options' ) ? '<p class="envoi-maquette envoi-erreur">Administrateur : activez l’extension DualTech Studio pour recevoir les demandes de ce formulaire.</p>' : '';
		} );
	}
}, 20 );
