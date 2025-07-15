<div id="ap-widget-events" class="ap-card" role="region" aria-labelledby="ap-widget-events-title">
  <h2 id="ap-widget-events-title" class="ap-card__title">🎟 <?php esc_html_e( 'Upcoming Events', 'artpulse' ); ?></h2>
  <div>
    <?php
    $events = get_posts(['post_type' => 'artpulse_event', 'numberposts' => 3]);
    foreach ($events as $event) {
        echo '<p><a href="' . get_permalink($event) . '">' . esc_html($event->post_title) . '</a></p>';
    }
    ?>
  </div>
</div>

