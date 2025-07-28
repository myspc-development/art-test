<?php
/**
 * Template for displaying single ArtPulse Event content
 * Fetches event details from post meta.
 */

?>

<div class="container-wrap">
  <div class="container">
    <div class="row">
      <div class="col span_12">

        <?php if (has_post_thumbnail()) : ?>
          <div class="event-featured-image nectar-portfolio-single-media">
            <?php the_post_thumbnail('large', ['class' => 'img-responsive']); ?>
          </div>
        <?php endif; ?>

        <h1 class="event-title"><?php the_title(); ?></h1>

        <?php
          // Fetch post meta
          $date    = get_post_meta(get_the_ID(), '_ap_event_date', true);
          $venue   = get_post_meta(get_the_ID(), '_ap_event_venue', true);
          $address = get_post_meta(get_the_ID(), '_ap_event_address', true);
          $start   = get_post_meta(get_the_ID(), '_ap_event_start_time', true);
          $end     = get_post_meta(get_the_ID(), '_ap_event_end_time', true);
          $contact = get_post_meta(get_the_ID(), '_ap_event_contact', true);
          $rsvp    = get_post_meta(get_the_ID(), '_ap_event_rsvp', true);
        ?>

          <ul class="event-meta styled-box" itemscope itemtype="https://schema.org/Event">
            <meta itemprop="name" content="<?php the_title(); ?>">
            <meta itemprop="startDate" content="<?php echo esc_attr($date); ?>">
            <meta itemprop="location" content="<?php echo esc_attr($venue); ?>">
            <li><strong>Date:</strong> <?php echo esc_html($date ?: __('Not specified', 'artpulse')); ?></li>
            <?php $time_display = ($start || $end) ? esc_html($start) . ($start && $end ? ' – ' : '') . esc_html($end) : __('Not specified', 'artpulse'); ?>
            <li><strong>Time:</strong> <?php echo $time_display; ?></li>
            <li><strong>Venue:</strong> <?php echo esc_html($venue ?: __('Not specified', 'artpulse')); ?></li>
            <li><strong>Address:</strong> <?php echo esc_html($address ?: __('Not specified', 'artpulse')); ?></li>
            <li><strong>Contact:</strong> <?php echo esc_html($contact ?: __('Not specified', 'artpulse')); ?></li>
            <?php if (!empty($rsvp) && filter_var($rsvp, FILTER_VALIDATE_URL)) : ?>
              <li><strong>RSVP:</strong> <a href="<?php echo esc_url($rsvp); ?>" class="event-rsvp-link" target="_blank">RSVP Now</a></li>
            <?php else : ?>
              <li><strong>RSVP:</strong> <?php esc_html_e('Not specified', 'artpulse'); ?></li>
            <?php endif; ?>
          </ul>

        <div class="event-description">
          <?php the_content(); ?>
        </div>

      </div>
    </div>
  </div>
</div>
