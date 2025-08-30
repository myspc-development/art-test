<?php
namespace {
    if ( ! defined( 'ABSPATH' ) ) {
        define( 'ABSPATH', __DIR__ . '/' );
    }
    if ( ! function_exists( 'get_role' ) ) {
        function get_role( $role ) {
            $caps = $GLOBALS['test_roles'][ $role ] ?? array();
            return new class( $caps ) {
                private array $caps;
                public function __construct( array $caps ) { $this->caps = $caps; }
                public function has_cap( $cap ) { return ! empty( $this->caps[ $cap ] ); }
            };
        }
    }
}

namespace ArtPulse\Core\Tests {
    use PHPUnit\Framework\TestCase;
    use ArtPulse\Core\WidgetAccessValidator;
    use ReflectionClass;

    /**
     * @runInSeparateProcess
     */
    class WidgetAccessValidatorTest extends TestCase {
        protected function setUp(): void {
            $GLOBALS['test_roles'] = array();
            $this->setWidgets( array() );
        }

        protected function tearDown(): void {
            $GLOBALS['test_roles'] = array();
        }

        private function setWidgets( array $widgets ): void {
            $ref  = new ReflectionClass( \ArtPulse\Core\DashboardWidgetRegistry::class );
            $prop = $ref->getProperty( 'widgets' );
            $prop->setAccessible( true );
            $prop->setValue( null, $widgets );
        }

        public function test_unregistered_widget(): void {
            $result = WidgetAccessValidator::validate( 'missing', 'member' );
            $this->assertFalse( $result['allowed'] );
            $this->assertSame( 'unregistered', $result['reason'] );
        }

        public function test_role_mismatch(): void {
            $this->setWidgets( array( 'widget_foo' => array( 'roles' => array( 'admin' ) ) ) );
            $result = WidgetAccessValidator::validate( 'foo', 'member' );
            $this->assertFalse( $result['allowed'] );
            $this->assertSame( 'role_mismatch', $result['reason'] );
        }

        public function test_missing_capability(): void {
            $GLOBALS['test_roles']['member'] = array( 'read' => true );
            $this->setWidgets(
                array( 'widget_bar' => array( 'roles' => array( 'member' ), 'capability' => 'manage_options' ) )
            );
            $result = WidgetAccessValidator::validate( 'bar', 'member' );
            $this->assertFalse( $result['allowed'] );
            $this->assertSame( 'missing_capability', $result['reason'] );
            $this->assertSame( 'manage_options', $result['cap'] );
        }
    }
}
