<?php
/**
 * Pied de page : présentation, coordonnées, liens, mentions.
 * Tout vient des « Réglages de la maison » et du menu principal.
 */
defined( 'ABSPATH' ) || exit;

$tel     = schiesser_reglage( 'telephone' );
$email   = schiesser_reglage( 'email' );
$ig      = schiesser_reglage( 'instagram' );
$fb      = schiesser_reglage( 'facebook' );
$rue     = schiesser_reglage( 'rue' );
$ville   = schiesser_reglage( 'ville' );
$annee   = schiesser_reglage( 'annee_fondation' );
$mention = get_page_by_path( 'mentions-legales' ) ?: get_page_by_path( 'impressum' );
// Liens vers les sections de la page (celles qui ont une ancre), comme dans la maquette.
$ancres  = ( is_page() && function_exists( 'schiesser_ancres_page' ) ) ? array_slice( schiesser_ancres_page(), 0, 5, true ) : array();
$ancres  = count( $ancres ) >= 2 ? $ancres : array();
?>
</main>

<footer class="site-footer">
	<div class="wrap">
		<div class="fgrid<?php echo $ancres ? ' fgrid--4' : ''; ?>">
			<div class="fbrand">
				<span class="brand"><?php schiesser_logo( implode( ' · ', array_filter( array( $rue, $ville, $annee ? 'Seit ' . $annee : '' ) ) ) ); ?></span>
				<p><?php echo esc_html( schiesser_reglage( 'presentation' ) ); ?></p>
				<div class="x-social">
					<?php if ( $ig ) : ?>
						<a href="<?php echo esc_url( $ig ); ?>" aria-label="Instagram" rel="noopener" target="_blank"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".8" fill="currentColor" stroke="none"/></svg></a>
					<?php endif; ?>
					<?php if ( $fb ) : ?>
						<a href="<?php echo esc_url( $fb ); ?>" aria-label="Facebook" rel="noopener" target="_blank"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 8.5V7a1.5 1.5 0 011.5-1.5H17V3h-2.2A4 4 0 0011 7v1.5H9V11h2v9h3v-9h2.2l.4-2.5H14z"/></svg></a>
					<?php endif; ?>
					<?php if ( $email ) : ?>
						<a href="<?php echo esc_url( 'mailto:' . $email ); ?>" aria-label="E-mail"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3.5 6.5l8.5 6 8.5-6"/></svg></a>
					<?php endif; ?>
				</div>
			</div>
			<div class="fcol">
				<h2 class="fcol-titre">Nous trouver</h2>
				<span class="fcol-txt"><?php echo esc_html( implode( ', ', array_filter( schiesser_adresse_lignes() ) ) ); ?></span>
				<span class="fcol-txt"><?php echo esc_html( schiesser_horaires_resume() ); ?></span>
				<?php if ( $email ) : ?><a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a><?php endif; ?>
				<?php if ( $tel ) : ?><a href="<?php echo esc_url( schiesser_lien_tel() ); ?>"><?php echo esc_html( $tel ); ?></a><?php endif; ?>
			</div>
			<?php if ( $ancres ) : ?>
				<nav class="fcol" aria-label="Sur cette page">
					<h2 class="fcol-titre">Sur cette page</h2>
					<?php foreach ( $ancres as $ancre => $titre ) : ?>
						<a href="#<?php echo esc_attr( $ancre ); ?>"><?php echo esc_html( $titre ); ?></a>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>
			<div class="fcol">
				<h2 class="fcol-titre">Explorer</h2>
				<?php foreach ( schiesser_liens_menu( 'pied' ) as $lien ) : ?>
					<a href="<?php echo esc_url( $lien['url'] ); ?>"><?php echo esc_html( $lien['titre'] ); ?></a>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="fbottom">
			<span>© <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
			<span class="x-legal">
				<?php if ( $mention && 'publish' === $mention->post_status ) : ?><a href="<?php echo esc_url( get_permalink( $mention ) ); ?>"><?php echo esc_html( get_the_title( $mention ) ); ?></a><?php endif; ?>
				<?php if ( get_privacy_policy_url() ) : ?><a href="<?php echo esc_url( get_privacy_policy_url() ); ?>">Confidentialité</a><?php endif; ?>
			</span>
			<span><?php echo esc_html( implode( ' · ', array_filter( array( $rue, $ville ) ) ) ); ?></span>
		</div>
	</div>
</footer>
<div class="x-cursor" aria-hidden="true"></div>

</div><!-- .pg -->
<?php wp_footer(); ?>
</body>
</html>
