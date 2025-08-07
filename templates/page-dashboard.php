<?php
/**
 * Template Name: User Dashboard
 */

use ArtPulse\Core\DashboardController;

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$user_id = get_current_user_id();
$layout  = DashboardController::get_user_dashboard_layout($user_id);

if (empty($layout)) {
    echo '<p>' . esc_html__('No widgets available for your dashboard.', 'artpulse') . '</p>';
} else {
    echo '<div class="ap-dashboard-grid">';
    foreach ($layout as $widget) {
        $id = $widget['id'] ?? '';
        if (!$id) {
            continue;
        }
        echo '<div class="ap-widget-card">';
        echo do_shortcode('[ap_widget id="' . esc_attr($id) . '"]');
        echo '</div>';
    }
    echo '</div>';
}

get_footer();
