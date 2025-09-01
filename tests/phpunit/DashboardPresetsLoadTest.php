<?php
declare(strict_types=1);

require_once __DIR__ . '/../TestStubs.php';
require_once __DIR__ . '/../TestHelpers/filesystem.php';

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardPresets;
use function ArtPulse\Tests\safe_unlink;
use Brain\Monkey;
use Brain\Monkey\Functions;

/**

 * @group PHPUNIT

 */

final class DashboardPresetsLoadTest extends TestCase {
	private string $dataDir;

        protected function setUp(): void {
                parent::setUp();
                Monkey\setUp();
                DashboardPresets::resetCache();
                $this->dataDir = dirname( __DIR__, 2 ) . '/data';
        }

        protected function tearDown(): void {
                DashboardPresets::resetCache();
                Monkey\tearDown();
                parent::tearDown();
        }

        public function test_missing_preset_skips_legacy_and_uses_defaults(): void {
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
                        'member'       => array(
                                'widget_membership',
                                'widget_account_tools',
                                'widget_my_follows',
                                'widget_recommended_for_you',
                                'widget_local_events',
                                'widget_my_events',
                                'widget_site_stats',
                        ),
                        'artist'       => array(
                                'widget_artist_revenue_summary',
                                'widget_artist_artwork_manager',
                                'widget_artist_audience_insights',
                                'widget_artist_feed_publisher',
                                'widget_my_events',
                                'widget_site_stats',
                        ),
                        'organization' => array(
                                'widget_audience_crm',
                                'widget_org_ticket_insights',
                                'widget_webhooks',
                                'widget_my_events',
                                'widget_site_stats',
                        ),
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
                file_put_contents(
                        $path,
                        json_encode(
                                array(
                                        'membership',
                                        'account-tools',
                                        'widget_followed_artists',
                                        'recommended_for_you',
                                        'local-events',
                                        'my-events',
                                        'site-stats',
                                )
                        )
                );
                $this->assertSame(
                        array(
                                'widget_membership',
                                'widget_account_tools',
                                'widget_my_follows',
                                'widget_recommended_for_you',
                                'widget_local_events',
                                'widget_my_events',
                                'widget_site_stats',
                        ),
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
