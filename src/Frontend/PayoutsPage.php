<?php
namespace ArtPulse\Frontend;

class PayoutsPage
{
    public static function register(): void
    {
        add_shortcode('ap_payouts', [self::class, 'render']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue']);
    }

    public static function enqueue(): void
    {
        if (function_exists('ap_enqueue_global_styles')) {
            ap_enqueue_global_styles();
        }
        wp_enqueue_script('ap-payouts-js');
    }

    public static function render(): string
    {
        if (!is_user_logged_in()) {
            return '<p>' . esc_html__('Please log in to view payouts.', 'artpulse') . '</p>';
        }
        $method = get_user_meta(get_current_user_id(), 'ap_payout_method', true);
        ob_start();
        $template_path = plugin_dir_path(__FILE__) . '../../templates/payouts.php';
        if (file_exists($template_path)) {
            include $template_path;
        }
        return ob_get_clean();
    }
}
