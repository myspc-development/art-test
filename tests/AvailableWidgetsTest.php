<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**

 * @group WIDGETS
 */

class AvailableWidgetsTest extends TestCase {

	public function test_widget_callbacks_are_renderable(): void {
		$file    = dirname( __DIR__ ) . '/available-widgets.json';
		$widgets = json_decode( file_get_contents( $file ), true );
		$this->assertIsArray( $widgets, 'available-widgets.json did not decode to array' );

		foreach ( $widgets as $widget ) {
			$id       = $widget['id'] ?? '';
			$callback = $widget['callback'] ?? '';

			// Skip obvious JavaScript-only widgets
			if ( preg_match( '/\.jsx$/i', $callback ) ) {
				continue;
			}

			// Class::method style callbacks
			if ( str_contains( $callback, '::' ) ) {
				[$class, $method] = explode( '::', $callback, 2 );
				$this->assertTrue( class_exists( $class ), "$id → missing class" );
				$this->assertTrue( method_exists( $class, $method ), "$id → method $method missing" );
				continue;
			}

			// PHP class callbacks by file name
			if ( preg_match( '/\.php$/i', $callback ) ) {
				$base       = basename( $callback, '.php' );
				$candidates = array(
					"ArtPulse\\Widgets\\$base",
					"ArtPulse\\$base",
					$base,
				);
				$class      = null;
				foreach ( $candidates as $candidate ) {
					if ( class_exists( $candidate ) ) {
						$class = $candidate;
						break;
					}
				}
				$this->assertNotNull( $class, "$id → missing class" );
				$this->assertTrue( method_exists( $class, 'render' ), "$id → class missing render()" );
				continue;
			}

			// Otherwise treat as function callback
			$this->assertTrue( function_exists( $callback ), "$id → missing function" );
		}
	}
}
