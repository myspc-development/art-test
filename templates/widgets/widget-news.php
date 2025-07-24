<div id="ap-widget-news" class="ap-card" role="region" aria-labelledby="ap-widget-news-title">
  <h2 id="ap-widget-news-title" class="ap-card__title">📰 <?php esc_html_e( 'Latest News', 'artpulse' ); ?></h2>
  <div>
    <?php
    $recent_posts = [];
    if (!defined('IS_DASHBOARD_BUILDER_PREVIEW') || !IS_DASHBOARD_BUILDER_PREVIEW) {
        $recent_posts = get_posts(['post_type' => 'post', 'numberposts' => 3]);
    }
    foreach ($recent_posts as $post) {
        echo '<p><a href="' . get_permalink($post) . '">' . esc_html($post->post_title) . '</a></p>';
    }
    ?>
  </div>
</div>

