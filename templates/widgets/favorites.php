<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
extract(ap_template_context($args ?? [], ['visible' => true]));
/**
 * Dashboard widget: Favorites.
 */
use ArtPulse\Community\FavoritesManager;
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) {
    echo '<p class="notice">' . esc_html__( 'Preview mode — dynamic content hidden', 'artpulse' ) . '</p>';
    return;
}
?>
<div id="favorites" class="ap-card" role="region" aria-labelledby="favorites-title" data-widget="favorites" <?php echo $visible ? '' : 'hidden'; ?>>
    <h2 id="favorites-title" class="ap-card__title"><?php esc_html_e('My Favorites','artpulse'); ?></h2>
    <?php
    $user_id   = get_current_user_id();
    $favorites = FavoritesManager::get_favorites($user_id);
    if (empty($favorites)) {
        echo '<p>' . esc_html__('You haven\xE2\x80\x99t favorited any content yet.', 'artpulse') . '</p>';
    } else {
        $labels = [
            'event'        => __('Events', 'artpulse'),
            'artist'       => __('Artists', 'artpulse'),
            'organization' => __('Organizations', 'artpulse'),
            'artwork'      => __('Artworks', 'artpulse'),
        ];
        foreach (['event', 'artist', 'organization', 'artwork'] as $type) {
            if (!empty($favorites[$type])) {
                echo '<div class="favorite-group favorite-group-' . esc_attr($type) . '">';
                echo '<h3>' . esc_html($labels[$type]) . '</h3>';
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
    <button class="ap-widget-settings-btn ap-form-button nectar-button" data-widget-settings="favorites"><?php esc_html_e('Settings', 'artpulse'); ?></button>
</div>
