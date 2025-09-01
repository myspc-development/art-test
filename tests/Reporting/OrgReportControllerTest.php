<?php
namespace ArtPulse\Reporting\Tests;

use ArtPulse\Reporting\OrgReportController;

/**
 * @group REPORTING
 */
class OrgReportControllerTest extends \WP_UnitTestCase {

	private int $org_id;

	public function set_up() {
		parent::set_up();
		$this->org_id = self::factory()->post->create(
			array(
				'post_type'   => 'artpulse_org',
				'post_status' => 'publish',
			)
		);
		if ( ! class_exists( 'Dompdf\\Dompdf' ) ) {
			eval( 'namespace Dompdf; class Dompdf { public function loadHtml($h){} public function setPaper($p){} public function render(){} public function output(){ return "PDF"; } }' );
		}
		OrgReportController::register();
		do_action( 'rest_api_init' );
	}

	public function test_download_csv_returns_csv(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/orgs/' . $this->org_id . '/report' );
		$req->set_param( 'type', 'engagement' );
		$req->set_param( 'format', 'csv' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$this->assertSame( 'text/csv', $res->get_headers()['Content-Type'] );
	}

	public function test_download_pdf_returns_pdf(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/orgs/' . $this->org_id . '/report' );
		$req->set_param( 'type', 'engagement' );
		$req->set_param( 'format', 'pdf' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$this->assertSame( 'application/pdf', $res->get_headers()['Content-Type'] );
	}
}
