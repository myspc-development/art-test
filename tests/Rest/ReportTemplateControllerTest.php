<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\ReportTemplateController;

/**
 * @group REST
 */
class ReportTemplateControllerTest extends \WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		ReportTemplateController::register();
		do_action( 'rest_api_init' );
	}

	public function test_save_and_get_template(): void {
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/report-template/budget' );
		$req->set_body_params( array() ); // ensure body parsed
		$req->set_header( 'Content-Type', 'application/json' );
		$req->set_body(
			json_encode(
				array(
					'template' => array(
						'rows' => array(
							array(
								'item'      => 'Venue',
								'estimated' => 100,
							),
						),
					),
				)
			)
		);
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );

		$get  = new \WP_REST_Request( 'GET', '/artpulse/v1/report-template/budget' );
		$res2 = rest_get_server()->dispatch( $get );
		$data = $res2->get_data();
		$this->assertIsArray( $data['rows'] );
		$this->assertSame( 'Venue', $data['rows'][0]['item'] );
	}
}
