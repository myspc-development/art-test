<?php
namespace ArtPulse\Frontend;

class LogoutShortcode
{
    public static function register(): void
    {
        add_shortcode('ap_logout', [self::class, 'render']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_styles']);
    }

    public static function enqueue_styles(): void
    {
        if (function_exists('ap_enqueue_global_styles')) {
            ap_enqueue_global_styles();
        }
    }

    public static function render($atts = []): string
    {
        if (!is_user_logged_in()) {
            return '';
        }

        $atts = shortcode_atts([
            'redirect' => home_url('/')
        ], $atts, 'ap_logout');

        $url = wp_logout_url(esc_url_raw($atts['redirect']));

        return '<a class="ap-logout-link" href="' . esc_url($url) . '">' . esc_html__('Log Out', 'artpulse') . '</a>';
    }
}
