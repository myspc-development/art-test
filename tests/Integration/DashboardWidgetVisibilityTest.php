<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Admin\UserLayoutManager;
use ArtPulse\Core\DashboardWidgetRegistry;

/**

 * @group integration

 */

class DashboardWidgetVisibilityTest extends \WP_UnitTestCase {
	public function set_up() {
		parent::set_up();
		$ref  = new \ReflectionClass( DashboardWidgetRegistry::class );
		$prop = $ref->getProperty( 'widgets' );
		$prop->setAccessible( true );
		$prop->setValue( null, array() );
		delete_option( 'ap_widget_group_visibility' );
	}

       public function test_can_view_respected_in_render(): void {
               DashboardWidgetRegistry::register_widget(
                       'widget_yes',
                       array(
                               'label'    => 'Yes',
                               'callback' => array( TestWidgetYes::class, 'render' ),
                               'roles'    => array( 'member' ),
                       )
               );
               DashboardWidgetRegistry::register_widget(
                       'widget_no',
                       array(
                               'label'    => 'No',
                               'callback' => array( TestWidgetNo::class, 'render' ),
                               'roles'    => array( 'member' ),
                       )
               );

               UserLayoutManager::save_role_layout(
                       'member',
                       array(
                               array( 'id' => 'widget_yes' ),
                               array( 'id' => 'widget_no' ),
                       )
               );

               $uid = self::factory()->user->create( array( 'role' => 'member' ) );
               wp_set_current_user( $uid );
               ob_start();
               DashboardWidgetRegistry::render_for_role( $uid );
               $html = ob_get_clean();

               $this->assertStringContainsString( 'data-slug="widget_yes"', $html );
               $this->assertStringNotContainsString( 'data-slug="widget_no"', $html );
       }

       public function test_group_visibility_option_hides_widgets(): void {
               DashboardWidgetRegistry::register_widget(
                       'widget_yes',
                       array(
                               'label'    => 'Grouped',
                               'callback' => array( TestWidgetYes::class, 'render' ),
                               'roles'    => array( 'member' ),
                               'group'    => 'beta',
                       )
               );
               UserLayoutManager::save_role_layout( 'member', array( array( 'id' => 'widget_yes' ) ) );
               update_option( 'ap_widget_group_visibility', array( 'beta' => false ) );

               $uid = self::factory()->user->create( array( 'role' => 'member' ) );
               wp_set_current_user( $uid );
               ob_start();
               DashboardWidgetRegistry::render_for_role( $uid );
               $html = ob_get_clean();

               $this->assertStringNotContainsString( 'data-slug="widget_yes"', $html );
               $this->assertStringNotContainsString( 'YES', $html );
       }
}

class TestWidgetYes {
	public static function can_view() {
		return true; }
	public static function render() {
		return 'YES'; }
}
class TestWidgetNo {
	public static function can_view() {
		return false; }
	public static function render() {
		return 'NO'; }
}
