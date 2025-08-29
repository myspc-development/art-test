<?php
namespace ArtPulse\Core\Tests;

use ArtPulse\Admin\UserLayoutManager;
use ArtPulse\Core\DashboardWidgetRegistry;

class UserLayoutManagerMergeTest extends \WP_UnitTestCase {

        public function set_up() {
                parent::set_up();

                $ref  = new \ReflectionClass( DashboardWidgetRegistry::class );
                $prop = $ref->getProperty( 'widgets' );
                $prop->setAccessible( true );
                $prop->setValue( null, array() );

                if ( ! get_role( 'member' ) ) {
                        add_role( 'member', 'Member' );
                }
                if ( ! get_role( 'artist' ) ) {
                        add_role( 'artist', 'Artist' );
                }

                DashboardWidgetRegistry::register( 'alpha', 'Alpha', '', '', '__return_null', array( 'roles' => array( 'member' ) ) );
                DashboardWidgetRegistry::register( 'beta', 'Beta', '', '', '__return_null', array( 'roles' => array( 'artist' ) ) );
                DashboardWidgetRegistry::register( 'shared', 'Shared', '', '', '__return_null', array( 'roles' => array( 'member', 'artist' ) ) );

                UserLayoutManager::save_role_layout(
                        'member',
                        array(
                                array(
                                        'id'      => 'alpha',
                                        'visible' => true,
                                ),
                                array(
                                        'id'      => 'shared',
                                        'visible' => true,
                                ),
                        )
                );
                UserLayoutManager::save_role_layout(
                        'artist',
                        array(
                                array(
                                        'id'      => 'beta',
                                        'visible' => false,
                                ),
                                array(
                                        'id'      => 'shared',
                                        'visible' => true,
                                ),
                        )
                );
        }

        public function test_merges_layouts_from_multiple_roles(): void {
                remove_action( 'add_user_role', 'ap_merge_dashboard_on_role_upgrade', 10 );
                remove_action( 'set_user_role', 'ap_merge_dashboard_on_role_upgrade', 10 );

                $user_id = self::factory()->user->create( array( 'role' => 'member' ) );
                $user    = get_user_by( 'id', $user_id );
                $user->add_role( 'artist' );

                $layout = UserLayoutManager::get_layout_for_user( $user_id );

                $expected = array(
                        array(
                                'id'      => 'alpha',
                                'visible' => true,
                        ),
                        array(
                                'id'      => 'shared',
                                'visible' => true,
                        ),
                        array(
                                'id'      => 'beta',
                                'visible' => false,
                        ),
                );

                $this->assertSame( $expected, $layout );
        }
}

