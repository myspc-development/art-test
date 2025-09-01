<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\ProfileMetricsController;
use ArtPulse\Core\ProfileMetrics;

/**
 * @group REST
 */
class ProfileMetricsControllerTest extends \WP_UnitTestCase {

	private int $user_id;

	public function set_up() {
		parent::set_up();
		ProfileMetrics::install_table();
		$this->user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $this->user_id );
		ProfileMetricsController::register();
		do_action( 'rest_api_init' );
	}

	public function test_get_metrics_returns_expected_data(): void {
		$yesterday = date( 'Y-m-d', strtotime( '-1 day' ) );
		add_filter(
			'current_time',
			function ( $time, $type ) use ( $yesterday ) {
				return $type === 'Y-m-d' ? $yesterday : $time;
			},
			10,
			2
		);
		ProfileMetrics::log_metric( $this->user_id, 'view' );
		remove_all_filters( 'current_time' );

		ProfileMetrics::log_metric( $this->user_id, 'view' );

		$req = new \WP_REST_Request( 'GET', "/artpulse/v1/profile-metrics/{$this->user_id}" );
		$req->set_param( 'metric', 'view' );
		$req->set_param( 'days', 2 );
		$res = rest_get_server()->dispatch( $req );

		$this->assertSame( 200, $res->get_status() );
		$data  = $res->get_data();
		$today = date( 'Y-m-d' );
		$this->assertSame( array( $yesterday, $today ), $data['days'] );
		$this->assertSame( array( 1, 1 ), $data['counts'] );
	}
}
