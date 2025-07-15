<div id="ap-widget-favorites" class="ap-card" role="region" aria-labelledby="ap-widget-favorites-title">
  <h2 id="ap-widget-favorites-title" class="ap-card__title">⭐ <?php esc_html_e( 'Saved Artists', 'artpulse' ); ?></h2>
  <div>
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

