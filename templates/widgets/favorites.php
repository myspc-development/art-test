<?php
/**
 * Dashboard widget: Favorites.
 */
use ArtPulse\Community\FavoritesManager;
?>
<div class="dashboard-card" data-widget="favorites">
    <h2 id="favorites"><?php esc_html_e('My Favorites','artpulse'); ?></h2>
    <?php
    $user_id   = get_current_user_id();
    $favorites = FavoritesManager::get_favorites($user_id);
    if (empty($favorites)) {
        echo '<p>' . esc_html__('You haven\xE2\x80\x99t favorited any content yet.', 'artpulse') . '</p>';
    } else {
        foreach (['event', 'artist', 'organization', 'artwork'] as $type) {
            if (!empty($favorites[$type])) {
                echo '<div class="favorite-group favorite-group-' . esc_attr($type) . '">';
                echo '<h3>' . esc_html(ucfirst($type)) . 's</h3>';
                echo '<ul class="favorites-list">';
                foreach ($favorites[$type] as $post_id) {
                    $title = get_the_title($post_id);
                    $link  = get_permalink($post_id);
                    echo '<li><a href="' . esc_url($link) . '">' . esc_html($title) . '</a></li>';
                }
                echo '</ul></div>';
            }
        }
    }
    ?>
</div>
