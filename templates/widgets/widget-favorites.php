<div class="ap-widget notice notice-info p-4 rounded">
  <div class="ap-widget-header">⭐ <?php esc_html_e( 'Saved Artists', 'artpulse' ); ?></div>
  <div class="ap-widget-body">
    <?php
    $user_id = get_current_user_id();
    $artists = get_user_meta($user_id, 'favorite_artists', true);
    if (empty($artists) || !is_array($artists)) {
        echo '<p>' . esc_html__('No favorites yet.', 'artpulse') . '</p>';
    } else {
        foreach ($artists as $artist_id) {
            echo '<p><a href="' . get_permalink($artist_id) . '">' . esc_html(get_the_title($artist_id)) . '</a></p>';
        }
    }
    ?>
  </div>
</div>

