<?php
namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Widgets\Placeholder\ApPlaceholderWidget;

require_once __DIR__ . '/../TestStubs.php';

class DashboardWidgetRegistryAliasTest extends TestCase {

	protected function setUp(): void {
		$ref = new \ReflectionClass( DashboardWidgetRegistry::class );
		foreach ( array( 'widgets', 'builder_widgets', 'id_map', 'issues', 'logged_duplicates', 'aliases' ) as $prop ) {
			if ( $ref->hasProperty( $prop ) ) {
				$p = $ref->getProperty( $prop );
				$p->setAccessible( true );
				$p->setValue( null, array() );
			}
		}
	}

	public function test_alias_resolves_to_canonical(): void {
		DashboardWidgetRegistry::register( 'widget_favorites', 'Favorites', '', '', static fn() => '' );
		DashboardWidgetRegistry::register( 'widget_widget_favorites', 'Legacy', '', '', static fn() => '' );
		DashboardWidgetRegistry::alias( 'widget_widget_favorites', 'widget_favorites' );

		$defs = DashboardWidgetRegistry::get_all();
		$this->assertArrayHasKey( 'widget_favorites', $defs );
		$this->assertArrayNotHasKey( 'widget_widget_favorites', $defs );
		$this->assertTrue( DashboardWidgetRegistry::exists( 'widget_widget_favorites' ) );
		$this->assertSame(
			DashboardWidgetRegistry::get( 'widget_favorites' ),
			DashboardWidgetRegistry::get( 'widget_widget_favorites' )
		);
	}

	public function test_bind_renderer_updates_definition(): void {
		DashboardWidgetRegistry::register( 'widget_demo', 'Demo', '', '', array( ApPlaceholderWidget::class, 'render' ) );
		$class = get_class(
			new class() {
				public static function render( int $user_id = 0 ): string {
					return 'ok'; }
			}
		);
		DashboardWidgetRegistry::bindRenderer( 'widget_demo', array( $class, 'render' ) );
		$def = DashboardWidgetRegistry::get( 'widget_demo' );
		$this->assertSame( $class, $def['class'] );
		$this->assertTrue( is_callable( $def['callback'] ) );
	}

	public function test_news_alias_maps_to_feed(): void {
		DashboardWidgetRegistry::register( 'widget_news_feed', 'NewsFeed', '', '', static fn() => '' );
		DashboardWidgetRegistry::alias( 'widget_news', 'widget_news_feed' );
		$this->assertTrue( DashboardWidgetRegistry::exists( 'widget_news' ) );
		$this->assertSame(
			DashboardWidgetRegistry::get( 'widget_news_feed' ),
			DashboardWidgetRegistry::get( 'widget_news' )
		);
	}
}
