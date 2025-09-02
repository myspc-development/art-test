<?php
namespace ArtPulse\Reporting\Tests;

require_once __DIR__ . '/../TestHelpers.php';

use ArtPulse\Reporting\BudgetExportController;
use ArtPulse\Admin\Tests\Stub;

/**
 * @group REPORTING
 */
class BudgetExportControllerTest extends \WP_UnitTestCase {

	private int $event_id;

	public function set_up() {
		parent::set_up();
		Stub::reset();

		$this->event_id = self::factory()->post->create(
			array(
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
			)
		);

		update_post_meta(
			$this->event_id,
			'ap_budget_lines',
			array(
				array(
					'estimated' => 100,
					'actual'    => 90,
				),
				array(
					'estimated' => 50,
					'actual'    => 60,
				),
			)
		);

		if ( ! class_exists( 'Dompdf\\Dompdf' ) ) {
			eval( 'namespace Dompdf; class Dompdf { public function loadHtml($h){} public function setPaper($p){} public function render(){} public function output(){ return "PDF"; } }' );
		}

		BudgetExportController::register();
		do_action( 'rest_api_init' );
	}

	public function test_export_pdf_returns_pdf_response(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/budget/export' );
		$req->set_param( 'event_id', $this->event_id );
		$req->set_param( 'format', 'pdf' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$this->assertSame( 'application/pdf', $res->get_headers()['Content-Type'] );
	}

	public function test_export_csv_returns_csv_response(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/budget/export' );
		$req->set_param( 'event_id', $this->event_id );
		$req->set_param( 'format', 'csv' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$this->assertSame( 'text/csv', $res->get_headers()['Content-Type'] );
	}

	public function test_merge_across_events_sums_totals(): void {
		$id2 = self::factory()->post->create(
			array(
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
			)
		);
		update_post_meta(
			$id2,
			'ap_budget_lines',
			array(
				array(
					'estimated' => 200,
					'actual'    => 200,
				),
			)
		);

		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/budget/export' );
		$req->set_param( 'event_ids', $this->event_id . ',' . $id2 );
		$req->set_param( 'format', 'csv' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$csv = $res->get_data();
		$this->assertStringContainsString( 'Estimated Total', $csv );
		$this->assertStringContainsString( '350', $csv );
	}
}
