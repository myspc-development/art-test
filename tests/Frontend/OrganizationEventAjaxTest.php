<?php
namespace ArtPulse\Frontend;

require_once __DIR__ . '/../TestHelpers/FrontendFunctionStubs.php';

namespace ArtPulse\Frontend\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Frontend\OrganizationDashboardShortcode;

/**

 * @group FRONTEND

 */

class OrganizationEventAjaxTest extends TestCase {

        protected function setUp(): void {
                \ArtPulse\Frontend\StubState::reset();
                \ArtPulse\Frontend\StubState::$current_user = 1;
                $_POST              = array();
                $_FILES             = array();
                $GLOBALS['__ap_test_user_meta'] = array();
        }

        protected function tearDown(): void {
                $_POST               = array();
                $_FILES              = array();
                $GLOBALS['__ap_test_user_meta'] = array();
                \ArtPulse\Frontend\StubState::reset();
                parent::tearDown();
        }

	public function test_update_event_returns_html(): void {
               \ArtPulse\Frontend\StubState::$get_posts_return = array(
                       (object) array(
                               'ID'         => 7,
                               'post_title' => 'First',
                       ),
                       (object) array(
                               'ID'         => 8,
                               'post_title' => 'Second',
                       ),
               );

               // Ensure the event has an associated organization and that user meta differs
               \ArtPulse\Frontend\StubState::$post_meta[7]['_ap_event_organization'] = 12;
               $GLOBALS['__ap_test_user_meta'][1]['ap_organization_id']           = 99;

		$addr = array(
			'country' => 'US',
			'state'   => 'CA',
			'city'    => 'LA',
		);

		$_POST = array(
			'nonce'                    => 'n',
			'ap_event_id'              => 7,
			'ap_event_title'           => 'First',
			'ap_event_date'            => '2024-01-01',
			'ap_event_start_date'      => '',
			'ap_event_end_date'        => '',
			'ap_event_location'        => '',
			'ap_venue_name'            => '',
			'ap_event_street_address'  => '',
			'ap_event_country'         => '',
			'ap_event_state'           => '',
			'ap_event_city'            => '',
			'ap_event_postcode'        => '',
			'address_components'       => json_encode( $addr ),
			'ap_event_organizer_name'  => '',
			'ap_event_organizer_email' => '',
		);

		OrganizationDashboardShortcode::handle_ajax_update_event();

               $this->assertSame( 7, \ArtPulse\Frontend\StubState::$wp_update_post_args['ID'] ?? null );
               $this->assertSame( 12, \ArtPulse\Frontend\StubState::$get_posts_args['meta_value'] ?? null );
                $html = \ArtPulse\Frontend\StubState::$json['updated_list_html'] ?? '';
		$this->assertStringContainsString( 'First', $html );
		$this->assertStringContainsString( 'Second', $html );

		$expected_meta = array( 7, 'address_components', json_encode( $addr ) );
		$this->assertContains( $expected_meta, \ArtPulse\Frontend\StubState::$meta_log );
	}

        public function test_add_event_returns_error_when_upload_fails(): void {
                \ArtPulse\Frontend\StubState::$media_default = new \WP_Error( 'upload_error', 'Upload failed' );
                $_FILES             = array( 'event_banner' => array( 'tmp_name' => 'tmp' ) );

                $_POST = array(
                        'nonce'                 => 'n',
                        'ap_event_title'        => 'Event',
                        'ap_event_date'         => '2024-01-01',
                        'ap_event_location'     => '',
                        'ap_event_organization' => 1,
                );

                $GLOBALS['__ap_test_user_meta'][1]['ap_organization_id'] = 1;

                OrganizationDashboardShortcode::handle_ajax_add_event();

                $this->assertSame( 'Upload failed', \ArtPulse\Frontend\StubState::$json_error['message'] ?? null );
        }
}
