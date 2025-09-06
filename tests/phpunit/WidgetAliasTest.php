<?php
require_once __DIR__ . '/../TestStubs.php';
if ( ! function_exists( 'esc_html_e' ) ) {
	function esc_html_e( $text, $domain = null ) {
		echo $text; }
}
if ( ! function_exists( '_e' ) ) {
	function _e( $text, $domain = null ) {
		echo $text; }
}

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\WidgetRegistry;
use ArtPulse\Tests\Stubs\MockStorage;

/**

 * @group PHPUNIT
 */

final class WidgetAliasTest extends TestCase {
	protected function setUp(): void {
		// ensure widgets+aliases registered for the test
		do_action( 'init' );
		MockStorage::$current_roles = array( 'read' );
				$this->registerWidget( 'widget_membership' );
				$this->registerWidget( 'widget_my_events' );
				$this->registerWidget( 'widget_account_tools' );
				$this->registerWidget( 'widget_site_stats' );
				$this->registerWidget( 'widget_recommended_for_you' );
				$this->registerWidget( 'widget_local_events' );
				WidgetRegistry::register( 'widget_my_follows', array( self::class, 'renderMyFollows' ) );
	}

	/**
	 * @dataProvider aliasProvider
	 */
	public function test_legacy_aliases_canonicalize_and_render( string $legacy, string $expected ): void {
		$this->assertSame( $expected, WidgetRegistry::normalize_slug( $legacy ) );
		$this->assertTrue( WidgetRegistry::exists( $expected ) );
		$html = WidgetRegistry::render( $legacy );
		$this->assertIsString( $html );
		$this->assertNotSame( '', trim( $html ) );
	}

	public function aliasProvider(): array {
		return array(
			array( 'membership', 'widget_membership' ),
			array( 'my-events', 'widget_my_events' ),
			array( 'account-tools', 'widget_account_tools' ),
			array( 'site_stats', 'widget_site_stats' ),
			array( 'followed_artists', 'widget_my_follows' ),
			array( 'widget_followed_artists', 'widget_my_follows' ),
			array( 'recommended_for_you', 'widget_recommended_for_you' ),
			array( 'local_events', 'widget_local_events' ),
		);
	}

	public function test_canonical_slug_renders_template(): void {
			$html = WidgetRegistry::render( 'widget_my_follows' );
			$this->assertIsString( $html );
			$this->assertNotSame( '', trim( $html ) );
			$this->assertStringContainsString( '<div', $html );
	}

	private function registerWidget( string $slug ): void {
			WidgetRegistry::register( $slug, array( self::class, 'renderSection' ) );
	}

	public static function renderSection( array $ctx = array() ): string {
			return '<section></section>';
	}

	public static function renderMyFollows( array $ctx = array() ): string {
			ob_start();
			require __DIR__ . '/../../templates/widgets/widget-my-follows.php';
			return ob_get_clean();
	}
}
