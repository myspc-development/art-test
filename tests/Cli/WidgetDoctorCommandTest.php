<?php
namespace {
    class WP_CLI {
        public static array $commands = [];
        public static string $last_output = '';

        public static function add_command( string $name, $callable ): void {
            self::$commands[ $name ] = $callable;
        }

        public static function runcommand( string $command ): string {
            $parts       = preg_split( '/\s+/', trim( $command ) );
            $cmd_tokens  = [];
            $assoc       = [];
            foreach ( $parts as $p ) {
                if ( str_starts_with( $p, '--' ) ) {
                    $p = substr( $p, 2 );
                    $kv = explode( '=', $p, 2 );
                    $assoc[ $kv[0] ] = $kv[1] ?? true;
                } else {
                    $cmd_tokens[] = $p;
                }
            }
            $root = $cmd_tokens[0] . ' ' . ($cmd_tokens[1] ?? '');
            $sub  = $cmd_tokens[2] ?? '';
            $handler = self::$commands[ $root ] ?? null;
            if ( is_string( $handler ) ) {
                $obj = new $handler();
            } elseif ( is_object( $handler ) ) {
                $obj = $handler;
            } else {
                throw new \RuntimeException( 'Command not registered.' );
            }
            $method = $sub;
            if ( ! method_exists( $obj, $method ) && method_exists( $obj, $sub . '_' ) ) {
                $method = $sub . '_';
            }
            ob_start();
            try {
                $obj->$method( [], $assoc );
            } catch ( \RuntimeException $e ) {
                self::$last_output = ob_get_clean();
                throw $e;
            }
            self::$last_output = ob_get_clean();
            return self::$last_output;
        }

        public static function success( string $msg ): void {
            echo $msg . "\n";
        }

        public static function error( string $msg ): void {
            throw new \RuntimeException( $msg );
        }
    }
}

namespace WP_CLI\Utils {
    function format_items( $type, $items, $fields ): void {
        echo implode( "\t", $fields ) . "\n";
        foreach ( $items as $row ) {
            $out = [];
            foreach ( $fields as $f ) {
                $out[] = $row[ $f ] ?? '';
            }
            echo implode( "\t", $out ) . "\n";
        }
    }
}

namespace {
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
            $GLOBALS['hidden_widgets']['member'] = [ 'widget_two' ];
            $out = WP_CLI::runcommand( 'artpulse widgets list' );
            $this->assertStringContainsString( "id\tstatus\troles\thas_callback\thidden_for_roles", $out );
            $this->assertStringContainsString( "widget_two\tbeta\tartist\tyes\tmember", $out );
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
