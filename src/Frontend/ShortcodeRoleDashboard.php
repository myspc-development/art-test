<?php
namespace ArtPulse\Frontend;

class ShortcodeRoleDashboard
{
    public static function register(): void
    {
        add_shortcode('ap_role_dashboard', [self::class, 'render']);
        add_action('wp_enqueue_scripts', [self::class, 'maybe_enqueue']);
    }

    public static function enqueue_assets(string $role): void
    {
        $handle      = 'ap-role-dashboard';
        $script_path = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'dist/dashboard.js';
        if (file_exists($script_path)) {
            wp_enqueue_script(
                $handle,
                plugins_url('dist/dashboard.js', ARTPULSE_PLUGIN_FILE),
                ['wp-element'],
                filemtime($script_path),
                true
            );
            wp_localize_script(
                $handle,
                'apDashboardData',
                [
                    'currentUser' => wp_get_current_user()->user_login,
                    'role'        => $role,
                    'restBase'    => esc_url_raw(rest_url(ARTPULSE_API_NAMESPACE)),
                    'nonce'       => wp_create_nonce('wp_rest'),
                ]
            );
        }

        $style_path = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'assets/css/dashboard.css';
        if (file_exists($style_path)) {
            wp_enqueue_style(
                $handle,
                plugins_url('assets/css/dashboard.css', ARTPULSE_PLUGIN_FILE),
                [],
                filemtime($style_path)
            );
        }
    }

    public static function maybe_enqueue(): void
    {
        if (has_shortcode(get_post_field('post_content', get_the_ID() ?: 0), 'ap_role_dashboard')) {
            $role = is_user_logged_in() ? \ArtPulse\Core\DashboardController::get_role(get_current_user_id()) : 'member';
            self::enqueue_assets($role);
        }
    }

    public static function render($atts = []): string
    {
        if (!is_user_logged_in()) {
            return '';
        }
        $atts = shortcode_atts(['role' => 'member'], $atts, 'ap_role_dashboard');
        $role = sanitize_key($atts['role']);
        self::enqueue_assets($role);
        return '<div id="ap-dashboard-root" class="ap-dashboard-grid" data-role="' . esc_attr($role) . '"></div>';
    }
}

