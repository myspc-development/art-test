<?php
namespace ArtPulse\Admin\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\DashboardWidgetTools;
use ArtPulse\Admin\UserLayoutManager;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\DashboardWidgetManager;

// Stub functions
if ( ! function_exists( __NAMESPACE__ . '\\artpulse_dashicon' ) ) {
	function artpulse_dashicon( $icon, $args = array() ) {
		return '<span></span>'; }
}
if ( ! function_exists( __NAMESPACE__ . '\\esc_attr' ) ) {
	function esc_attr( $str ) {
		return $str; }
}
if ( ! function_exists( __NAMESPACE__ . '\\update_option' ) ) {
	function update_option( $k, $v ) {
		PreviewStyleTest::$options[ $k ] = $v; }
}
if ( ! function_exists( __NAMESPACE__ . '\\get_option' ) ) {
	function get_option( $k, $d = array() ) {
		return PreviewStyleTest::$options[ $k ] ?? $d; }
}
if ( ! function_exists( __NAMESPACE__ . '\\sanitize_key' ) ) {
	function sanitize_key( $key ) {
		return preg_replace( '/[^a-z0-9_]/i', '', strtolower( $key ) ); }
}

/**

 * @group ADMIN
 */

class PreviewStyleTest extends TestCase {

	public static array $options = array();
	protected function setUp(): void {
		$ref  = new \ReflectionClass( DashboardWidgetRegistry::class );
		$prop = $ref->getProperty( 'widgets' );
		$prop->setAccessible( true );
		$prop->setValue( null, array() );
	}

	public function test_preview_injects_style_tag(): void {
				DashboardWidgetRegistry::register( 'widget_alpha', 'Alpha', '', '', '__return_null' );
				UserLayoutManager::save_role_layout( 'subscriber', array( array( 'id' => 'widget_alpha' ) ) );
		UserLayoutManager::save_role_style( 'subscriber', array( 'background_color' => '#000' ) );

		ob_start();
		DashboardWidgetTools::render_role_dashboard_preview( 'subscriber' );
		$html = ob_get_clean();

		$this->assertStringContainsString( '<style id="ap-preview-style">', $html );
		$this->assertStringContainsString( 'background:#000', $html );
	}
}
