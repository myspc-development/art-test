<?php
$user_role = 'organization';

add_action('wp_enqueue_scripts', function () use ($user_role) {
    if (ap_user_can_edit_layout($user_role)) {
        wp_enqueue_script('sortablejs', plugin_dir_url(__FILE__) . '../assets/js/Sortable.min.js', [], '1.14', true);
        wp_enqueue_script("{$user_role}-dashboard-js", plugin_dir_url(__FILE__) . "../assets/js/{$user_role}-dashboard.js", ['sortablejs', 'dark-mode-toggle'], null, true);
        wp_localize_script("{$user_role}-dashboard-js", 'APWidgetOrder', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('ap_widget_order'),
        ]);
        wp_enqueue_script('dark-mode-toggle', plugin_dir_url(__FILE__) . '../assets/js/dark-mode-toggle.js', [], null, true);
        wp_enqueue_style('dashboard-style', plugin_dir_url(__FILE__) . '../assets/css/dashboard-widget.css');
    }
});

include locate_template('partials/dashboard-generic.php');

