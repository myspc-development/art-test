<div class="ap-widget">
  <div class="ap-widget-header">🎟 Upcoming Events</div>
  <div class="ap-widget-body">
    <?php
    $events = get_posts(['post_type' => 'artpulse_event', 'numberposts' => 3]);
    foreach ($events as $event) {
        echo '<p><a href="' . get_permalink($event) . '">' . esc_html($event->post_title) . '</a></p>';
    }
    ?>
  </div>
</div>

