<?php
/**
 * Carte du Tea Room en PDF.
 *
 * - schiesser_url_carte_pdf() : le PDF ouvert par le bouton « Karte als PDF » (celui des Réglages maison,
 *   sinon celui fourni avec le thème, sinon la carte imprimable).
 * - « Carte imprimable » (/?schiesser_karte=druck) : la carte des « Produits Tea Room » mise en page
 *   au format A4, toujours à jour. Imprimer → Enregistrer au format PDF crée un nouveau PDF.
 *   Le PDF livré avec le thème (assets/pdf/) a été produit à partir de cette page.
 */

defined( 'ABSPATH' ) || exit;

const SCHIESSER_CARTE_PDF = 'assets/pdf/karte-tea-room-schiesser.pdf';

/** Fichier PDF fourni avec le thème, par langue. */
function schiesser_fichier_carte_pdf( $langue ) {
	$fichiers = array(
		'de' => SCHIESSER_CARTE_PDF,
		'fr' => 'assets/pdf/carte-tea-room-schiesser.pdf',
		'en' => 'assets/pdf/menu-tea-room-schiesser.pdf',
	);
	return $fichiers[ $langue ] ?? SCHIESSER_CARTE_PDF;
}

/** Adresse du PDF de la carte. */
function schiesser_url_carte_pdf() {
	$langue  = function_exists( 'schiesser_langue' ) ? schiesser_langue() : 'de';
	$reglage = trim( (string) schiesser_reglage( 'de' === $langue ? 'carte_pdf' : 'carte_pdf_' . $langue ) );
	if ( '' !== $reglage ) {
		return $reglage;
	}
	$fichier = schiesser_fichier_carte_pdf( $langue );
	if ( file_exists( SCHIESSER_DIR . '/' . $fichier ) ) {
		return SCHIESSER_URI . '/' . $fichier . '?v=' . SCHIESSER_VERSION;
	}
	return add_query_arg( 'schiesser_karte', 'druck', schiesser_url_accueil() );
}

/* La carte imprimable n'est pas une page à indexer. */
add_action( 'template_redirect', function () {
	if ( 'druck' !== ( $_GET['schiesser_karte'] ?? '' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}
	header( 'X-Robots-Tag: noindex, follow', true );
	nocache_headers();
	echo schiesser_carte_druck_html(); // phpcs:ignore WordPress.Security.EscapeOutput
	exit;
}, 2 );

/** Page A4 de la carte (HTML complet). */
function schiesser_carte_druck_html() {
	$carte = function_exists( 'schiesser_carte_tearoom' ) ? schiesser_carte_tearoom() : array( 'rubriques' => array() );
	$nom   = schiesser_reglage( 'nom_etablissement' ) ?: get_bloginfo( 'name' );
	$adr   = implode( ' · ', array_filter( array( schiesser_reglage( 'rue' ), trim( schiesser_reglage( 'code_postal' ) . ' ' . schiesser_reglage( 'ville' ) ) ) ) );
	$tel   = schiesser_reglage( 'telephone' );
	$web   = apply_filters( 'schiesser_carte_druck_web', preg_replace( '#^https?://(www\.)?#', '', untrailingslashit( home_url() ) ) );
	$f     = SCHIESSER_URI . '/assets/fonts/';
	$sceau = '<svg class="sceau" viewBox="0 0 200 200" aria-hidden="true"><defs><path id="r" d="M100,100 m-80,0 a80,80 0 1,1 160,0 a80,80 0 1,1 -160,0"/></defs>'
		. '<text font-size="10.5" font-weight="600" letter-spacing="3" fill="currentColor"><textPath href="#r" textLength="496" lengthAdjust="spacing">' . esc_html( schiesser_t( 'CONFISERIE · TEA-ROOM · MARKTPLATZ BASEL · SEIT 1870 ·' ) ) . '</textPath></text>'
		. '<circle cx="100" cy="100" r="64" fill="none" stroke="currentColor" stroke-opacity=".6"/><circle cx="100" cy="100" r="58" fill="none" stroke="currentColor" stroke-opacity=".45" stroke-dasharray="1 4"/>'
		. '<text x="100" y="121" text-anchor="middle" font-size="58" font-weight="500" font-family="Bodoni Moda" fill="currentColor">S</text></svg>';
	$orn   = '<span class="orn" aria-hidden="true"><i></i><b>◆</b><i></i></span>';

	// Une page A4 par rubrique ; deux rubriques courtes partagent une page.
	$pages  = array();
	$charge = 99;
	foreach ( $carte['rubriques'] as $i => $r ) {
		$plats = '';
		$poids = 3;
		foreach ( $r['plats'] as $p ) {
			$desc   = trim( wp_strip_all_tags( (string) $p['description'] ) );
			$poids += 1 + ceil( mb_strlen( $desc ) / 85 ) * 0.62;
			$plats .= '<div class="plat"><div class="l"><span class="n">' . esc_html( wp_strip_all_tags( $p['nom'] ) ) . '</span>'
				. ( '' !== trim( (string) $p['mention'] ) ? '<span class="m">' . esc_html( $p['mention'] ) . '</span>' : '' )
				. '<span class="d"></span><span class="p">' . esc_html( str_replace( 'CHF ', '', (string) $p['prix'] ) ) . '</span></div>'
				. ( '' !== $desc ? '<p>' . esc_html( $desc ) . '</p>' : '' ) . '</div>';
		}
		$html = '<section class="rub"><header><span class="no">' . sprintf( '%02d', $i + 1 ) . '</span>'
			. '<h2>' . esc_html( $r['nom'] ) . '</h2>' . $orn . '</header><div class="plats">' . $plats . '</div></section>';
		if ( $charge + $poids > 30 ) {
			$pages[] = '';
			$charge  = 0;
		}
		$pages[ count( $pages ) - 1 ] .= $html;
		$charge += $poids;
	}
	$pied = '<div class="pied">' . esc_html( trim( $nom . ' · ' . $adr . ( $tel ? ' · ' . $tel : '' ), ' ·' ) ) . '<span>' . esc_html( schiesser_t( 'Alle Preise in CHF inkl. MwSt. · Auskunft zu Allergenen erhalten Sie gerne bei unserem Team.' ) ) . '</span></div>';

	ob_start();
	?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr( schiesser_langues_site()[ schiesser_langue() ][2] ); ?>">
<head>
<meta charset="utf-8">
<meta name="robots" content="noindex, follow">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo esc_html( schiesser_t( 'Karte Tea Room' ) ); ?> · <?php echo esc_html( $nom ); ?></title>
<style>
@font-face{font-family:'Bodoni Moda';font-style:normal;font-weight:400 900;src:url(<?php echo esc_url( $f . 'bodoni-moda.woff2' ); ?>) format('woff2')}
@font-face{font-family:'Bodoni Moda';font-style:italic;font-weight:400 900;src:url(<?php echo esc_url( $f . 'bodoni-moda-italic.woff2' ); ?>) format('woff2')}
@font-face{font-family:'Inter';font-weight:100 900;src:url(<?php echo esc_url( $f . 'inter.woff2' ); ?>) format('woff2')}
@page{size:A4;margin:0}
:root{--paper:#F4EEE3;--ink:#2A1C12;--soft:#6B5A47;--vert:#174633;--gold:#174633;--mint:#8CC5A6;--dark:#271B12;--line:rgba(58,40,24,.18)}
*{box-sizing:border-box;margin:0;padding:0}
html{background:#8a7a68}
body{font-family:'Inter',sans-serif;color:var(--ink);-webkit-print-color-adjust:exact;print-color-adjust:exact}
.page{width:210mm;height:297mm;overflow:hidden;margin:0 auto;background:var(--paper);position:relative;padding:20mm 20mm 26mm;display:flex;flex-direction:column;page-break-after:always;break-after:page}
.page:last-child{page-break-after:auto;break-after:auto}
@media screen{.page{margin:10mm auto;box-shadow:0 20px 60px rgba(0,0,0,.35)}.aide{max-width:210mm;margin:8mm auto 0;font:13px/1.5 Inter,sans-serif;color:#fff;text-align:center}.aide button{font:inherit;margin-left:8px;padding:6px 14px;border:1px solid #fff;background:transparent;color:#fff;cursor:pointer}}
@media print{.aide{display:none}html{background:none}}
.page::before{content:"";position:absolute;inset:8mm;border:.6pt solid var(--vert);pointer-events:none}
.page::after{content:"";position:absolute;inset:9.6mm;border:.3pt solid rgba(58,40,24,.35);pointer-events:none}
/* couverture */
.couv{display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;background:var(--dark);color:var(--paper)}
.couv::before{border-color:rgba(140,197,166,.6)}.couv::after{border-color:rgba(140,197,166,.3)}
.couv .sceau{width:46mm;height:46mm;color:var(--mint);margin-bottom:14mm}
.couv .k{font-size:8pt;letter-spacing:.42em;text-transform:uppercase;color:var(--mint);font-weight:600}
.couv h1{font-family:'Bodoni Moda',serif;font-weight:500;font-size:54pt;line-height:.95;letter-spacing:-.01em;margin:7mm 0 4mm}
.couv h1 em{display:block;font-style:italic;font-weight:400;font-size:30pt;margin-top:3mm;color:#E9DFCC}
.couv .sous{font-family:'Bodoni Moda',serif;font-style:italic;font-size:14pt;color:#D9CDB6}
.couv .orn{margin:10mm 0;color:var(--mint)}
.couv .bas{position:absolute;left:0;right:0;bottom:22mm;font-size:7.5pt;letter-spacing:.3em;text-transform:uppercase;color:rgba(244,238,227,.7)}
.orn{display:flex;align-items:center;justify-content:center;gap:3mm;color:var(--gold)}
.orn i{display:block;width:16mm;height:.5pt;background:currentColor;opacity:.7}
.orn b{font-size:6pt;font-weight:400}
/* rubriques */
.contenu{flex:1;display:flex;flex-direction:column;justify-content:center;gap:10mm}
.rub{break-inside:avoid}
.rub header{text-align:center;margin-bottom:5mm}
.rub .no{display:block;font-family:'Bodoni Moda',serif;font-style:italic;font-size:10pt;color:var(--soft)}
.rub h2{font-family:'Bodoni Moda',serif;font-weight:500;font-size:24pt;letter-spacing:.01em;line-height:1.1;margin:1mm 0 2.5mm}
.plats{max-width:150mm;margin:0 auto}
.plat{break-inside:avoid;padding:2.1mm 0;border-bottom:.3pt solid var(--line)}
.plat:last-child{border-bottom:0}
.plat .l{display:flex;align-items:baseline;gap:2.5mm}
.plat .n{font-family:'Bodoni Moda',serif;font-weight:500;font-size:11.5pt}
.plat .m{font-size:6pt;letter-spacing:.22em;text-transform:uppercase;font-weight:700;color:var(--vert);border:.4pt solid var(--vert);padding:.5mm 1.4mm;white-space:nowrap}
.plat .d{flex:1;border-bottom:.5pt dotted rgba(23,70,51,.45);transform:translateY(-1mm);min-width:6mm}
.plat .p{font-family:'Bodoni Moda',serif;font-size:11pt;color:var(--vert);white-space:nowrap;font-variant-numeric:tabular-nums}
.plat p{font-family:'Bodoni Moda',serif;font-style:italic;font-size:8.6pt;line-height:1.4;color:var(--soft);margin-top:.8mm;max-width:128mm}
.entete{display:flex;justify-content:space-between;align-items:baseline;font-size:6.8pt;letter-spacing:.3em;text-transform:uppercase;color:var(--soft);margin-bottom:6mm;padding-bottom:3mm;border-bottom:.4pt solid var(--line)}
.entete b{font-family:'Bodoni Moda',serif;font-size:12pt;letter-spacing:.18em;font-weight:500;color:var(--ink)}
.pied{position:absolute;left:20mm;right:20mm;bottom:13mm;text-align:center;font-size:6.6pt;letter-spacing:.14em;text-transform:uppercase;color:var(--soft)}
.pied span{display:block;margin-top:1mm;text-transform:none;letter-spacing:.04em;font-style:italic;font-family:'Bodoni Moda',serif;font-size:8pt}
</style>
</head>
<body>
<p class="aide">Carte imprimable : Imprimer → « Enregistrer au format PDF », format A4, marges « Aucune », graphiques d’arrière-plan activés. <button type="button" onclick="window.print()">Imprimer</button></p>
<div class="page couv">
	<?php echo $sceau; // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<p class="k"><?php echo esc_html( schiesser_t( 'Seit 1870 · Marktplatz Basel' ) ); ?></p>
	<h1>Schiesser<em>Tea Room &amp; Rathstübli</em></h1>
	<p class="sous"><?php echo esc_html( schiesser_t( 'Das älteste Kaffeehaus der Schweiz' ) ); ?></p>
	<?php echo $orn; // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<p class="k"><?php echo esc_html( schiesser_t( 'Die Karte' ) ); ?></p>
	<p class="bas"><?php echo esc_html( trim( $adr . ( $tel ? ' · ' . $tel : '' ) . ' · ' . $web, ' ·' ) ); ?></p>
</div>
<?php foreach ( $pages as $k => $contenu ) : ?>
<div class="page">
	<div class="entete"><span>Tea Room</span><b>Schiesser</b><span><?php echo esc_html( ( $k + 1 ) . ' / ' . count( $pages ) ); ?></span></div>
	<div class="contenu"><?php echo $contenu; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
	<?php echo $pied; // phpcs:ignore WordPress.Security.EscapeOutput ?>
</div>
<?php endforeach; ?>
<script>
/* Une rubrique un peu longue est légèrement réduite pour tenir sur sa page. */
(function(){function ajuster(){document.querySelectorAll('.page').forEach(function(p){var c=p.querySelector('.contenu');if(!c)return;var z=1;c.style.zoom=1;while(p.scrollHeight>p.clientHeight+1&&z>0.78){z-=0.02;c.style.zoom=z;}});}
if(document.fonts&&document.fonts.ready){document.fonts.ready.then(ajuster);}else{window.addEventListener('load',ajuster);}window.addEventListener('beforeprint',ajuster);})();
</script>
</body>
</html>
	<?php
	return (string) ob_get_clean();
}
