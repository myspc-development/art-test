<?php
namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\WidgetRegistry;
use ArtPulse\Tests\Stubs\MockStorage;

require_once __DIR__ . '/../TestStubs.php';

/**

 * @group PHPUNIT
 */

class DashboardWidgetAliasResolutionTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		// Reset DashboardWidgetRegistry state
		$ref = new \ReflectionClass( DashboardWidgetRegistry::class );
		foreach ( array( 'widgets', 'builder_widgets', 'id_map', 'issues', 'logged_duplicates', 'aliases' ) as $prop ) {
			if ( $ref->hasProperty( $prop ) ) {
				$p = $ref->getProperty( $prop );
				$p->setAccessible( true );
				$p->setValue( null, array() );
			}
		}
		// Reset WidgetRegistry state
		$ref2 = new \ReflectionClass( WidgetRegistry::class );
		foreach ( array( 'widgets', 'logged_missing' ) as $prop ) {
			if ( $ref2->hasProperty( $prop ) ) {
				$p = $ref2->getProperty( $prop );
				$p->setAccessible( true );
				$p->setValue( null, array() );
			}
		}
		WidgetRegistry::resetDebug();
		MockStorage::$current_roles = array( 'manage_options' );
	}

	/**
	 * @dataProvider aliasProvider
	 */
	public function test_aliases_resolve_and_render_per_role( string $role, string $alias, string $canonical ): void {
		// Register canonical widget and its alias
		self::$currentCanonical = $canonical;
		self::$currentAlias     = $alias;
		WidgetRegistry::register( $canonical, array( self::class, 'renderCanonical' ) );
		DashboardWidgetRegistry::register( $canonical, 'Test', '', '', array( self::class, 'renderAlias' ), array( 'roles' => array( $role ) ) );
		DashboardWidgetRegistry::alias( $alias, $canonical );

		$this->assertTrue( DashboardWidgetRegistry::exists( $canonical ) );
		$this->assertTrue( DashboardWidgetRegistry::exists( $alias ) );

		$defAlias     = DashboardWidgetRegistry::get( $alias );
		$defCanonical = DashboardWidgetRegistry::get( $canonical );
		$this->assertSame( $defCanonical, $defAlias );

		$widgets = DashboardWidgetRegistry::get_widgets_by_role( $role, 1 );
		$this->assertArrayHasKey( $canonical, $widgets );
		$this->assertArrayNotHasKey( $alias, $widgets );

		$html = call_user_func( $defAlias['callback'], 1 );
		$this->assertStringContainsString( $canonical, $html );
	}

	public function aliasProvider(): array {
		return array(
			array( 'member', 'followed_artists', 'widget_my_follows' ),
			array( 'member', 'widget_followed_artists', 'widget_my_follows' ),
			array( 'artist', 'my-events', 'widget_my_events' ),
			array( 'artist', 'myevents', 'widget_my_events' ),
			array( 'organization', 'widget_account-tools', 'widget_account_tools' ),
		);
	}

	/**
	 * @dataProvider roleProvider
	 */
	public function test_unknown_slug_renders_placeholder( string $role ): void {
		WidgetRegistry::setDebug( true );
		$html = WidgetRegistry::render( 'unknown_widget' );
		$this->assertStringContainsString( 'ap-widget--missing', $html );
		WidgetRegistry::resetDebug();
	}

	public function roleProvider(): array {
			return array(
				array( 'member' ),
				array( 'artist' ),
				array( 'organization' ),
			);
	}

	private static string $currentCanonical = '';
	private static string $currentAlias     = '';

	public static function renderCanonical( array $ctx = array() ): string {
			return '<div data-slug="' . self::$currentCanonical . '"></div>';
	}

	public static function renderAlias(): string {
			return WidgetRegistry::render( self::$currentAlias );
	}
}
