<?php
/**
 * Template for displaying single ArtPulse Event content
 * Fetches event details from post meta.
 */
error_log('📦 content-artpulse_event.php loaded');
?>

<div class="container-wrap">
  <div class="container">
    <div class="row">
      <div class="col span_12">

        <?php if (has_post_thumbnail()) : ?>
          <div class="event-featured-image nectar-portfolio-single-media">
            <?php the_post_thumbnail('large', ['class' => 'img-responsive', 'alt' => esc_attr( get_the_title() )]); ?>
          </div>
        <?php endif; ?>

        <h1 class="event-title"><?php the_title(); ?></h1>

        <?php
          // Fetch core event meta
          $date     = get_post_meta(get_the_ID(), '_ap_event_date', true);
          $venue    = get_post_meta(get_the_ID(), '_ap_event_venue', true);
          $address  = get_post_meta(get_the_ID(), '_ap_event_address', true);
          $start    = get_post_meta(get_the_ID(), '_ap_event_start_time', true);
          $end      = get_post_meta(get_the_ID(), '_ap_event_end_time', true);
          $contact  = get_post_meta(get_the_ID(), '_ap_event_contact', true);
          $rsvp     = get_post_meta(get_the_ID(), '_ap_event_rsvp', true);

          // Optional: organizer and location details
          $organizer = get_post_meta(get_the_ID(), '_ap_event_organizer_name', true);
          $email     = get_post_meta(get_the_ID(), '_ap_event_organizer_email', true);
          $city      = get_post_meta(get_the_ID(), '_ap_event_city', true);
          $state     = get_post_meta(get_the_ID(), '_ap_event_state', true);
          $country   = get_post_meta(get_the_ID(), '_ap_event_country', true);
        ?>

        <?php if ($date || $venue || $address || $start || $end || $contact || $rsvp || $organizer || $email || $city || $state || $country) : ?>
          <ul class="event-meta styled-box">
            <?php if ($date): ?>
              <li><strong>Date:</strong> <?= esc_html($date); ?></li>
            <?php endif; ?>
            <?php if ($venue): ?>
              <li><strong>Venue:</strong> <?= esc_html($venue); ?></li>
            <?php endif; ?>
            <?php if ($address): ?>
              <li><strong>Address:</strong> <?= esc_html($address); ?></li>
            <?php endif; ?>
            <?php if ($start || $end): ?>
              <li><strong>Time:</strong>
                <?= esc_html($start); ?>
                <?= ($start && $end) ? ' – ' : ''; ?>
                <?= esc_html($end); ?>
              </li>
            <?php endif; ?>
            <?php if ($organizer): ?>
              <li><strong>Organizer:</strong> <?= esc_html($organizer); ?></li>
            <?php endif; ?>
            <?php if ($email): ?>
              <li><strong>Email:</strong> <?= esc_html($email); ?></li>
            <?php endif; ?>
            <?php if ($contact): ?>
              <li><strong>Contact:</strong> <?= esc_html($contact); ?></li>
            <?php endif; ?>
            <?php if ($rsvp): ?>
              <li><strong>RSVP:</strong> <a href="<?= esc_url($rsvp); ?>" target="_blank">Reserve Now</a></li>
            <?php endif; ?>
            <?php if ($city || $state || $country): ?>
              <li><strong>Location:</strong>
                <?= esc_html(trim("{$city}, {$state}, {$country}", ", ")); ?>
              </li>
            <?php endif; ?>
          </ul>
        <?php endif; ?>

        <div class="event-description">
          <?php
          if (trim(get_the_content())) {
            the_content();
          } else {
            echo '<p>No description provided.</p>';
          }
          ?>
        </div>

        <?php echo \ArtPulse\Frontend\ap_share_buttons( get_permalink(), get_the_title(), get_post_type(), get_the_ID() ); ?>

      </div>
    </div>
  </div>
</div>
