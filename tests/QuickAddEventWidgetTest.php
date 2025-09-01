<?php
use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use function Brain\Monkey\Functions\when;
use function Brain\Monkey\Functions\stubs;

require_once __DIR__ . '/quick-add-bootstrap.php';

/**

 * @group WIDGETS

 */

class QuickAddEventWidgetTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		when( 'current_user_can' )->justReturn( true );
		when( 'get_current_user_id' )->justReturn( 1 );
		when( 'get_user_meta' )->justReturn( 123 );
		when( 'wp_nonce_field' )->justReturn( '' );
		when( 'admin_url' )->justReturn( '/wp-admin/admin-ajax.php' );
		when( 'rest_url' )->justReturn( '/wp-json/' );
		when( 'wp_create_nonce' )->justReturn( 'nonce' );
		when( 'esc_url' )->alias( fn( $v ) => $v );
		when( 'esc_html__' )->alias( fn( $v ) => $v );
		when( 'esc_html_e' )->alias( fn( $v ) => $v );
		when( 'esc_attr_e' )->alias( fn( $v ) => $v );
		when( 'esc_attr' )->alias( fn( $v ) => $v );
		when( 'ArtPulse\Widgets\esc_attr' )->alias( fn( $v ) => $v );
		when( 'ArtPulse\Core\get_option' )->justReturn( array() );
		when( 'ArtPulse\Core\get_userdata' )->justReturn( (object) array( 'roles' => array( 'organization' ) ) );
		when( 'sanitize_key' )->alias( fn( $v ) => $v );
		when( 'apply_filters' )->alias(
			function ( $tag, $value ) {
				return $value;
			}
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_render_outputs_required_ids() {
		$html = ArtPulse\Widgets\OrgQuickAddEventWidget::render();
		$this->assertStringContainsString( 'id="ap-add-event-btn"', $html );
		$this->assertStringContainsString( 'id="ap-org-modal"', $html );
		$this->assertStringContainsString( 'id="ap-org-event-form"', $html );
		$this->assertStringContainsString( 'data-ajax-url', $html );
		$this->assertStringContainsString( 'data-action="ap_add_org_event"', $html );
	}
}
