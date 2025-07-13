<div class="ap-widget notice notice-info p-4 rounded">
  <div class="ap-widget-header">🎟 <?php esc_html_e( 'Upcoming Events', 'artpulse' ); ?></div>
  <div class="ap-widget-body">
    <?php
    $events = get_posts(['post_type' => 'artpulse_event', 'numberposts' => 3]);
    foreach ($events as $event) {
        echo '<p><a href="' . get_permalink($event) . '">' . esc_html($event->post_title) . '</a></p>';
    }
    ?>
  </div>
</div>

