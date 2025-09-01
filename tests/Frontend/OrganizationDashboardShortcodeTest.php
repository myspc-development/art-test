<?php
namespace ArtPulse\Frontend;

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
                $GLOBALS['__ap_test_user_meta'] = array();
                \ArtPulse\Frontend\StubState::$current_user_caps = array();
        }

	public function test_dashboard_renders_grid(): void {
                $GLOBALS['__ap_test_user_meta'][1]['ap_organization_id'] = 10;
                $html = OrganizationDashboardShortcode::render( array() );
		$this->assertStringContainsString( 'ap-dashboard-grid', $html );
	}

	public function test_analytics_hidden_without_cap(): void {
                $GLOBALS['__ap_test_user_meta'][1]['ap_organization_id'] = 10;
                \ArtPulse\Frontend\StubState::$current_user_caps['view_analytics'] = false;

		$html = OrganizationDashboardShortcode::render( array() );
		$this->assertStringNotContainsString( 'Organization Analytics', $html );
	}

	public function test_analytics_visible_with_cap(): void {
                $GLOBALS['__ap_test_user_meta'][1]['ap_organization_id'] = 10;
                \ArtPulse\Frontend\StubState::$current_user_caps['view_analytics'] = true;

		$html = OrganizationDashboardShortcode::render( array() );
		$this->assertStringContainsString( 'Organization Analytics', $html );
	}
}
