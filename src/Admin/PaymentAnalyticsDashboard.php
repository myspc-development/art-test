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
                <tr>
                    <td><?php esc_html_e('Churn Rate', 'artpulse'); ?></td>
                    <td><?php echo esc_html(number_format_i18n($metrics['churn_rate'], 2)) . '%'; ?></td>
                </tr>
                <tr>
                    <td><?php esc_html_e('Monthly Revenue Growth', 'artpulse'); ?></td>
                    <td><?php echo esc_html(number_format_i18n($metrics['monthly_revenue_growth'], 2)) . '%'; ?></td>
                </tr>
                <tr>
                    <td><?php esc_html_e('Monthly Subscription Growth', 'artpulse'); ?></td>
                    <td><?php echo esc_html(number_format_i18n($metrics['monthly_subscription_growth'], 2)) . '%'; ?></td>
                </tr>
                <tr>
                    <td><?php esc_html_e('Payment Success Rate', 'artpulse'); ?></td>
                    <td><?php echo esc_html(number_format_i18n($metrics['payment_success_rate'], 2)) . '%'; ?></td>
                </tr>
                <tr>
                    <td><?php esc_html_e('Payment Failure Rate', 'artpulse'); ?></td>
                    <td><?php echo esc_html(number_format_i18n($metrics['payment_failure_rate'], 2)) . '%'; ?></td>
                </tr>
                </tbody>
            </table>
        </div>
        <?php
    }
    public static function get_metrics(): array
    {
        $cached = get_transient('ap_payment_metrics');
        if (false !== $cached) {
            return $cached;
        }

        $woo_total            = 0;
        $wc_success           = 0;
        $wc_failure           = 0;
        $wc_cancellations     = 0;
        $wc_month_total       = 0;
        $wc_last_month_total  = 0;

        $month_start     = strtotime('first day of this month 00:00:00');
        $last_month_start = strtotime('first day of last month 00:00:00');

        if (function_exists('wc_get_orders')) {
            $lookback_days = absint(apply_filters('artpulse_payment_metrics_lookback_days', 365));
            $date_after    = current_time('timestamp') - ($lookback_days * DAY_IN_SECONDS);
            $orders = wc_get_orders([
                'limit'        => -1,
                'status'       => ['completed', 'processing', 'failed', 'cancelled', 'refunded'],
                'date_created' => '>' . gmdate('Y-m-d H:i:s', $date_after),
            ]);
            foreach ($orders as $order) {
                $total  = $order->get_total();
                $status = $order->get_status();

                if (in_array($status, ['completed', 'processing'], true)) {
                    $woo_total += $total;
                    $wc_success++;
                } else {
                    $wc_failure++;
                    if (in_array($status, ['cancelled', 'refunded'], true)) {
                        $wc_cancellations++;
                    }
                }

                $created = $order->get_date_created() ? $order->get_date_created()->getTimestamp() : 0;
                if ($created >= $month_start) {
                    $wc_month_total += $total;
                } elseif ($created >= $last_month_start && $created < $month_start) {
                    $wc_last_month_total += $total;
                }
            }
        }

        $stripe_total           = 0;
        $stripe_month_total     = 0;
        $stripe_last_month_total = 0;
        $active_subs            = 0;
        $cancellations          = 0;
        $stripe_success         = 0;
        $stripe_failure         = 0;
        $sub_month_count        = 0;
        $sub_last_month_count   = 0;

        $opts   = get_option('artpulse_settings', []);
        $secret = $opts['stripe_secret'] ?? '';
        if ($secret && class_exists(StripeClient::class)) {
            try {
                $stripe  = new StripeClient($secret);
                $charges = $stripe->charges->all(['limit' => 100]);
                foreach ($charges->data as $charge) {
                    $amount = $charge->amount / 100;
                    $time   = $charge->created;
                    if ($charge->paid && !$charge->refunded) {
                        $stripe_total += $amount;
                        $stripe_success++;
                        if ($time >= $month_start) {
                            $stripe_month_total += $amount;
                        } elseif ($time >= $last_month_start && $time < $month_start) {
                            $stripe_last_month_total += $amount;
                        }
                    } else {
                        $stripe_failure++;
                    }
                }

                $subs = $stripe->subscriptions->all(['limit' => 100, 'status' => 'all']);
                foreach ($subs->data as $sub) {
                    $created = $sub->created;
                    if ($sub->status === 'active') {
                        $active_subs++;
                    }
                    if ($sub->status === 'canceled') {
                        $cancellations++;
                    }
                    if ($created >= $month_start) {
                        $sub_month_count++;
                    } elseif ($created >= $last_month_start && $created < $month_start) {
                        $sub_last_month_count++;
                    }
                }
            } catch (\Exception $e) {
                // Ignore errors
            }
        }

        $current_revenue = $wc_month_total + $stripe_month_total;
        $last_revenue    = $wc_last_month_total + $stripe_last_month_total;
        $monthly_revenue_growth = $last_revenue > 0 ? (($current_revenue - $last_revenue) / $last_revenue) * 100 : 0;

        $monthly_sub_growth = $sub_last_month_count > 0 ? (($sub_month_count - $sub_last_month_count) / $sub_last_month_count) * 100 : 0;

        $total_success = $stripe_success + $wc_success;
        $total_failure = $stripe_failure + $wc_failure;

        $payment_success_rate = ($total_success + $total_failure) > 0 ? ($total_success / ($total_success + $total_failure)) * 100 : 0;
        $payment_failure_rate = ($total_success + $total_failure) > 0 ? ($total_failure / ($total_success + $total_failure)) * 100 : 0;

        $churn_rate = ($cancellations + $active_subs) > 0 ? ($cancellations / ($cancellations + $active_subs)) * 100 : 0;

        $metrics = [
            'woo_total_revenue'          => round($woo_total, 2),
            'stripe_total_revenue'       => round($stripe_total, 2),
            'active_subscriptions'       => $active_subs,
            'churn_rate'                 => round($churn_rate, 2),
            'monthly_revenue_growth'     => round($monthly_revenue_growth, 2),
            'monthly_subscription_growth'=> round($monthly_sub_growth, 2),
            'payment_success_rate'       => round($payment_success_rate, 2),
            'payment_failure_rate'       => round($payment_failure_rate, 2),
        ];

        set_transient('ap_payment_metrics', $metrics, 15 * MINUTE_IN_SECONDS);

        return $metrics;
    }
}
