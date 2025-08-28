<?php
namespace ArtPulse\Core\Tests;

require_once __DIR__ . '/../TestStubs.php';

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\WidgetRegistry;
use ArtPulse\Core\DashboardPresets;

class DashboardPresetIntegrityTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		// Parse widget registration files and register slugs
		$files = array(
			__DIR__ . '/../../includes/dashboard-widgets.php',
			__DIR__ . '/../../includes/business-dashboard-widgets.php',
		);
		foreach ( $files as $file ) {
			if ( ! is_file( $file ) ) {
				continue;
			}
			$contents = file_get_contents( $file );
			if ( $contents === false ) {
				continue;
			}
			if ( preg_match_all( "/DashboardWidgetRegistry::register\(\s*'([^']+)'/", $contents, $m ) ) {
				foreach ( $m[1] as $slug ) {
					WidgetRegistry::register( $slug, static fn() => '' );
				}
			}
		}
	}

	protected function tearDown(): void {
		$ref = new \ReflectionClass( WidgetRegistry::class );
		foreach ( array( 'widgets', 'logged_missing' ) as $prop ) {
			$p = $ref->getProperty( $prop );
			$p->setAccessible( true );
			$p->setValue( null, array() );
		}
		parent::tearDown();
	}

	public function test_presets_reference_registered_slugs(): void {
		$registered = WidgetRegistry::list();
		foreach ( array( 'member', 'artist', 'organization' ) as $role ) {
			$preset  = DashboardPresets::forRole( $role );
			$unknown = array_diff( $preset, $registered );
			$this->assertEmpty(
				$unknown,
				"Preset {$role} references unregistered widgets: " . implode( ', ', $unknown )
			);
		}
	}
}
