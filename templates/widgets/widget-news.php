<div class="ap-widget notice notice-info p-4 rounded">
  <div class="ap-widget-header">📰 <?php esc_html_e( 'Latest News', 'artpulse' ); ?></div>
  <div class="ap-widget-body">
    <?php
    $recent_posts = get_posts(['post_type' => 'post', 'numberposts' => 3]);
    foreach ($recent_posts as $post) {
        echo '<p><a href="' . get_permalink($post) . '">' . esc_html($post->post_title) . '</a></p>';
    }
    ?>
  </div>
</div>

