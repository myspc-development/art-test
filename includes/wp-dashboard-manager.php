<?php
/**
 * Register custom dashboard widgets using WordPress's meta box system.
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register three sample widgets on the main dashboard screen.
 */
function artpulse_add_wp_widget(string $id, string $title, callable $cb): void {
    static $ids = [];

    $id = sanitize_key($id);
    if (!$id || in_array($id, $ids, true)) {
        trigger_error('Duplicate or invalid dashboard widget ID: ' . $id, E_USER_WARNING);
        return;
    }

    $title = trim(strip_tags($title));
    if ($title === '') {
        trigger_error('Dashboard widget missing title for ID: ' . $id, E_USER_WARNING);
        return;
    }

    wp_add_dashboard_widget($id, $title, $cb);
    $ids[] = $id;
}

function artpulse_wp_register_widgets() {
    artpulse_add_wp_widget(
        'artpulse_widget_summary',
        __('ArtPulse Summary', 'artpulse'),
        'artpulse_widget_summary_render'
    );

    artpulse_add_wp_widget(
        'artpulse_widget_events',
        __('Upcoming Events', 'artpulse'),
        'artpulse_widget_events_render'
    );

    artpulse_add_wp_widget(
        'artpulse_widget_tags',
        __('Trending Tags', 'artpulse'),
        'artpulse_widget_tags_render'
    );

    // Move the trending tags widget to the sidebar column by default.
    global $wp_meta_boxes;
    if (isset($wp_meta_boxes['dashboard']['normal']['core']['artpulse_widget_tags'])) {
        $widget = $wp_meta_boxes['dashboard']['normal']['core']['artpulse_widget_tags'];
        unset($wp_meta_boxes['dashboard']['normal']['core']['artpulse_widget_tags']);
        $wp_meta_boxes['dashboard']['side']['core']['artpulse_widget_tags'] = $widget;
    }
}
add_action('wp_dashboard_setup', 'artpulse_wp_register_widgets');

function artpulse_widget_summary_render() {
    echo '<p>' . esc_html__( 'Welcome to ArtPulse. This widget summarizes your recent activity.', 'artpulse' ) . '</p>';
}

function artpulse_widget_events_render() {
    echo '<p>' . esc_html__( 'No upcoming events scheduled.', 'artpulse' ) . '</p>';
}

function artpulse_widget_tags_render() {
    echo '<p>' . esc_html__( 'Trending tags will appear here.', 'artpulse' ) . '</p>';
}

/**
 * Enqueue simple layout CSS for the dashboard widgets.
 */
function artpulse_wp_dashboard_styles($hook) {
    if ($hook !== 'index.php') {
        return;
    }
    wp_enqueue_style(
        'artpulse-wp-dashboard',
        plugins_url('../assets/css/wp-dashboard-layout.css', __FILE__),
        [],
        '1.0'
    );
}
add_action('admin_enqueue_scripts', 'artpulse_wp_dashboard_styles');
