<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_dashboard_setup', function () {
    wp_add_dashboard_widget(
        'ap_messages_widget',
        'Recent Messages',
        function () {
            echo '<div id="ap-messages-dashboard-widget">Loading messages...</div>';
        }
    );
});

add_action('admin_enqueue_scripts', function($hook) {
    if ($hook === 'index.php') {
        wp_enqueue_script(
            'ap-dashboard-messages',
            plugin_dir_url(__FILE__) . '../assets/js/dashboard-messages.js',
            [],
            null,
            true
        );
    }
});
