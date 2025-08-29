<?php
declare(strict_types=1);

require_once __DIR__ . '/../TestStubs.php';

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardPresets;
use function ArtPulse\Tests\safe_unlink;

final class DashboardPresetsLoadTest extends TestCase {
	private string $dataDir;

	protected function setUp(): void {
		DashboardPresets::resetCache();
		$this->dataDir = dirname( __DIR__, 2 ) . '/data';
	}

	protected function tearDown(): void {
		DashboardPresets::resetCache();
	}

	public function test_fallback_when_json_missing(): void {
		$roles   = array( 'member', 'artist', 'organization' );
		$backups = array();
		foreach ( $roles as $r ) {
			$path = "$this->dataDir/preset-$r.json";
			if ( is_readable( $path ) ) {
				$backups[ $r ] = file_get_contents( $path );
				safe_unlink( $path );
			}
		}
                $expected = array(
                        'member'       => array_fill( 0, 7, 'widget_placeholder' ),
                        'artist'       => array_fill( 0, 6, 'widget_placeholder' ),
                        'organization' => array_fill( 0, 5, 'widget_placeholder' ),
                );
		foreach ( $roles as $r ) {
			$this->assertSame( $expected[ $r ], DashboardPresets::forRole( $r ) );
		}
		foreach ( $backups as $r => $contents ) {
			file_put_contents( "$this->dataDir/preset-$r.json", (string) $contents );
		}
	}

	public function test_json_canonicalization(): void {
		$path = "$this->dataDir/preset-member.json";
		$orig = is_readable( $path ) ? file_get_contents( $path ) : null;
		file_put_contents( $path, json_encode( array( 'membership', 'account-tools', 'widget_followed_artists' ) ) );
		$this->assertSame(
			array( 'widget_membership', 'widget_account_tools', 'widget_my_follows' ),
			DashboardPresets::forRole( 'member' )
		);
		if ( $orig !== null ) {
			file_put_contents( $path, $orig );
		} else {
			safe_unlink( $path );
		}
	}

	public function test_bogus_falls_back_to_member(): void {
		$ids = DashboardPresets::forRole( 'bogus' );
		$this->assertContains( 'widget_membership', $ids );
	}
}
