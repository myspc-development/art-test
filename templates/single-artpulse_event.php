<?php
/**
 * Single template for ArtPulse Events.
 */

get_header();
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('✅ Template rendering started.');
}

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
    echo '<h1 class="entry-title event-title">' . esc_html( get_the_title() ) . '</h1>';
    $rsvps      = get_post_meta(get_the_ID(), 'event_rsvp_list', true);
    $rsvp_count = is_array($rsvps) ? count($rsvps) : 0;
    $fav_count  = intval(get_post_meta(get_the_ID(), 'ap_favorite_count', true));
    echo '<div class="ap-event-actions">';
    echo \ArtPulse\Frontend\ap_render_favorite_button(get_the_ID(), 'artpulse_event');
    echo '<span class="ap-fav-count" aria-label="' . esc_attr__('Interested count','artpulse') . '">' . esc_html($fav_count) . '</span>';
    echo \ArtPulse\Frontend\ap_render_rsvp_button(get_the_ID());
    echo '<span class="ap-rsvp-count" aria-label="' . esc_attr__('RSVP count','artpulse') . '">' . esc_html($rsvp_count) . '</span>';
    echo '<button class="ap-event-vote" data-event-id="' . esc_attr( get_the_ID() ) . '">⭐ ' . esc_html__('Mark as Memorable','artpulse') . '</button> <span class="ap-event-vote-count"></span>';
    echo '</div>';

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

    $v_enabled = get_post_meta(get_the_ID(), '_ap_virtual_access_enabled', true);
    $v_url     = get_post_meta(get_the_ID(), '_ap_virtual_event_url', true);
    if ($v_enabled && $v_url) {
      $has_access = false;
      if (is_user_logged_in()) {
        $has_access = \ArtPulse\Monetization\TicketManager::user_has_ticket(get_current_user_id(), get_the_ID());
      }
      echo '<div class="virtual-event-section">';
      if ($has_access) {
        $embed = wp_oembed_get($v_url);
        if ($embed) {
          echo wp_kses_post( $embed );
        } else {
          echo '<a href="' . esc_url($v_url) . '" target="_blank">' . esc_html__('Join Event', 'artpulse') . '</a>';
        }
      } else {
        echo '<p>' . esc_html__('Purchase a ticket to access the virtual event.', 'artpulse') . '</p>';
      }
      echo '</div>';
    }

    // Event content
    echo '<div class="entry-content">';
    the_content();
    echo '</div>';
    echo \ArtPulse\Frontend\ap_event_calendar_links(get_the_ID());
    $owner = get_post_field('post_author', get_the_ID());
    $donate = \ArtPulse\Frontend\ap_render_donate_button($owner);
    if (!$donate) {
        $org = get_post_meta(get_the_ID(), '_ap_event_organization', true);
        if ($org) {
            $org_owner = get_post_field('post_author', $org);
            $donate = \ArtPulse\Frontend\ap_render_donate_button($org_owner);
        }
    }
    if ($donate) {
        echo $donate;
    }
    ?>
    <form class="ap-newsletter-optin">
      <input type="email" placeholder="<?php esc_attr_e('Your email','artpulse'); ?>" required>
      <button type="submit"><?php esc_html_e('Subscribe','artpulse'); ?></button>
      <span class="ap-optin-message"></span>
    </form>
    <?php comments_template('/partials/event-comments.php'); ?>
    <?php

    echo '</div>'; // close .container
  endwhile;
else :
  echo '<p>No event found.</p>';
endif;

get_footer();
?>
