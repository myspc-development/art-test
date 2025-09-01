<?php
namespace ArtPulse\Core\Tests;

require_once __DIR__ . '/../TestStubs.php';

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\WidgetRegistry;
use ArtPulse\Core\DashboardPresets;

/** @coversNothing */
/**
 * @group PHPUNIT
 */
class DashboardPresetIntegrityTest extends TestCase {
    public static function renderEmpty(): string {
        return '';
    }
    protected function setUp(): void {
        parent::setUp();
        // Register a minimal set of widgets used by presets.
        $slugs = array(
            'widget_membership',
            'widget_my_follows',
            'widget_local_events',
            'widget_recommended_for_you',
            'widget_my_events',
            'widget_account_tools',
            'widget_site_stats',
            'widget_artist_revenue_summary',
            'widget_artist_artwork_manager',
            'widget_artist_audience_insights',
            'widget_artist_feed_publisher',
            'widget_audience_crm',
            'widget_org_ticket_insights',
            'widget_webhooks',
        );
        foreach ( $slugs as $slug ) {
            WidgetRegistry::register( $slug, [self::class, 'renderEmpty'] );
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

    public function test_presets_reference_registered_widgets(): void {
        foreach ( array( 'member', 'artist', 'organization' ) as $role ) {
            $slugs = DashboardPresets::forRole( $role );
            foreach ( $slugs as $slug ) {
                $this->assertTrue(
                    WidgetRegistry::exists( $slug ),
                    "Preset for {$role} includes unknown slug {$slug}"
                );
            }
        }
    }
}

