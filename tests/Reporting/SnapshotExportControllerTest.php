<?php
namespace ArtPulse\Reporting\Tests;

use ArtPulse\Reporting\SnapshotExportController;
use ArtPulse\Core\VisitTracker;

/**
 * @group REPORTING
 */
class SnapshotExportControllerTest extends \WP_UnitTestCase {

	private int $org_id;
	private int $event_id;
	private int $tier_id;

	public function set_up() {
		parent::set_up();
		\ArtPulse\DB\create_monetization_tables();
		do_action( 'init' );
		$this->org_id   = self::factory()->post->create(
			array(
				'post_type'   => 'artpulse_org',
				'post_status' => 'publish',
			)
		);
		$this->event_id = self::factory()->post->create(
			array(
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $this->event_id, '_ap_event_organization', $this->org_id );
		update_post_meta( $this->event_id, 'event_rsvp_list', array( 1, 2 ) );

		VisitTracker::install_table();
		global $wpdb;
		$table = $wpdb->prefix . 'ap_event_checkins';
		$wpdb->insert(
			$table,
			array(
				'event_id'    => $this->event_id,
				'user_id'     => 0,
				'institution' => '',
				'group_size'  => 5,
				'visit_date'  => current_time( 'mysql' ),
			)
		);

		$table = $wpdb->prefix . 'ap_event_tickets';
		$wpdb->insert(
			$table,
			array(
				'event_id'  => $this->event_id,
				'name'      => 'General',
				'price'     => 10,
				'inventory' => 0,
			)
		);
		$this->tier_id = $wpdb->insert_id;
		$table         = $wpdb->prefix . 'ap_tickets';
		$wpdb->insert(
			$table,
			array(
				'user_id'        => 1,
				'event_id'       => $this->event_id,
				'ticket_tier_id' => $this->tier_id,
				'code'           => 'ABC',
				'status'         => 'active',
				'purchase_date'  => current_time( 'mysql' ),
			)
		);

		if ( ! class_exists( 'Dompdf\Dompdf' ) ) {
			eval( 'namespace Dompdf; class Dompdf { public function loadHtml($h){} public function setPaper($p){} public function render(){} public function output(){ return "PDF"; } }' );
		}

		SnapshotExportController::register();
		do_action( 'rest_api_init' );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
	}

	public function test_summary_endpoint_returns_metrics(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/reporting/snapshot' );
		$req->set_param( 'org_id', $this->org_id );
		$req->set_param( 'period', date( 'Y-m' ) );
		$res  = rest_get_server()->dispatch( $req );
		$data = $res->get_data();
		$this->assertSame( 200, $res->get_status() );
		$this->assertSame( 1, $data['Total Events'] );
		$this->assertSame( 2, $data['RSVPs'] );
		$this->assertSame( 5, $data['Check-Ins'] );
		$this->assertSame( 10.0, $data['Revenue'] );
	}

	public function test_csv_endpoint_returns_csv(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/reporting/snapshot.csv' );
		$req->set_param( 'org_id', $this->org_id );
		$req->set_param( 'period', date( 'Y-m' ) );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$this->assertSame( 'text/csv', $res->get_headers()['Content-Type'] );
	}

	public function test_pdf_endpoint_returns_pdf(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/reporting/snapshot.pdf' );
		$req->set_param( 'org_id', $this->org_id );
		$req->set_param( 'period', date( 'Y-m' ) );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$this->assertSame( 'application/pdf', $res->get_headers()['Content-Type'] );
	}
}
