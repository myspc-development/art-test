<?php
namespace ArtPulse\Admin;

class PaymentReportsPage
{
    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'addMenu']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueue']);
    }

    public static function addMenu(): void
    {
        add_submenu_page(
            'artpulse-settings',
            __('Payment Reports', 'artpulse'),
            __('Payment Reports', 'artpulse'),
            'manage_options',
            'ap-payment-reports',
            [self::class, 'render']
        );
    }

    public static function enqueue(string $hook): void
    {
        if ($hook !== 'artpulse-settings_page_ap-payment-reports') {
            return;
        }
        $path = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'assets/js/payment-reports.js';
        $url  = plugin_dir_url(ARTPULSE_PLUGIN_FILE) . 'assets/js/payment-reports.js';
        if (file_exists($path)) {
            wp_enqueue_script(
                'ap-payment-reports',
                $url,
                ['wp-element'],
                filemtime($path),
                true
            );
            wp_localize_script('ap-payment-reports', 'APPaymentReports', [
                'apiRoot' => esc_url_raw(rest_url()),
                'nonce'   => wp_create_nonce('wp_rest'),
            ]);
        }
    }

    public static function render(): void
    {
        echo '<div class="wrap"><h1>' . esc_html__('Payment Reports', 'artpulse') . '</h1>';
        echo '<div id="ap-payment-reports-root"></div></div>';
    }
}
