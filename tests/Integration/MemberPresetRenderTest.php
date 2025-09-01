<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\WidgetRegistry;
use ArtPulse\Core\DashboardPresets;

/**

 * @group INTEGRATION

 */

final class MemberPresetRenderTest extends TestCase {

	public function test_member_preset_contains_my_follows_section(): void {
		$ids = $this->memberPresetIds();
		$this->assertNotEmpty( $ids, 'Could not resolve member preset IDs' );
		$this->assertContains( 'widget_my_follows', $ids, 'Preset should include widget_my_follows' );

		// Render just the canonical widget and assert it emits a <section>
		$html = WidgetRegistry::render( 'widget_my_follows' );
		$this->assertIsString( $html );
		$this->assertNotSame( '', trim( $html ), 'Rendered markup should not be empty' );
		$this->assertStringContainsString( '<section', $html, 'widget_my_follows should render a <section>' );
	}

	/** @return array<int,string> */
	private function memberPresetIds(): array {
		$cls = DashboardPresets::class;

		// Try common accessors first
		foreach ( array( 'get', 'idsForRole', 'ids_for', 'forRole' ) as $m ) {
			if ( method_exists( $cls, $m ) ) {
				$out = $cls::$m( 'member' );
				if ( is_array( $out ) ) {
					return $out;
				}
				if ( is_array( $out['member'] ?? null ) ) {
					return $out['member'];
				}
			}
		}

		// Fall back to constants or static properties
		$ref = new \ReflectionClass( $cls );

		if ( $ref->hasConstant( 'PRESETS' ) ) {
			$presets = $ref->getConstant( 'PRESETS' );
			if ( is_array( $presets['member'] ?? null ) ) {
				return $presets['member'];
			}
		}

		if ( $ref->hasProperty( 'presets' ) ) {
			$prop = $ref->getProperty( 'presets' );
			$prop->setAccessible( true );
			$presets = $prop->getValue();
			if ( is_array( $presets['member'] ?? null ) ) {
				return $presets['member'];
			}
		}

		return array();
	}
}
