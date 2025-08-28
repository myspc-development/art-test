<?php
namespace ArtPulse\Audit\Tests {

use PHPUnit\Framework\TestCase;
use ArtPulse\Cli\WidgetAudit;
use ArtPulse\Core\DashboardWidgetRegistry;

require_once __DIR__ . '/../TestStubs.php';

class WidgetSourcesVisibilityTest extends TestCase
{
    public static array $rows = [];

    protected function setUp(): void
    {
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        foreach (["widgets","builder_widgets","id_map","issues","logged_duplicates","aliases"] as $prop) {
            if ($ref->hasProperty($prop)) {
                $p = $ref->getProperty($prop);
                $p->setAccessible(true);
                $p->setValue(null, []);
            }
        }
        update_option('artpulse_widget_roles', []);
        update_option('artpulse_hidden_widgets', []);
    }

    public function test_roles_from_visibility_option(): void
    {
        DashboardWidgetRegistry::register('widget_demo', 'Demo', '', '', static fn() => '');
        update_option('artpulse_widget_roles', ['member' => ['widget_demo']]);

        $cmd = new WidgetAudit();
        $cmd->widgets([], ['format' => 'table']);

        $this->assertNotEmpty(self::$rows);
        $this->assertSame('member', self::$rows[0]['roles_from_visibility']);
    }
}
}

namespace WP_CLI\Utils {
    function format_items($format, $rows, $fields) {
        \ArtPulse\Audit\Tests\WidgetSourcesVisibilityTest::$rows = $rows;
    }
}

namespace {
    if (!class_exists('WP_CLI')) {
        class WP_CLI {
            public static function line($msg): void {}
            public static function error($msg): void { throw new \Exception($msg); }
        }
    }
}
