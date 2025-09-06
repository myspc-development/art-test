<?php
get_header();
while ( have_posts() ) :
	the_post(); ?>
	<div class="nectar-portfolio-single-media">
	<?php the_post_thumbnail( 'full', array( 'class' => 'img-responsive' ) ); ?>
	</div>
	<h1 class="entry-title"><?php the_title(); ?></h1>
	<?php echo \ArtPulse\Frontend\ap_render_favorite_button( get_the_ID(), 'artpulse_artist' ); ?>
	<div class="entry-content"><?php the_content(); ?></div>
	<?php
	$bio             = get_post_meta( get_the_ID(), '_ap_artist_bio', true );
	$org             = get_post_meta( get_the_ID(), '_ap_artist_org', true );
	$specialty_terms = get_the_terms( get_the_ID(), 'artist_specialty' );
	$specialties     = $specialty_terms && ! is_wp_error( $specialty_terms )
		? implode( ', ', wp_list_pluck( $specialty_terms, 'name' ) )
		: '';
	$style_terms     = get_the_terms( get_the_ID(), 'artwork_style' );
	$styles          = $style_terms && ! is_wp_error( $style_terms )
		? implode( ', ', wp_list_pluck( $style_terms, 'name' ) )
		: '';
	if ( $bio || $org || $specialties || $styles ) :
		?>
	<ul class="portfolio-meta">
		<?php if ( $bio ) : ?>
		<li><strong><?php esc_html_e( 'Biography:', 'artpulse' ); ?></strong> <?php echo wp_kses_post( $bio ); ?></li>
		<?php endif; ?>
		<?php if ( $org ) : ?>
		<li><strong><?php esc_html_e( 'Organization ID:', 'artpulse' ); ?></strong> <?php echo esc_html( $org ); ?></li>
		<?php endif; ?>
		<?php if ( $specialties ) : ?>
		<li><strong><?php esc_html_e( 'Specialties:', 'artpulse' ); ?></strong> <?php echo esc_html( $specialties ); ?></li>
		<?php endif; ?>
		<?php if ( $styles ) : ?>
		<li><strong><?php esc_html_e( 'Styles:', 'artpulse' ); ?></strong> <?php echo esc_html( $styles ); ?></li>
		<?php endif; ?>
	</ul>
	<?php endif; ?>
	<?php echo \ArtPulse\Frontend\ap_share_buttons( get_permalink(), get_the_title(), get_post_type(), get_the_ID() ); ?>
<?php endwhile; ?>
get_footer();
