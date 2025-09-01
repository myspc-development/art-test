<?php
namespace ArtPulse\Admin\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\PaymentAnalyticsDashboard;

/**

 * @group admin

 */

class PaymentAnalyticsDashboardTest extends TestCase {

	protected function setUp(): void {
		Stub::reset();
		Stub::$current_time = strtotime( '2024-03-20' );
	}

	public function test_returns_cached_metrics_when_present(): void {
		$key                      = 'ap_payment_metrics_' . md5( '2024-01-01_2024-01-31' );
		Stub::$transients[ $key ] = array( 'cached' => true );

		$metrics = PaymentAnalyticsDashboard::get_metrics( '2024-01-01', '2024-01-31' );

		$this->assertSame( array( 'cached' => true ), $metrics );
		$this->assertSame( 0, Stub::$wc_calls );
		$this->assertSame( 0, Stub::$stripe_charge_calls );
		$this->assertSame( 0, Stub::$stripe_subs_calls );
	}

	public function test_metrics_calculation_with_woo_and_stripe(): void {
		Stub::$orders = array(
			new WC_Order( strtotime( '2024-02-15' ), 10, 'completed' ),
			new WC_Order( strtotime( '2024-03-05' ), 20, 'processing' ),
			new WC_Order( strtotime( '2024-03-15' ), 5, 'failed' ),
			new WC_Order( strtotime( '2024-03-18' ), 30, 'cancelled' ),
			new WC_Order( strtotime( '2024-01-10' ), 40, 'completed' ),
		);

		Stub::$charges = array(
			(object) array(
				'amount'   => 1500,
				'currency' => 'usd',
				'created'  => strtotime( '2024-02-10' ),
				'paid'     => true,
				'refunded' => false,
				'status'   => 'succeeded',
			),
			(object) array(
				'amount'   => 2000,
				'currency' => 'usd',
				'created'  => strtotime( '2024-03-05' ),
				'paid'     => true,
				'refunded' => false,
				'status'   => 'succeeded',
			),
			(object) array(
				'amount'   => 500,
				'currency' => 'usd',
				'created'  => strtotime( '2024-03-10' ),
				'paid'     => false,
				'refunded' => false,
				'status'   => 'failed',
			),
		);

		Stub::$subs = array(
			(object) array(
				'created' => strtotime( '2024-02-05' ),
				'status'  => 'active',
			),
			(object) array(
				'created' => strtotime( '2024-02-20' ),
				'status'  => 'canceled',
			),
			(object) array(
				'created' => strtotime( '2024-03-01' ),
				'status'  => 'active',
			),
			(object) array(
				'created' => strtotime( '2024-03-10' ),
				'status'  => 'active',
			),
		);

		Stub::$options['artpulse_settings'] = array(
			'stripe_secret'         => 'sk_test',
			'payment_metrics_cache' => 15,
		);

		$metrics = PaymentAnalyticsDashboard::get_metrics( '2024-01-01', '2024-03-31' );

		$expected = array(
			'woo_total_revenue'           => 70.0,
			'stripe_total_revenue'        => 35.0,
			'active_subscriptions'        => 3,
			'churn_rate'                  => 25.0,
			'monthly_revenue_growth'      => 200.0,
			'monthly_subscription_growth' => 0.0,
			'payment_success_rate'        => 62.5,
			'payment_failure_rate'        => 37.5,
			'months'                      => array(
				date( 'M Y', strtotime( '2024-01-01' ) ),
				date( 'M Y', strtotime( '2024-02-01' ) ),
				date( 'M Y', strtotime( '2024-03-01' ) ),
			),
			'revenue_trend'               => array( 40.0, 25.0, 75.0 ),
			'subscription_trend'          => array( 0, 2, 2 ),
		);

		$this->assertSame( $expected, $metrics );
		$cache_key = 'ap_payment_metrics_' . md5( '2024-01-01_2024-03-31' );
		$this->assertArrayHasKey( $cache_key, Stub::$transients );
		$this->assertSame( 1, Stub::$wc_calls );
		$this->assertSame( 1, Stub::$stripe_charge_calls );
		$this->assertSame( 1, Stub::$stripe_subs_calls );
	}
}
