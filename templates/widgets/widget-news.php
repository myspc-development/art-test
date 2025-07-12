<div class="ap-widget">
  <div class="ap-widget-header">📰 Latest News</div>
  <div class="ap-widget-body">
    <?php
    $recent_posts = get_posts(['post_type' => 'post', 'numberposts' => 3]);
    foreach ($recent_posts as $post) {
        echo '<p><a href="' . get_permalink($post) . '">' . esc_html($post->post_title) . '</a></p>';
    }
    ?>
  </div>
</div>

