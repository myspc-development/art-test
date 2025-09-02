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
          $email     = sanitize_email( get_post_meta(get_the_ID(), '_ap_event_organizer_email', true) );
          $city      = get_post_meta(get_the_ID(), '_ap_event_city', true);
          $state     = get_post_meta(get_the_ID(), '_ap_event_state', true);
          $country   = get_post_meta(get_the_ID(), '_ap_event_country', true);
        ?>

        <?php if ($date || $venue || $address || $start || $end || $contact || $rsvp || $organizer || $email || $city || $state || $country) : ?>
          <ul class="event-meta styled-box">
            <?php if ($date): ?>
              <li><strong><?php esc_html_e('Date:', 'artpulse'); ?></strong> <?= esc_html($date); ?></li>
            <?php endif; ?>
            <?php if ($venue): ?>
              <li><strong><?php esc_html_e('Venue:', 'artpulse'); ?></strong> <?= esc_html($venue); ?></li>
            <?php endif; ?>
            <?php if ($address): ?>
              <li><strong><?php esc_html_e('Address:', 'artpulse'); ?></strong> <?= esc_html($address); ?></li>
            <?php endif; ?>
            <?php if ($start || $end): ?>
              <li><strong><?php esc_html_e('Time:', 'artpulse'); ?></strong>
                <?= esc_html($start); ?>
                <?= ($start && $end) ? ' – ' : ''; ?>
                <?= esc_html($end); ?>
              </li>
            <?php endif; ?>
            <?php if ($organizer): ?>
              <li><strong><?php esc_html_e('Organizer:', 'artpulse'); ?></strong> <?= esc_html($organizer); ?></li>
            <?php endif; ?>
            <?php if ($email): ?>
              <li><strong><?php esc_html_e('Email:', 'artpulse'); ?></strong> <?= str_replace( '&#064;', '&#64;', esc_html( antispambot( $email ) ) ); ?></li>
            <?php endif; ?>
            <?php if ($contact): ?>
              <li><strong><?php esc_html_e('Contact:', 'artpulse'); ?></strong> <?= esc_html($contact); ?></li>
            <?php endif; ?>
            <?php if ($rsvp): ?>
              <li><strong><?php esc_html_e('RSVP:', 'artpulse'); ?></strong> <a href="<?= esc_url($rsvp); ?>" target="_blank"><?php esc_html_e('Reserve Now', 'artpulse'); ?></a></li>
            <?php endif; ?>
            <?php if ($city || $state || $country): ?>
              <li><strong><?php esc_html_e('Location:', 'artpulse'); ?></strong>
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
