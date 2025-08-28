<?php
get_header();
if ( have_posts() ) :
  while ( have_posts() ) : the_post();

    // Meta fields
    $venue   = get_post_meta(get_the_ID(), '_ap_event_venue', true);
    $address = get_post_meta(get_the_ID(), '_ap_event_address', true);
    $start   = get_post_meta(get_the_ID(), '_ap_event_start_time', true);
    $end     = get_post_meta(get_the_ID(), '_ap_event_end_time', true);
    $contact = get_post_meta(get_the_ID(), '_ap_event_contact', true);
    $rsvp    = get_post_meta(get_the_ID(), '_ap_event_rsvp', true);
    $date    = get_post_meta(get_the_ID(), '_ap_event_date', true);

    // Output
    echo '<div class="container single-artpulse-event">';
    
    if ( has_post_thumbnail() ) {
      echo '<div class="event-image">';
      the_post_thumbnail('large', ['class' => 'img-responsive']);
      echo '</div>';
    }

    echo '<h1 class="event-title">' . esc_html( get_the_title() ) . '</h1>';

    echo '<ul class="event-meta">';
    if ($date)    echo '<li><strong>Date:</strong> ' . esc_html($date) . '</li>';
    if ($venue)   echo '<li><strong>Venue:</strong> ' . esc_html($venue) . '</li>';
    if ($address) echo '<li><strong>Address:</strong> ' . esc_html($address) . '</li>';
    if ($start || $end) {
      echo '<li><strong>Time:</strong> ';
      echo esc_html($start);
      echo ($start && $end) ? ' – ' : '';
      echo esc_html($end);
      echo '</li>';
    }
    if ($contact) echo '<li><strong>Contact:</strong> ' . esc_html($contact) . '</li>';
    if ($rsvp)    echo '<li><strong>RSVP:</strong> <a href="' . esc_url($rsvp) . '" target="_blank">Reserve</a></li>';
    echo '</ul>';

    echo '<div class="event-content">';
    the_content();
    echo '</div>';

    echo '</div>'; // .container

  endwhile;
endif;
get_footer();
?>
