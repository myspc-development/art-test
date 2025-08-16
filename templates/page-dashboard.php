<?php
/**
 * Template Name: User Dashboard
 */

use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetRegistry;

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$user_id = get_current_user_id();
$layout  = DashboardController::get_user_dashboard_layout($user_id);

if (empty($layout)) {
    echo '<p>' . esc_html__('No widgets available for your dashboard.', 'artpulse') . '</p>';
} else {
    echo '<div class="ap-dashboard-grid" role="grid" aria-label="Dashboard widgets">';
    foreach ($layout as $widget) {
        $id = $widget['id'] ?? '';
        if (!$id) {
            continue;
        }
        $config = DashboardWidgetRegistry::get_widget($id, $user_id);
        $label  = $config['label'] ?? $id;
        echo '<div class="ap-widget-card" role="gridcell" tabindex="0" aria-label="' . esc_attr($label) . '">';
        echo do_shortcode('[ap_widget id="' . esc_attr($id) . '"]');
        echo '</div>';
    }
    echo '</div>';
}

get_footer();
