<?php
namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\WidgetRegistry;

/**

 * @group PHPUNIT

 */

class WidgetRegistryTest extends TestCase {

         public static function renderGreeting( array $ctx = array() ): string {
                 return 'hello ' . ( $ctx['name'] ?? 'world' );
         }

         protected function tearDown(): void {
		$ref  = new \ReflectionClass( WidgetRegistry::class );
		$prop = $ref->getProperty( 'widgets' );
		$prop->setAccessible( true );
		$prop->setValue( null, array() );
		$prop = $ref->getProperty( 'logged_missing' );
		$prop->setAccessible( true );
		$prop->setValue( null, array() );
		WidgetRegistry::resetDebug();
	}

         public function test_register_and_render(): void {
                 WidgetRegistry::setDebug( false );
                WidgetRegistry::register( 'widget_foo', [self::class, 'renderGreeting'] );
                $this->assertTrue( WidgetRegistry::exists( 'widget_foo' ) );
                $this->assertSame( 'hello bob', WidgetRegistry::render( 'widget_foo', array( 'name' => 'bob' ) ) );
                $this->assertContains( 'widget_foo', WidgetRegistry::list() );
         }

	public function test_missing_slug_returns_placeholder_with_data_slug(): void {
		WidgetRegistry::setDebug( true );
		$html = WidgetRegistry::render( 'missing' );
		$this->assertStringContainsString( 'ap-widget--missing', $html );
		$this->assertStringContainsString( 'data-slug="widget_missing"', $html );
	}

	public function test_missing_slug_returns_empty_string_when_debug_disabled(): void {
		WidgetRegistry::setDebug( false );
		$html = WidgetRegistry::render( 'missing' );
		$this->assertSame( '', $html );
	}
}
