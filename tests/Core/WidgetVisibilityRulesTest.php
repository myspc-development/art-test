<?php
namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Dashboard\WidgetVisibilityManager;
use ArtPulse\Core\DashboardWidgetRegistry;

require_once __DIR__ . '/../TestStubs.php';
require_once __DIR__ . '/../../src/Dashboard/WidgetVisibilityManager.php';
require_once __DIR__ . '/../../src/Core/DashboardWidgetRegistry.php';

class WidgetVisibilityRulesTest extends TestCase {
    protected function setUp(): void {
        \ArtPulse\Tests\Stubs\MockStorage::$options = [];
    }

    public function test_rules_include_widget_exclusions(): void {
        DashboardWidgetRegistry::init();
        $rules = WidgetVisibilityManager::get_visibility_rules();
        $this->assertArrayHasKey('widget_news', $rules);
        $this->assertContains('organization', $rules['widget_news']['exclude_roles']);
    }
}
