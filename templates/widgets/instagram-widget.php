<?php
/**
 * Instagram widget template.
 */
$token = $args['access_token'] ?? '';
$count = isset($args['count']) ? (int) $args['count'] : 3;
$urls  = [];
$posts = [];

if ($token) {
    $cache_key = 'ap_instagram_posts_' . md5($token . '|' . $count);
    $posts     = get_transient($cache_key);
    if (false === $posts) {
        $resp = wp_remote_get("https://graph.instagram.com/me/media?fields=permalink,media_url,caption&access_token={$token}&limit={$count}");
        if (!is_wp_error($resp)) {
            $data  = json_decode(wp_remote_retrieve_body($resp), true);
            $posts = $data['data'] ?? [];
            set_transient($cache_key, $posts, HOUR_IN_SECONDS);
        } else {
            $posts = [];
        }
    }
} elseif (!empty($args['urls'])) {
    $urls = array_slice((array) $args['urls'], 0, $count);
    foreach ($urls as $u) {
        $posts[] = ['permalink' => $u];
    }
}
?>
<div class="ap-widget">
  <div class="ap-widget-header">📷 <?php _e('Instagram', 'artpulse'); ?></div>
  <div class="ap-widget-body">
    <?php foreach ($posts as $post) : ?>
      <div class="ap-instagram-post">
        <?php
        $embed = wp_oembed_get($post['permalink']);
        if ($embed) {
            echo $embed; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        } else {
            echo '<a href="' . esc_url($post['permalink']) . '" target="_blank">' . esc_html($post['permalink']) . '</a>';
        }
        ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>
