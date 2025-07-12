<?php
$user_role = 'artist';

add_action('wp_enqueue_scripts', function () use ($user_role) {
    if (ap_user_can_edit_layout($user_role)) {
        wp_enqueue_script("{$user_role}-dashboard-js", plugin_dir_url(__FILE__) . "../assets/js/{$user_role}-dashboard.js", ['jquery-ui-sortable', 'dark-mode-toggle'], null, true);
        wp_localize_script("{$user_role}-dashboard-js", 'apDashboard', [
            'nonce'    => wp_create_nonce('ap_dashboard_nonce'),
        ]);
        wp_enqueue_script('dark-mode-toggle', plugin_dir_url(__FILE__) . '../assets/js/dark-mode-toggle.js', [], null, true);
        wp_enqueue_style('dashboard-style', plugin_dir_url(__FILE__) . '../assets/css/dashboard-widget.css');
    }
});

include locate_template('partials/dashboard-generic.php');

