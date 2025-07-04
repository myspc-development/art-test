<?php
/**
 * Default organization single template with gallery slider.
 */
get_header();
while ( have_posts() ) : the_post();
  if ( has_post_thumbnail() ) {
    echo '<div class="nectar-portfolio-single-media">';
    the_post_thumbnail('full', ['class' => 'img-responsive']);
    echo '</div>';
  }

  $gallery_ids = get_post_meta(get_the_ID(), '_ap_submission_images', true);
  if ( is_array($gallery_ids) && count($gallery_ids) > 0 ) {
    echo '<div class="event-gallery swiper">';
    echo '<div class="swiper-wrapper">';
    foreach ( $gallery_ids as $img_id ) {
      echo '<div class="swiper-slide">' . wp_get_attachment_image($img_id, 'large') . '</div>';
    }
    echo '</div><div class="swiper-pagination"></div><div class="swiper-button-prev"></div><div class="swiper-button-next"></div></div>';
  }

  echo '<h1 class="entry-title">' . get_the_title() . '</h1>';
  echo '<div class="entry-content">';
  the_content();
  echo '</div>';

  $address = get_post_meta(get_the_ID(), 'ead_org_street_address', true);
  $website = get_post_meta(get_the_ID(), 'ead_org_website_url', true);
  if ( $address || $website ) {
    echo '<ul class="portfolio-meta">';
    if ( $address ) {
      echo '<li><strong>' . esc_html__('Address:', 'artpulse') . '</strong> ' . esc_html($address) . '</li>';
    }
    if ( $website ) {
      echo '<li><strong>' . esc_html__('Website:', 'artpulse') . '</strong> <a href="' . esc_url($website) . '" target="_blank">' . esc_html($website) . '</a></li>';
    }
    echo '</ul>';
  }

  $days  = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
  $hours = [];
  foreach ( $days as $day ) {
    $start  = get_post_meta(get_the_ID(), "ead_org_{$day}_start_time", true);
    $end    = get_post_meta(get_the_ID(), "ead_org_{$day}_end_time", true);
    $closed = get_post_meta(get_the_ID(), "ead_org_{$day}_closed", true);
    if ( $start || $end || $closed ) {
      $hours[$day] = [
        'start'  => $start,
        'end'    => $end,
        'closed' => $closed,
      ];
    }
  }
  if ( ! empty($hours) ) {
    echo '<h2>' . esc_html__('Opening Hours', 'artpulse') . '</h2>';
    echo '<ul class="portfolio-meta opening-hours">';
    foreach ( $hours as $day => $vals ) {
      echo '<li><strong>' . esc_html(ucfirst($day) . ':') . '</strong> ' . ($vals['closed'] ? esc_html__('Closed', 'artpulse') : esc_html(trim($vals['start'] . ' - ' . $vals['end']))) . '</li>';
    }
    echo '</ul>';
  }
endwhile;
get_footer();
