<?php
namespace ArtPulse\Rest;

class LayoutSaveEndpoint
{
    public static function register(): void
    {
        add_action('wp_ajax_save_dashboard_layout', [self::class, 'handle']);
    }

    public static function handle(): void
    {
        check_ajax_referer('ap_dashboard_nonce');

        $user_id = get_current_user_id();
        $layout  = $_POST['layout'] ?? [];

        if (!is_array($layout)) {
            wp_send_json_error('Invalid layout format.');
        }

        update_user_meta($user_id, 'ap_dashboard_layout', $layout);
        wp_send_json_success(['message' => 'Layout saved.']);
    }
}
