<?php
namespace ArtPulse\Frontend;

require_once __DIR__ . '/../TestHelpers/FrontendFunctionStubs.php';

if ( ! function_exists( __NAMESPACE__ . '\get_terms' ) ) {
	function get_terms( $tax, $args ) {
		return array(); }
}
if ( ! function_exists( __NAMESPACE__ . '\current_user_can' ) ) {
	function current_user_can( $cap ) {
		return \ArtPulse\Frontend\Tests\OrganizationDashboardShortcodeTest::$caps[ $cap ] ?? false; }
}

namespace ArtPulse\Frontend\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Frontend\OrganizationDashboardShortcode;

/**

 * @group FRONTEND

 */

class OrganizationDashboardShortcodeTest extends TestCase {
        public static array $post_meta = array();
        public static array $caps      = array();

        protected function setUp(): void {
                $GLOBALS['__ap_test_user_meta'] = array();
                self::$post_meta                = array();
                self::$caps                     = array();
        }

	public function test_dashboard_renders_grid(): void {
                $GLOBALS['__ap_test_user_meta'][1]['ap_organization_id'] = 10;
                $html = OrganizationDashboardShortcode::render( array() );
		$this->assertStringContainsString( 'ap-dashboard-grid', $html );
	}

	public function test_analytics_hidden_without_cap(): void {
                $GLOBALS['__ap_test_user_meta'][1]['ap_organization_id'] = 10;
                self::$caps['view_analytics']             = false;

		$html = OrganizationDashboardShortcode::render( array() );
		$this->assertStringNotContainsString( 'Organization Analytics', $html );
	}

	public function test_analytics_visible_with_cap(): void {
                $GLOBALS['__ap_test_user_meta'][1]['ap_organization_id'] = 10;
                self::$caps['view_analytics']             = true;

		$html = OrganizationDashboardShortcode::render( array() );
		$this->assertStringContainsString( 'Organization Analytics', $html );
	}
}
