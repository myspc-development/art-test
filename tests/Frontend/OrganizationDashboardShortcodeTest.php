<?php
namespace ArtPulse\Frontend;

if ( ! function_exists( __NAMESPACE__ . '\\user_can' ) ) {
	function user_can( $user_id, $cap ) {
			return true;
	}
}

if ( ! function_exists( __NAMESPACE__ . '\\ap_get_ui_mode' ) ) {
	function ap_get_ui_mode() {
			return 'php';
	}
}

if ( ! function_exists( __NAMESPACE__ . '\\apply_filters' ) ) {
	function apply_filters( $hook, $value ) {
			return $value;
	}
}

require_once __DIR__ . '/../TestHelpers/FrontendFunctionStubs.php';

namespace ArtPulse\Frontend\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Frontend\OrganizationDashboardShortcode;

/**

 * @group FRONTEND
 */

class OrganizationDashboardShortcodeTest extends TestCase {

	protected function setUp(): void {
			\ArtPulse\Frontend\StubState::reset();
			\ArtPulse\Frontend\StubState::$current_user                      = 1;
			$GLOBALS['__ap_test_user_meta']                                  = array();
			\ArtPulse\Frontend\StubState::$current_user_caps                 = array();
			\ArtPulse\Frontend\StubState::$shortcodes['[ap_user_dashboard]'] = '<div class="ap-dashboard-grid"></div>';
	}

	public function test_dashboard_renders_grid(): void {
			$GLOBALS['__ap_test_user_meta'][1]['ap_organization_id'] = 10;
			$this->setOutputCallback( static fn() => '' );
			ob_start();
			$html   = OrganizationDashboardShortcode::render( array() );
			$output = ob_get_clean();
			$this->assertSame( '', $output, 'Unexpected output buffer' );
			$this->assertStringContainsString( 'ap-dashboard-grid', $html );
			$this->assertStringNotContainsString( 'Access denied', $html );
	}

	public function test_analytics_hidden_without_cap(): void {
			$GLOBALS['__ap_test_user_meta'][1]['ap_organization_id']           = 10;
			\ArtPulse\Frontend\StubState::$current_user_caps['view_analytics'] = false;

			\ArtPulse\Frontend\StubState::$shortcodes['[ap_user_dashboard]'] = '<div class="ap-dashboard-grid"></div>';
			$this->setOutputCallback( static fn() => '' );
			ob_start();
			$html   = OrganizationDashboardShortcode::render( array() );
			$output = ob_get_clean();
			$this->assertSame( '', $output, 'Unexpected output buffer' );
			$this->assertStringNotContainsString( 'Organization Analytics', $html );
	}

	public function test_analytics_visible_with_cap(): void {
			$GLOBALS['__ap_test_user_meta'][1]['ap_organization_id']           = 10;
			\ArtPulse\Frontend\StubState::$current_user_caps['view_analytics'] = true;

			\ArtPulse\Frontend\StubState::$shortcodes['[ap_user_dashboard]'] = '<div class="ap-dashboard-grid"><div>Organization Analytics</div></div>';
			$this->setOutputCallback( static fn() => '' );
			ob_start();
			$html   = OrganizationDashboardShortcode::render( array() );
			$output = ob_get_clean();
			$this->assertSame( '', $output, 'Unexpected output buffer' );
			$this->assertStringContainsString( 'Organization Analytics', $html );
	}
}
