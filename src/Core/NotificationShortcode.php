<?php
namespace ArtPulse\Core;

class NotificationShortcode {
    public static function register() {
        if (!shortcode_exists('ap_notifications')) {
            add_shortcode('ap_notifications', [__CLASS__, 'render']);
        }
    }

    public static function render($atts = []) {
        $user_id = get_current_user_id();
        if (!$user_id) return '<p>Please log in to see notifications.</p>';

        $notifications = get_user_meta($user_id, 'ap_notifications', true);
        if (!is_array($notifications)) return '<p>No notifications.</p>';

        ob_start();
        echo '<ul class="ap-notifications">';
        foreach ($notifications as $n) {
            echo '<li>' . esc_html($n) . '</li>';
        }
        echo '</ul>';
        return ob_get_clean();
    }
}
