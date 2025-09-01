<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Core\DashboardPresets;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Admin\UserLayoutManager;

/**

 * @group integration

 */

class CanonicalWidgetRenderTest extends \WP_UnitTestCase {
        private const ROLES = array( 'member', 'artist', 'organization' );

        public function set_up(): void {
                parent::set_up();

                $this->resetRegistries();
                delete_option( 'ap_dashboard_widget_config' );
                DashboardPresets::resetCache();

                $admin = self::factory()->user->create( array( 'role' => 'administrator' ) );
                wp_set_current_user( $admin );

                $all = array();
                foreach ( self::ROLES as $r ) {
                        foreach ( DashboardPresets::forRole( $r ) as $slug ) {
                                $canon     = DashboardWidgetRegistry::canon_slug( $slug );
                                $all[ $canon ] = true;
                        }
                }

                foreach ( array_keys( $all ) as $slug ) {
                        DashboardWidgetRegistry::register_widget(
                                $slug,
                                array(
                                        'label'    => $slug,
                                        'callback' => static function () use ( $slug ) {
                                                echo '<section data-slug="' . $slug . '"></section>';
                                        },
                                )
                        );
                        DashboardWidgetRegistry::register(
                                $slug,
                                array(
                                        'title'           => $slug,
                                        'render_callback' => static function () use ( $slug ) {
                                                echo '<section data-slug="' . $slug . '"></section>';
                                        },
                                )
                        );
                }

                foreach ( self::ROLES as $r ) {
                        $layout = array_map(
                                static fn( $slug ) => array(
                                        'id'      => DashboardWidgetRegistry::canon_slug( $slug ),
                                        'visible' => true,
                                ),
                                DashboardPresets::forRole( $r )
                        );
                        UserLayoutManager::save_role_layout( $r, $layout );
                }
        }

        private function resetRegistries(): void {
                $ref = new \ReflectionClass( DashboardWidgetRegistry::class );
                foreach ( array( 'widgets', 'builder_widgets' ) as $prop ) {
                        $p = $ref->getProperty( $prop );
                        $p->setAccessible( true );
                        $p->setValue( null, array() );
                }
        }

        public static function roleProvider(): array {
                return array_map( static fn( $r ) => array( $r ), self::ROLES );
        }

        /**
         * @dataProvider roleProvider
         */
        public function test_canonical_slugs_render_for_role( string $role ): void {
                set_query_var( 'ap_role', $role );
                $template = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . 'templates/simple-dashboard.php';
                ob_start();
                include $template;
                $html = ob_get_clean();
               preg_match_all( '/data-slug="([^"]+)"/', $html, $m );
               $expected = array_map( array( DashboardWidgetRegistry::class, 'canon_slug' ), DashboardPresets::forRole( $role ) );
               $this->assertSame( $expected, array_values( array_unique( $m[1] ) ) );
        }
}
