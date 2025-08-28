<?php

namespace {
    // WP-CLI stub loaded via bootstrap
    $GLOBALS['hidden_widgets'] = [];
    $GLOBALS['options']        = [];

    function apply_filters( $hook, $value, $role ) {
        if ( 'ap_dashboard_hidden_widgets' === $hook ) {
            return $GLOBALS['hidden_widgets'][ $role ] ?? $value;
        }
        return $value;
    }

    function get_option( $name, $default = false ) {
        return $GLOBALS['options'][ $name ] ?? $default;
    }

    function update_option( $name, $value ) {
        $GLOBALS['options'][ $name ] = $value;
    }

    function __return_true() {
        return true;
    }
}

namespace ArtPulse\Core {
    class DashboardWidgetRegistry {
        private static array $widgets = [];

        public static function set( array $widgets ): void {
            self::$widgets = $widgets;
        }

        public static function all(): array {
            return self::$widgets;
        }

        public static function update_widget( string $id, array $definition ): void {
            self::$widgets[ $id ] = array_merge( self::$widgets[ $id ] ?? [], $definition );
        }

        public static function canon_slug( string $slug ): string {
            return $slug;
        }
    }
}

namespace ArtPulse\Cli\Tests {
    use PHPUnit\Framework\TestCase;
    use ArtPulse\Core\DashboardWidgetRegistry;
    use WP_CLI;

    require_once __DIR__ . '/../../src/Cli/WidgetDoctor.php';

    class WidgetDoctorCommandTest extends TestCase {
        protected function setUp(): void {
            WP_CLI::$commands    = [];
            WP_CLI::$last_output = '';
            $GLOBALS['hidden_widgets'] = [];
            $GLOBALS['options']        = [];
            DashboardWidgetRegistry::set( [] );
            WP_CLI::add_command( 'artpulse widgets', \ArtPulse\Cli\WidgetDoctor::class );
        }

        public function test_list_outputs_table(): void {
            DashboardWidgetRegistry::set( [
                'widget_one' => [ 'status' => 'active', 'roles' => [ 'member' ], 'callback' => '__return_true' ],
                'widget_two' => [ 'status' => 'beta', 'roles' => [ 'artist' ], 'callback' => '__return_true' ],
            ] );
            $out = WP_CLI::runcommand( 'artpulse widgets list' );
            $this->assertStringContainsString( "id\troles", $out );
            $this->assertStringContainsString( "widget_two\tartist", $out );
        }

        public function test_list_supports_json(): void {
            DashboardWidgetRegistry::set( [
                'widget_one' => [ 'status' => 'active', 'roles' => [ 'member' ], 'callback' => '__return_true' ],
                'widget_two' => [ 'status' => 'beta', 'roles' => [ 'artist' ], 'callback' => '__return_true' ],
            ] );
            $out = WP_CLI::runcommand( 'artpulse widgets list --format=json' );
            $decoded = json_decode( $out, true );
            $this->assertSame( 'artist', $decoded[1]['roles'] );
        }

        public function test_audit_reports_issues_and_errors(): void {
            DashboardWidgetRegistry::set( [
                'widget_ok'     => [ 'status' => 'active', 'callback' => '__return_true' ],
                'widget_bad'    => [ 'status' => 'active', 'callback' => 'missing_cb' ],
                'widget_hidden' => [ 'status' => 'coming_soon', 'callback' => '__return_true' ],
            ] );
            $GLOBALS['hidden_widgets']['artist'] = [ 'widget_hidden' ];
            try {
                WP_CLI::runcommand( 'artpulse widgets audit' );
                $this->fail( 'Expected error not thrown' );
            } catch ( \RuntimeException $e ) {
                $this->assertSame( 'Widget issues found.', $e->getMessage() );
                $out = WP_CLI::$last_output;
                $this->assertStringContainsString( 'widget_bad', $out );
                $this->assertStringContainsString( 'widget_hidden', $out );
            }
        }

        public function test_fix_unhides_and_activates(): void {
            DashboardWidgetRegistry::set( [
                'widget_fix' => [ 'status' => 'coming_soon', 'callback' => '__return_true' ],
            ] );
            $GLOBALS['options']['artpulse_dashboard_hidden_member'] = [ 'widget_fix' ];
            $GLOBALS['options']['artpulse_widget_status']          = [];
            $out = WP_CLI::runcommand( 'artpulse widgets fix --role=member --activate-all --unhide' );
            $this->assertStringContainsString( 'no placeholders will appear for member', $out );
            $this->assertSame( [], $GLOBALS['options']['artpulse_dashboard_hidden_member'] );
            $this->assertArrayHasKey( 'widget_fix', $GLOBALS['options']['artpulse_widget_status'] );
            $widgets = DashboardWidgetRegistry::all();
            $this->assertSame( 'active', $widgets['widget_fix']['status'] );
        }
    }
}
