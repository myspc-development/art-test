<?php
namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Dashboard\WidgetVisibilityManager;
use ArtPulse\Core\DashboardWidgetRegistry;

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once __DIR__ . '/../TestStubs.php';
require_once __DIR__ . '/../../includes/widget-loader.php';
require_once __DIR__ . '/../../src/Dashboard/WidgetVisibilityManager.php';
require_once __DIR__ . '/../../src/Core/DashboardWidgetRegistry.php';

/**

 * @group CORE

 */

class WidgetVisibilityRulesTest extends TestCase {
	protected function setUp(): void {
		\ArtPulse\Tests\Stubs\MockStorage::$options = array();
	}

	public function test_rules_include_widget_exclusions(): void {
		DashboardWidgetRegistry::init();
		$rules = WidgetVisibilityManager::get_visibility_rules();
		$this->assertArrayHasKey( 'widget_news', $rules );
		$this->assertContains( 'organization', $rules['widget_news']['exclude_roles'] );
	}

	public function test_rules_include_allowed_roles_from_registry(): void {
		DashboardWidgetRegistry::init();
		$rules = WidgetVisibilityManager::get_visibility_rules();
		$this->assertArrayHasKey( 'widget_news', $rules );
		$this->assertSame( array( 'member' ), $rules['widget_news']['allowed_roles'] );
	}

	public function test_rules_include_allowed_roles_from_options(): void {
		\ArtPulse\Tests\Stubs\MockStorage::$options['artpulse_widget_roles'] = array(
			'custom_widget' => array(
				'allowed_roles' => array( 'member' ),
			),
		);
		$rules = WidgetVisibilityManager::get_visibility_rules();
		$this->assertSame( array( 'member' ), $rules['custom_widget']['allowed_roles'] );
	}
}
