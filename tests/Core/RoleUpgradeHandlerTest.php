<?php
namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\UserLayoutManager;
use ArtPulse\Admin\LayoutSnapshotManager;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Tests\Stubs\MockStorage;

require_once __DIR__ . '/../../includes/role-upgrade-handler.php';

/**

 * @group core

 */

class RoleUpgradeHandlerTest extends TestCase {

	protected function setUp(): void {
		MockStorage::$users     = array();
		MockStorage::$user_meta = array();
		$ref                    = new \ReflectionClass( DashboardWidgetRegistry::class );
		$prop                   = $ref->getProperty( 'widgets' );
		$prop->setAccessible( true );
		$prop->setValue( null, array() );
	}

	public function test_widgets_merge_and_layout_is_snapshotted(): void {
               DashboardWidgetRegistry::register( 'widget_alpha', 'Alpha', '', '', '__return_null' );
               DashboardWidgetRegistry::register( 'widget_beta', 'Beta', '', '', '__return_null' );
               DashboardWidgetRegistry::register( 'widget_gamma', 'Gamma', '', '', '__return_null' );

		UserLayoutManager::save_role_layout(
			'member',
			array(
                               array(
                                       'id'      => 'widget_alpha',
                                       'visible' => true,
                               ),
                               array(
                                       'id'      => 'widget_beta',
                                       'visible' => false,
                               ),
			)
		);
		UserLayoutManager::save_role_layout(
			'artist',
			array(
                               array(
                                       'id'      => 'widget_gamma',
                                       'visible' => true,
                               ),
                               array(
                                       'id'      => 'widget_beta',
                                       'visible' => true,
                               ),
			)
		);

               MockStorage::$users[1]                            = (object) array( 'roles' => array( 'member', 'artist' ) );
               MockStorage::$user_meta[1]['ap_dashboard_layout'] = array(
                       array(
                               'id'      => 'widget_alpha',
                               'visible' => true,
                       ),
                       array(
                               'id'      => 'widget_beta',
                               'visible' => false,
                       ),
               );

		ap_merge_dashboard_on_role_upgrade( 1, 'artist', array( 'member' ) );

               $expected = array(
                       array(
                               'id'      => 'widget_alpha',
                               'visible' => true,
                       ),
                       array(
                               'id'      => 'widget_beta',
                               'visible' => false,
                       ),
                       array(
                               'id'      => 'widget_gamma',
                               'visible' => true,
                       ),
               );
		$this->assertSame( $expected, MockStorage::$user_meta[1]['ap_dashboard_layout'] );

		$snaps = MockStorage::$user_meta[1][ LayoutSnapshotManager::META_KEY ] ?? array();
		$this->assertCount( 1, $snaps );
		$this->assertSame( 'member', $snaps[0]['role'] );
		$this->assertSame(
			array(
                               array(
                                       'id'      => 'widget_alpha',
                                       'visible' => true,
                               ),
                               array(
                                       'id'      => 'widget_beta',
                                       'visible' => false,
                               ),
			),
			$snaps[0]['layout']
		);
	}

	public function test_layout_defaults_when_none_saved(): void {
               DashboardWidgetRegistry::register( 'widget_a', 'A', '', '', '__return_null' );
               DashboardWidgetRegistry::register( 'widget_b', 'B', '', '', '__return_null' );

		UserLayoutManager::save_role_layout(
			'member',
			array(
                               array(
                                       'id'      => 'widget_a',
                                       'visible' => true,
                               ),
			)
		);
		UserLayoutManager::save_role_layout(
			'artist',
			array(
                               array(
                                       'id'      => 'widget_b',
                                       'visible' => true,
                               ),
			)
		);

		MockStorage::$users[2] = (object) array( 'roles' => array( 'member', 'artist' ) );

		ap_merge_dashboard_on_role_upgrade( 2, 'artist', array( 'member' ) );

               $expected = array(
                       array(
                               'id'      => 'widget_a',
                               'visible' => true,
                       ),
                       array(
                               'id'      => 'widget_b',
                               'visible' => true,
                       ),
               );
		$this->assertSame( $expected, MockStorage::$user_meta[2]['ap_dashboard_layout'] );
	}
}
