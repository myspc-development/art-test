<?php
/**
 * Shortcodes for rendering common user dashboard widgets.
 */

if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\Core\ShortcodeRegistry;
use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\RoleResolver;

function ap_user_events_shortcode(): string {
    return do_shortcode('[ap_widget id="my-events"]');
}
ShortcodeRegistry::register('ap_user_events', 'User Events', 'ap_user_events_shortcode');

function ap_user_follows_shortcode(): string {
    return do_shortcode('[ap_widget id="my-follows"]');
}
ShortcodeRegistry::register('ap_user_follows', 'User Follows', 'ap_user_follows_shortcode');

function ap_user_analytics_shortcode(): string {
    return do_shortcode('[ap_widget id="artpulse_analytics_widget"]');
}
ShortcodeRegistry::register('ap_user_analytics', 'User Analytics', 'ap_user_analytics_shortcode');

function user_dashboard_shortcode(): string {
    if (!is_user_logged_in()) {
        return '';
    }
    wp_enqueue_style('ap-react-dashboard');
    wp_enqueue_script('ap-react-vendor');
    wp_enqueue_script('ap-react-dashboard');

    $user_id = get_current_user_id();
    $role    = function_exists('ap_get_effective_role') ? ap_get_effective_role() : RoleResolver::resolve($user_id);
    $layout  = DashboardController::get_user_dashboard_layout($user_id);

    $widgets = [];
    foreach ($layout as $entry) {
        $id = $entry['id'] ?? '';
        if ($id && DashboardWidgetRegistry::user_can_see($id, $user_id)) {
            $widgets[] = $id;
        }
    }

    if (empty($widgets)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $layouts = get_option('artpulse_default_layouts', array());
            if (is_string($layouts)) {
                $d = json_decode($layouts, true);
                if (is_array($d)) {
                    $layouts = $d;
                } else {
                    $layouts = array();
                }
            }
            $layout_ids = isset($layouts[$role]) && is_array($layouts[$role]) ? $layouts[$role] : array();
            error_log(sprintf(
                '⛔ No visible widgets => role=%s layout_count=%d layout_ids=[%s]',
                $role,
                is_array($layout_ids) ? count($layout_ids) : 0,
                is_array($layout_ids) ? implode(',', $layout_ids) : ''
            ));
        }
        $widgets = ['empty_dashboard'];
    }

    ob_start();
    echo '<div class="ap-dashboard-fallback" data-role="' . esc_attr($role) . '">';
    foreach ($widgets as $id) {
        ap_render_widget($id, $user_id);
    }
    echo '</div>';
    return ob_get_clean();
}
ShortcodeRegistry::register('user_dashboard', 'User Dashboard', 'user_dashboard_shortcode');
