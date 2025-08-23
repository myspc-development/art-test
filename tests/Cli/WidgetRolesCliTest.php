<?php

namespace {
    require_once __DIR__ . '/WP_CLI_Stub.php';
    if (!defined('ABSPATH')) { define('ABSPATH', __DIR__); }
    $GLOBALS['options'] = [];
    function get_option($name, $default = false) { return $GLOBALS['options'][$name] ?? $default; }
    function update_option($name, $value) { $GLOBALS['options'][$name] = $value; }
}

namespace ArtPulse\Core {
    class DashboardWidgetRegistry {
        public static function get_role_widget_map( array $roles = [] ): array {
            return [
                'member' => [ [ 'id' => 'w1' ] ],
                'artist' => [ [ 'id' => 'w2' ] ],
            ];
        }
    }
}

namespace ArtPulse\Cli\Tests {
    use PHPUnit\Framework\TestCase;
    use WP_CLI;

    require_once __DIR__ . '/../../includes/class-cli-widget-roles.php';

    class WidgetRolesCliTest extends TestCase {
        protected function setUp(): void {
            WP_CLI::$commands = [];
            WP_CLI::$last_output = '';
            $GLOBALS['options'] = [];
        }

        public function test_export_outputs_json(): void {
            WP_CLI::add_command('widget-roles', \AP_CLI_Widget_Roles::class);
            $out = WP_CLI::runcommand('widget-roles export');
            $this->assertJson($out);
            $data = json_decode($out, true);
            $this->assertArrayHasKey('member', $data);
        }

        public function test_import_missing_file_errors(): void {
            WP_CLI::add_command('widget-roles', \AP_CLI_Widget_Roles::class);
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('Missing file.');
            WP_CLI::runcommand('widget-roles import');
        }

        public function test_import_file_not_found_errors(): void {
            WP_CLI::add_command('widget-roles', \AP_CLI_Widget_Roles::class);
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('File not found.');
            WP_CLI::runcommand('widget-roles --import=missing.json');
        }

        public function test_import_invalid_json_errors(): void {
            WP_CLI::add_command('widget-roles', \AP_CLI_Widget_Roles::class);
            $tmp = tempnam(sys_get_temp_dir(), 'wr');
            file_put_contents($tmp, '{invalid');
            try {
                $this->expectException(\RuntimeException::class);
                $this->expectExceptionMessage('Invalid JSON.');
                WP_CLI::runcommand('widget-roles import ' . $tmp);
            } finally {
                @unlink($tmp);
            }
        }

        public function test_import_success_updates_option(): void {
            WP_CLI::add_command('widget-roles', \AP_CLI_Widget_Roles::class);
            $tmp = tempnam(sys_get_temp_dir(), 'wr');
            $data = ['member' => [ ['id' => 'w1'] ]];
            file_put_contents($tmp, json_encode($data));
            $out = WP_CLI::runcommand('widget-roles --import=' . $tmp);
            $this->assertStringContainsString('Imported widget-role map.', $out);
            $this->assertSame($data, $GLOBALS['options']['artpulse_widget_roles']);
            @unlink($tmp);
        }
    }
}
