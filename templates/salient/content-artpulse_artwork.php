<?php
get_header();
while ( have_posts() ) :
	the_post(); ?>
	<div class="nectar-portfolio-single-media">
	<?php the_post_thumbnail( 'full', array( 'class' => 'img-responsive' ) ); ?>
	</div>
	<?php
	$gallery_ids = get_post_meta( get_the_ID(), '_ap_submission_images', true );
	if ( is_array( $gallery_ids ) && count( $gallery_ids ) > 1 ) :
		echo '<div class="artwork-gallery">';
		foreach ( array_slice( $gallery_ids, 1 ) as $img_id ) {
			echo wp_kses_post( wp_get_attachment_image( $img_id, 'large', false, array( 'loading' => 'lazy' ) ) );
		}
		echo '</div>';
	endif;
	?>
	<h1 class="entry-title"><?php the_title(); ?></h1>
	<?php echo \ArtPulse\Frontend\ap_render_favorite_button( get_the_ID(), 'artpulse_artwork' ); ?>
	<div class="entry-content"><?php the_content(); ?></div>
	<?php
	$medium_meta  = get_post_meta( get_the_ID(), '_ap_artwork_medium', true );
	$dimensions   = get_post_meta( get_the_ID(), '_ap_artwork_dimensions', true );
	$materials    = get_post_meta( get_the_ID(), '_ap_artwork_materials', true );
	$medium_terms = get_the_terms( get_the_ID(), 'artpulse_medium' );
	$medium_names = $medium_terms && ! is_wp_error( $medium_terms )
		? implode( ', ', wp_list_pluck( $medium_terms, 'name' ) )
		: '';
	$medium       = $medium_names ?: $medium_meta;
	$style_terms  = get_the_terms( get_the_ID(), 'artwork_style' );
	$style_meta   = get_post_meta( get_the_ID(), 'artwork_styles', true );
	$style        = ( $style_terms && ! is_wp_error( $style_terms ) )
		? implode( ', ', wp_list_pluck( $style_terms, 'name' ) )
		: $style_meta;
	$for_sale     = get_post_meta( get_the_ID(), 'for_sale', true );
	$price        = get_post_meta( get_the_ID(), 'price', true );
	$buy_link     = get_post_meta( get_the_ID(), 'buy_link', true );
	$sale_enabled = get_option( 'ap_enable_artworks_for_sale' );
	if ( $medium || $style || $dimensions || $materials || ( $sale_enabled && $for_sale && ( $price || $buy_link ) ) ) :
		?>
	<ul class="portfolio-meta">
		<?php if ( $medium ) : ?>
		<li><strong><?php esc_html_e( 'Medium:', 'artpulse' ); ?></strong> <?php echo esc_html( $medium ); ?></li>
		<?php endif; ?>
		<?php if ( $style ) : ?>
		<li><strong><?php esc_html_e( 'Style:', 'artpulse' ); ?></strong> <?php echo esc_html( $style ); ?></li>
		<?php endif; ?>
		<?php if ( $dimensions ) : ?>
		<li><strong><?php esc_html_e( 'Dimensions:', 'artpulse' ); ?></strong> <?php echo esc_html( $dimensions ); ?></li>
		<?php endif; ?>
		<?php if ( $materials ) : ?>
		<li><strong><?php esc_html_e( 'Materials:', 'artpulse' ); ?></strong> <?php echo esc_html( $materials ); ?></li>
		<?php endif; ?>
		<?php if ( $sale_enabled && $for_sale && $price ) : ?>
		<li><strong><?php esc_html_e( 'Price:', 'artpulse' ); ?></strong> <?php echo esc_html( $price ); ?></li>
		<?php endif; ?>
		<?php if ( $sale_enabled && $for_sale && $buy_link ) : ?>
		<li><a class="ap-buy-link" href="<?php echo esc_url( $buy_link ); ?>" target="_blank"><?php esc_html_e( 'Buy Now', 'artpulse' ); ?></a></li>
		<?php endif; ?>
	</ul>
	<?php endif; ?>
	<?php echo \ArtPulse\Frontend\ap_share_buttons( get_permalink(), get_the_title(), get_post_type(), get_the_ID() ); ?>
<?php endwhile; ?>
get_footer();
