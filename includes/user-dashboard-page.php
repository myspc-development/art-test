<?php
/**
 * Ensure the front-end dashboard page exists and uses the plugin template.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get the user dashboard page ID if it exists.
 */
function ap_get_dashboard_page_id(): int {
    $page = get_page_by_path('dashboard');
    return $page ? (int) $page->ID : 0;
}

/**
 * Create the dashboard page if missing and set its template.
 */
function ap_ensure_user_dashboard_page(): int {
    $page_id = ap_get_dashboard_page_id();
    if ($page_id) {
        return $page_id;
    }
    $page_id = wp_insert_post([
        'post_title'   => __('User Dashboard', 'artpulse'),
        'post_name'    => 'dashboard',
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_content' => '[user_dashboard]',
        'comment_status' => 'closed',
    ]);
    if (!is_wp_error($page_id)) {
        update_post_meta($page_id, '_wp_page_template', 'simple-dashboard.php');
        return (int) $page_id;
    }
    return 0;
}

add_action('init', 'ap_ensure_user_dashboard_page');

add_filter('template_include', function ($template) {
    if (is_page('dashboard')) {
        $custom = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'templates/simple-dashboard.php';
        if (file_exists($custom)) {
            return $custom;
        }
    }
    return $template;
});
