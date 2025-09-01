<?php
namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardWidgetRegistry;

if ( ! defined( 'ABSPATH' ) ) {
        define( 'ABSPATH', __DIR__ . '/' );
}

require_once __DIR__ . '/../TestStubs.php';
require_once __DIR__ . '/../../src/Dashboard/WidgetVisibility.php';
require_once __DIR__ . '/../../src/Core/DashboardWidgetRegistry.php';

/**

 * @group core

 */

class DashboardWidgetRegistryInitTest extends TestCase {
        /**
         * @runInSeparateProcess
         */
        public function test_init_succeeds_without_loader(): void {
                $root   = dirname( __DIR__, 2 );
                $loader = $root . '/includes/widget-loader.php';
                $backup = $loader . '.bak';

                rename( $loader, $backup );
                try {
                        DashboardWidgetRegistry::init();
                        $this->assertTrue( DashboardWidgetRegistry::exists( 'widget_news' ) );
                } finally {
                        rename( $backup, $loader );
                }
        }
}
