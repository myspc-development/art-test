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
        $handle = 'ap-dashboard-messages';
        wp_enqueue_script(
            $handle,
            plugin_dir_url(__FILE__) . '../assets/js/dashboard-messages.js',
            [],
            null,
            true
        );

        wp_localize_script($handle, 'ArtPulseData', [
            'nonce' => wp_create_nonce('wp_rest')
        ]);
    }
});

// Modal container for replying directly from the dashboard
add_action('admin_footer-index.php', function () {
    echo '<div id="ap-message-modal" style="display:none; position:fixed; top:20%; left:50%; transform:translateX(-50%); background:#fff; padding:20px; border:1px solid #ccc; border-radius:6px; z-index:9999; width:300px;">';
    echo '<h4>Reply to Message</h4>';
    echo '<textarea id="ap-reply-text" rows="4" style="width:100%; margin-bottom:10px;"></textarea>';
    echo '<button id="ap-send-reply" style="margin-right:10px;">Send</button>';
    echo '<button id="ap-cancel-reply">Cancel</button>';
    echo '</div>';
});
