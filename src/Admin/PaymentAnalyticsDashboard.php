<?php
namespace ArtPulse\Admin;

use Stripe\StripeClient;

class PaymentAnalyticsDashboard {

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'addMenu' ) );
	}

	public static function addMenu(): void {
		add_submenu_page(
			'artpulse-settings',
			__( 'Payment Analytics', 'artpulse' ),
			__( 'Payments', 'artpulse' ),
			'manage_options',
			'artpulse-payment-analytics',
			array( self::class, 'render' )
		);
	}

	public static function render(): void {
		$start_date = sanitize_text_field( $_GET['start_date'] ?? '' );
		$end_date   = sanitize_text_field( $_GET['end_date'] ?? '' );

		$chart_rel  = 'assets/libs/chart.js/4.4.1/chart.min.js';
		$chart_path = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . $chart_rel;
		$chart_ver  = file_exists( $chart_path ) ? filemtime( $chart_path ) : '4.4.1';

		wp_enqueue_script(
			'chart-js',
			plugins_url( $chart_rel, ARTPULSE_PLUGIN_FILE ),
			array(),
			$chart_ver,
			true
		);

		$script_path = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . 'assets/js/payment-analytics-dashboard.js';
		wp_enqueue_script(
			'ap-payment-dashboard',
			plugins_url( 'assets/js/payment-analytics-dashboard.js', ARTPULSE_PLUGIN_FILE ),
			array( 'chart-js' ),
			file_exists( $script_path ) ? filemtime( $script_path ) : '1.0',
			true
		);

		$metrics = self::get_metrics( $start_date, $end_date );

		wp_localize_script(
			'ap-payment-dashboard',
			'APPaymentDashboard',
			array(
				'months'        => $metrics['months'],
				'revenue'       => $metrics['revenue_trend'],
				'subscriptions' => $metrics['subscription_trend'],
			)
		);
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Payment Analytics', 'artpulse' ); ?></h1>

			<form method="get">
				<input type="hidden" name="page" value="artpulse-payment-analytics" />
				<label><?php esc_html_e( 'Start Date', 'artpulse' ); ?>
					<input type="date" name="start_date" value="<?php echo esc_attr( $start_date ); ?>" />
				</label>
				<label><?php esc_html_e( 'End Date', 'artpulse' ); ?>
					<input type="date" name="end_date" value="<?php echo esc_attr( $end_date ); ?>" />
				</label>
				<button class="button" type="submit"><?php esc_html_e( 'Filter', 'artpulse' ); ?></button>
			</form>

			<div>
				<canvas id="ap-payment-revenue-chart" height="120"></canvas>
			</div>
			<div>
				<canvas id="ap-payment-subscriptions-chart" height="120"></canvas>
			</div>

			<table class="widefat">
				<thead>
				<tr>
					<th><?php esc_html_e( 'Metric', 'artpulse' ); ?></th>
					<th><?php esc_html_e( 'Value', 'artpulse' ); ?></th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td><?php esc_html_e( 'WooCommerce Revenue', 'artpulse' ); ?></td>
					<td><?php echo esc_html( number_format_i18n( $metrics['woo_total_revenue'], 2 ) ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Stripe Revenue', 'artpulse' ); ?></td>
					<td><?php echo esc_html( number_format_i18n( $metrics['stripe_total_revenue'], 2 ) ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Active Subscriptions', 'artpulse' ); ?></td>
					<td><?php echo intval( $metrics['active_subscriptions'] ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Churn Rate', 'artpulse' ); ?></td>
					<td><?php echo esc_html( number_format_i18n( $metrics['churn_rate'], 2 ) ) . '%'; ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Monthly Revenue Growth', 'artpulse' ); ?></td>
					<td><?php echo esc_html( number_format_i18n( $metrics['monthly_revenue_growth'], 2 ) ) . '%'; ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Monthly Subscription Growth', 'artpulse' ); ?></td>
					<td><?php echo esc_html( number_format_i18n( $metrics['monthly_subscription_growth'], 2 ) ) . '%'; ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Payment Success Rate', 'artpulse' ); ?></td>
					<td><?php echo esc_html( number_format_i18n( $metrics['payment_success_rate'], 2 ) ) . '%'; ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Payment Failure Rate', 'artpulse' ); ?></td>
					<td><?php echo esc_html( number_format_i18n( $metrics['payment_failure_rate'], 2 ) ) . '%'; ?></td>
				</tr>
				</tbody>
			</table>
		</div>
		<?php
	}
	public static function get_metrics( string $start_date = '', string $end_date = '' ): array {
		$cache_key = 'ap_payment_metrics_' . md5( $start_date . '_' . $end_date );
		$cached    = get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		$start_ts = $start_date ? strtotime( $start_date . ' 00:00:00' ) : strtotime( '-5 months', current_time( 'timestamp' ) );
		$end_ts   = $end_date ? strtotime( $end_date . ' 23:59:59' ) : current_time( 'timestamp' );

		// Ensure start is before end
		if ( $start_ts > $end_ts ) {
			$tmp      = $start_ts;
			$start_ts = $end_ts;
			$end_ts   = $tmp;
		}

		$woo_total           = 0;
		$wc_success          = 0;
		$wc_failure          = 0;
		$wc_cancellations    = 0;
		$wc_month_total      = 0;
		$wc_last_month_total = 0;

		$month_start      = strtotime( 'first day of this month 00:00:00' );
		$last_month_start = strtotime( 'first day of last month 00:00:00' );

		$months        = array();
		$rev_by_month  = array();
		$subs_by_month = array();
		$iter          = strtotime( date( 'Y-m-01', $start_ts ) );
		while ( $iter <= $end_ts ) {
			$key                   = date( 'Y-m', $iter );
			$months[]              = date_i18n( 'M Y', $iter );
			$rev_by_month[ $key ]  = 0;
			$subs_by_month[ $key ] = 0;
			$iter                  = strtotime( '+1 month', $iter );
		}

		if ( function_exists( 'wc_get_orders' ) ) {
			$orders = wc_get_orders(
				array(
					'limit'        => -1,
					'status'       => array( 'completed', 'processing', 'failed', 'cancelled', 'refunded' ),
					'date_created' => '>=' . gmdate( 'Y-m-d H:i:s', $start_ts ),
				)
			);
			foreach ( $orders as $order ) {
				$created = $order->get_date_created() ? $order->get_date_created()->getTimestamp() : 0;
				if ( $created < $start_ts || $created > $end_ts ) {
					continue;
				}

				$total  = $order->get_total();
				$status = $order->get_status();

				if ( in_array( $status, array( 'completed', 'processing' ), true ) ) {
					$woo_total += $total;
					++$wc_success;
				} else {
					++$wc_failure;
					if ( in_array( $status, array( 'cancelled', 'refunded' ), true ) ) {
						++$wc_cancellations;
					}
				}

				$created = $order->get_date_created() ? $order->get_date_created()->getTimestamp() : 0;
				if ( $created >= $month_start ) {
					$wc_month_total += $total;
				} elseif ( $created >= $last_month_start && $created < $month_start ) {
					$wc_last_month_total += $total;
				}

				$key = date( 'Y-m', $created );
				if ( isset( $rev_by_month[ $key ] ) ) {
					$rev_by_month[ $key ] += $total;
				}
			}
		}

		$stripe_total            = 0;
		$stripe_month_total      = 0;
		$stripe_last_month_total = 0;
		$active_subs             = 0;
		$cancellations           = 0;
		$stripe_success          = 0;
		$stripe_failure          = 0;
		$sub_month_count         = 0;
		$sub_last_month_count    = 0;

		$opts   = get_option( 'artpulse_settings', array() );
		$secret = $opts['stripe_secret'] ?? '';
		if ( $secret && class_exists( StripeClient::class ) ) {
			try {
				$stripe  = new StripeClient( $secret );
				$charges = $stripe->charges->all( array( 'limit' => 100 ) );
				foreach ( $charges->data as $charge ) {
					$amount = $charge->amount / 100;
					$time   = $charge->created;
					if ( $time < $start_ts || $time > $end_ts ) {
						continue;
					}
					if ( $charge->paid && ! $charge->refunded ) {
						$stripe_total += $amount;
						++$stripe_success;
						if ( $time >= $month_start ) {
							$stripe_month_total += $amount;
						} elseif ( $time >= $last_month_start && $time < $month_start ) {
							$stripe_last_month_total += $amount;
						}

						$key = date( 'Y-m', $time );
						if ( isset( $rev_by_month[ $key ] ) ) {
							$rev_by_month[ $key ] += $amount;
						}
					} else {
						++$stripe_failure;
					}
				}

				$subs = $stripe->subscriptions->all(
					array(
						'limit'  => 100,
						'status' => 'all',
					)
				);
				foreach ( $subs->data as $sub ) {
					$created = $sub->created;
					if ( $created < $start_ts || $created > $end_ts ) {
						continue;
					}
					if ( $sub->status === 'active' ) {
						++$active_subs;
					}
					if ( $sub->status === 'canceled' ) {
						++$cancellations;
					}
					if ( $created >= $month_start ) {
						++$sub_month_count;
					} elseif ( $created >= $last_month_start && $created < $month_start ) {
						++$sub_last_month_count;
					}

					$key = date( 'Y-m', $created );
					if ( isset( $subs_by_month[ $key ] ) ) {
						++$subs_by_month[ $key ];
					}
				}
			} catch ( \Exception $e ) {
				// Ignore errors
			}
		}

		$current_revenue        = $wc_month_total + $stripe_month_total;
		$last_revenue           = $wc_last_month_total + $stripe_last_month_total;
		$monthly_revenue_growth = $last_revenue > 0 ? ( ( $current_revenue - $last_revenue ) / $last_revenue ) * 100 : 0;

		$monthly_sub_growth = $sub_last_month_count > 0 ? ( ( $sub_month_count - $sub_last_month_count ) / $sub_last_month_count ) * 100 : 0;

		$total_success = $stripe_success + $wc_success;
		$total_failure = $stripe_failure + $wc_failure;

		$payment_success_rate = ( $total_success + $total_failure ) > 0 ? ( $total_success / ( $total_success + $total_failure ) ) * 100 : 0;
		$payment_failure_rate = ( $total_success + $total_failure ) > 0 ? ( $total_failure / ( $total_success + $total_failure ) ) * 100 : 0;

		$churn_rate = ( $cancellations + $active_subs ) > 0 ? ( $cancellations / ( $cancellations + $active_subs ) ) * 100 : 0;

		$metrics = array(
			'woo_total_revenue'           => round( $woo_total, 2 ),
			'stripe_total_revenue'        => round( $stripe_total, 2 ),
			'active_subscriptions'        => $active_subs,
			'churn_rate'                  => round( $churn_rate, 2 ),
			'monthly_revenue_growth'      => round( $monthly_revenue_growth, 2 ),
			'monthly_subscription_growth' => round( $monthly_sub_growth, 2 ),
			'payment_success_rate'        => round( $payment_success_rate, 2 ),
			'payment_failure_rate'        => round( $payment_failure_rate, 2 ),
			'months'                      => $months,
			'revenue_trend'               => array_values( $rev_by_month ),
			'subscription_trend'          => array_values( $subs_by_month ),
		);

		$cache_minutes = absint( $opts['payment_metrics_cache'] ?? 15 );
		$cache_time    = (int) apply_filters( 'artpulse_payment_metrics_cache_time', $cache_minutes * MINUTE_IN_SECONDS );
		set_transient( $cache_key, $metrics, $cache_time );

		return $metrics;
	}
}
