<?php
/**
 * Single template for portfolio posts, using Salient portfolio wrappers.
 *
 * Place this file in:
 *   wp-content/plugins/artpulse-management-plugin/templates/salient/content-portfolio.php
 */

get_header(); ?>

<div id="nectar-outer">
	<div class="container-wrap">
	<div class="container">
		<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<?php
			while ( have_posts() ) :
				the_post();

				// Featured image
				if ( has_post_thumbnail() ) {
					echo '<div class="nectar-portfolio-single-media">';
					the_post_thumbnail( 'full', array( 'class' => 'img-responsive' ) );
					echo '</div>';
				}

				// Additional gallery images
				$gallery_ids = get_post_meta( get_the_ID(), '_ap_submission_images', true );
				if ( is_array( $gallery_ids ) && ! empty( $gallery_ids ) ) {
					echo '<div class="event-gallery swiper">';
					echo '<div class="swiper-wrapper">';
					foreach ( $gallery_ids as $img_id ) {
						echo '<div class="swiper-slide">' . wp_kses_post( wp_get_attachment_image( $img_id, 'large', false, array( 'loading' => 'lazy' ) ) ) . '</div>';
					}
					echo '</div><div class="swiper-pagination"></div><div class="swiper-button-prev"></div><div class="swiper-button-next"></div></div>';
				}

				// Title
				echo '<h1 class="entry-title">' . esc_html( get_the_title() ) . '</h1>';

				// Content
				echo '<div class="entry-content">';
				the_content();
				echo '</div>';

				// Event meta
				$date     = get_post_meta( get_the_ID(), '_ap_event_date', true );
				$location = get_post_meta( get_the_ID(), '_ap_event_location', true );

				if ( $date || $location ) {
					echo '<ul class="portfolio-meta">';
					if ( $date ) {
						echo '<li><strong>' . esc_html__( 'Date:', 'artpulse' ) . '</strong> ' . esc_html( $date ) . '</li>';
					}
					if ( $location ) {
						echo '<li><strong>' . esc_html__( 'Location:', 'artpulse' ) . '</strong> ' . esc_html( $location ) . '</li>';
					}
					echo '</ul>';
				}

			endwhile;
			?>
		</div> <!-- .col-md-8 -->
		</div> <!-- .row -->
	</div> <!-- .container -->
	</div> <!-- .container-wrap -->
</div> <!-- #nectar-outer -->

<?php
get_footer();
