<?php
/**
 * Dashboard navigation links.
 *
 * Expected variables: $show_notifications (bool).
 */

$current_user = wp_get_current_user();
$roles        = $current_user->roles;
if ( defined('WP_DEBUG') && WP_DEBUG ) {
    error_log('AP Dashboard Roles: ' . wp_json_encode($roles));
}

$menu_items = ap_merge_dashboard_menus($roles, !empty($show_notifications));
if ( defined('WP_DEBUG') && WP_DEBUG ) {
    error_log('AP Dashboard Menu: ' . wp_json_encode($menu_items));
}

?>
<nav class="dashboard-nav" role="navigation" aria-label="Dashboard Navigation">
    <?php foreach ($menu_items as $item): ?>
        <?php if (!empty($item['capability']) && !current_user_can($item['capability'])) continue; ?>
        <a href="<?= esc_attr($item['section']) ?>" class="dashboard-link nectar-button wpb_button small" data-section="<?= esc_attr(ltrim($item['section'], '#')) ?>">
            <span class="dashicons <?= esc_attr($item['icon']) ?>"></span><?= esc_html($item['label']) ?>
        </a>
    <?php endforeach; ?>
</nav>
