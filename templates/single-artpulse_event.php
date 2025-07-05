<?php
/**
 * Single template for ArtPulse Events.
 */

get_header();
error_log('✅ Template rendering started.');

if ( have_posts() ) :
  while ( have_posts() ) : the_post();
    echo '<div class="container single-event-content">';

    // Featured image
    if ( has_post_thumbnail() ) {
      echo '<div class="event-featured-image nectar-portfolio-single-media">';
      the_post_thumbnail('large', ['class' => 'img-responsive']);
      echo '</div>';
    }

    // Event title
    echo '<h1 class="entry-title event-title">' . get_the_title() . '</h1>';

    // Event meta
    $date     = get_post_meta(get_the_ID(), '_ap_event_date', true);
    $location = get_post_meta(get_the_ID(), '_ap_event_location', true);
    $address  = get_post_meta(get_the_ID(), '_ap_event_address', true);
    $start    = get_post_meta(get_the_ID(), '_ap_event_start_time', true);
    $end      = get_post_meta(get_the_ID(), '_ap_event_end_time', true);
    $contact  = get_post_meta(get_the_ID(), '_ap_event_contact', true);
    $rsvp     = get_post_meta(get_the_ID(), '_ap_event_rsvp', true);

    echo '<div class="event-meta styled-box"><ul class="event-meta-list">';

    if ($date)     echo '<li><strong>Date:</strong> ' . esc_html($date) . '</li>';
    if ($start || $end) {
      echo '<li><strong>Time:</strong> ';
      echo esc_html($start);
      echo ($start && $end) ? ' – ' : '';
      echo esc_html($end);
      echo '</li>';
    }
    if ($location) echo '<li><strong>Venue:</strong> ' . esc_html($location) . '</li>';
    if ($address)  echo '<li><strong>Address:</strong> ' . esc_html($address) . '</li>';
    if ($contact)  echo '<li><strong>Contact:</strong> ' . esc_html($contact) . '</li>';
    if ($rsvp)     echo '<li><strong>RSVP:</strong> <a href="' . esc_url($rsvp) . '" target="_blank">Reserve Now</a></li>';

    echo '</ul></div>'; // close .event-meta

    $product = get_post_meta(get_the_ID(), '_event_ticket_product_id', true);
    if ($product && function_exists('wc_get_product')) {
      $p = wc_get_product($product);
      if ($p) {
        global $product; // WooCommerce expects $product global
        $product = $p;
        echo '<div class="ap-ticket-purchase">';
        do_action('woocommerce_before_add_to_cart_form');
        if (function_exists('woocommerce_template_single_add_to_cart')) {
          woocommerce_template_single_add_to_cart();
        } else {
          echo '<a href="' . esc_url(add_query_arg('add-to-cart', $p->get_id())) . '" class="button">' . esc_html__('Buy Ticket', 'artpulse') . '</a>';
        }
        do_action('woocommerce_after_add_to_cart_form');
        echo '</div>';
      }
    }

    // Event content
    echo '<div class="entry-content">';
    the_content();
    echo '</div>';

    echo '</div>'; // close .container
  endwhile;
else :
  echo '<p>No event found.</p>';
endif;

get_footer();
?>
