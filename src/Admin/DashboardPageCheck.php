<?php
namespace ArtPulse\Admin;

class DashboardPageCheck
{
    private static array $missing = [];

    public static function register(): void
    {
        add_action('admin_init', [self::class, 'check_pages']);
    }

    public static function check_pages(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_GET['ap_create_dashboard_pages']) && check_admin_referer('ap_create_dashboard_pages')) {
            ShortcodePages::create_pages(['[ap_user_dashboard]', '[user_dashboard]']);
            add_action('admin_notices', static function () {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Dashboard pages created.', 'artpulse') . '</p></div>';
            });
            wp_safe_redirect(remove_query_arg(['ap_create_dashboard_pages', '_wpnonce']));
            exit;
        }

        $codes = ['[ap_user_dashboard]', '[user_dashboard]'];
        foreach ($codes as $code) {
            $pages = get_posts([
                'post_type'   => 'page',
                'post_status' => 'any',
                's'           => $code,
                'numberposts' => 1,
                'fields'      => 'ids',
            ]);
            if (empty($pages)) {
                self::$missing[] = $code;
            }
        }

        if (!empty(self::$missing)) {
            add_action('admin_notices', [self::class, 'render_notice']);
        }
    }

    public static function render_notice(): void
    {
        $url = wp_nonce_url(admin_url('admin.php?ap_create_dashboard_pages=1'), 'ap_create_dashboard_pages');
        $codes = implode(', ', self::$missing);
        echo '<div class="notice notice-warning"><p>';
        echo esc_html__('Required dashboard pages are missing:', 'artpulse') . ' ' . esc_html($codes) . ' '; 
        echo '<a href="' . esc_url($url) . '">' . esc_html__('Create pages now', 'artpulse') . '</a>';
        echo '</p></div>';
    }
}
