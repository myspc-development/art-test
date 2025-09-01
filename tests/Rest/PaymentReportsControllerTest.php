<?php
require_once __DIR__ . '/../TestHelpers.php';

namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\PaymentReportsController;
use ArtPulse\Admin\PaymentAnalyticsDashboard;
use ArtPulse\Admin\Tests\Stub;


/**
 * @group restapi
 */
class PaymentReportsControllerTest extends \WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		Stub::reset();
		Stub::$orders                       = array(
			new \ArtPulse\Admin\Tests\WC_Order( strtotime( '2024-02-15' ), 10, 'completed' ),
		);
		Stub::$charges                      = array(
			(object) array(
				'amount'   => 1500,
				'currency' => 'usd',
				'created'  => strtotime( '2024-02-10' ),
				'paid'     => true,
				'refunded' => false,
				'status'   => 'succeeded',
			),
		);
		Stub::$subs                         = array(
			(object) array(
				'created' => strtotime( '2024-02-05' ),
				'status'  => 'active',
			),
		);
		Stub::$options['artpulse_settings'] = array(
			'stripe_secret'         => 'sk_test',
			'payment_metrics_cache' => 15,
		);

		PaymentReportsController::register();
		do_action( 'rest_api_init' );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
	}

	public function test_reports_endpoint_returns_metrics(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/payment-reports' );
		$req->set_param( 'start_date', '2024-02-01' );
		$req->set_param( 'end_date', '2024-02-29' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertArrayHasKey( 'metrics', $data );
		$this->assertSame( 10.0, $data['metrics']['woo_total_revenue'] );
		$this->assertSame( 15.0, $data['metrics']['stripe_total_revenue'] );
	}
}
