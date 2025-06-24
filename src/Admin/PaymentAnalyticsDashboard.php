<?php
namespace ArtPulse\Admin;

use Stripe\StripeClient;

class PaymentAnalyticsDashboard
{
    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'addMenu']);
    }

    public static function addMenu(): void
    {
        add_submenu_page(
            'artpulse-settings',
            __('Payment Analytics', 'artpulse'),
            __('Payments', 'artpulse'),
            'manage_options',
            'artpulse-payment-analytics',
            [self::class, 'render']
        );
    }

    public static function render(): void
    {
        $metrics = self::get_metrics();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Payment Analytics', 'artpulse'); ?></h1>
            <table class="widefat">
                <thead>
                <tr>
                    <th><?php esc_html_e('Metric', 'artpulse'); ?></th>
                    <th><?php esc_html_e('Value', 'artpulse'); ?></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><?php esc_html_e('WooCommerce Revenue', 'artpulse'); ?></td>
                    <td><?php echo esc_html(number_format_i18n($metrics['woo_total_revenue'], 2)); ?></td>
                </tr>
                <tr>
                    <td><?php esc_html_e('Stripe Revenue', 'artpulse'); ?></td>
                    <td><?php echo esc_html(number_format_i18n($metrics['stripe_total_revenue'], 2)); ?></td>
                </tr>
                <tr>
                    <td><?php esc_html_e('Active Subscriptions', 'artpulse'); ?></td>
                    <td><?php echo intval($metrics['active_subscriptions']); ?></td>
                </tr>
                </tbody>
            </table>
        </div>
        <?php
    }
    public static function get_metrics(): array
    {
        $woo_total = 0;
        if (function_exists('wc_get_orders')) {
            $orders = wc_get_orders([
                'limit'  => -1,
                'status' => ['completed', 'processing'],
            ]);
            foreach ($orders as $order) {
                $woo_total += $order->get_total();
            }
        }

        $stripe_total = 0;
        $active_subs = 0;
        $opts = get_option('artpulse_settings', []);
        $secret = $opts['stripe_secret'] ?? '';
        if ($secret && class_exists(StripeClient::class)) {
            try {
                $stripe = new StripeClient($secret);
                $charges = $stripe->charges->all(['limit' => 100]);
                foreach ($charges->data as $charge) {
                    if ($charge->paid && !$charge->refunded) {
                        $stripe_total += $charge->amount / 100;
                    }
                }
                $subs = $stripe->subscriptions->all(['limit' => 100, 'status' => 'active']);
                $active_subs = count($subs->data);
            } catch (\Exception $e) {
                // Ignore errors
            }
        }

        return [
            'woo_total_revenue'    => round($woo_total, 2),
            'stripe_total_revenue' => round($stripe_total, 2),
            'active_subscriptions' => $active_subs,
        ];
    }
}
