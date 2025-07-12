<?php
/**
 * Single template for Artist posts with gallery carousel.
 */
get_header();

if ( have_posts() ) :
  while ( have_posts() ) : the_post();
    echo '<div class="container single-artist-content">';

    if ( has_post_thumbnail() ) {
      echo '<div class="artist-featured-image nectar-portfolio-single-media">';
      the_post_thumbnail('large', ['class' => 'img-responsive']);
      echo '</div>';
    }

    $gallery_ids = get_post_meta( get_the_ID(), '_ap_submission_images', true );
    if ( is_array( $gallery_ids ) && count( $gallery_ids ) > 1 ) {
      echo '<div class="event-gallery swiper">';
      echo '<div class="swiper-wrapper">';
      foreach ( array_slice( $gallery_ids, 1 ) as $img_id ) {
        echo '<div class="swiper-slide">' . wp_get_attachment_image( $img_id, 'large' ) . '</div>';
      }
      echo '</div><div class="swiper-pagination"></div><div class="swiper-button-prev"></div><div class="swiper-button-next"></div></div>';
    } else {
      echo '<p class="no-gallery">' . esc_html__( 'No gallery images available.', 'artpulse' ) . '</p>';
    }

    echo '<h1 class="entry-title artist-title">' . get_the_title() . '</h1>';
    echo '<div class="entry-content">';
    the_content();
    echo '</div>';

    echo '</div>';
  endwhile;
else :
  echo '<p>' . esc_html__( 'No artist found.', 'artpulse' ) . '</p>';
endif;

get_footer();
