<?php
namespace {
    if (!function_exists('apply_filters')) {
        function apply_filters($tag, $value, ...$args) { return $value; }
    }
}

namespace ArtPulse\Core\Tests {

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\WidgetRegistry;
use ArtPulse\Core\DashboardPresets;

class DashboardPresetIntegrityTest extends TestCase
{
    protected function setUp(): void
    {
        if (!defined('WP_DEBUG')) {
            define('WP_DEBUG', true);
        }
        $slugs = [
            'membership',
            'widget_followed_artists',
            'upcoming_events_by_location',
            'recommended_for_you',
            'myevents',
            'accounttools',
            'site_stats',
            'artist_revenue_summary',
            'artist_artwork_manager',
            'artist_audience_insights',
            'artist_feed_publisher',
            'lead_capture',
            'rsvp_stats',
            'webhooks',
        ];
        foreach ($slugs as $slug) {
            WidgetRegistry::register($slug, static fn() => '<div>' . $slug . '</div>');
        }
    }

    protected function tearDown(): void
    {
        $ref  = new \ReflectionClass(WidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue(null, []);
        $prop = $ref->getProperty('logged_missing');
        $prop->setAccessible(true);
        $prop->setValue(null, []);
    }

    public function test_presets_reference_registered_widgets(): void
    {
        foreach (['member', 'artist', 'organization'] as $role) {
            $slugs = DashboardPresets::get_preset_for_role($role);
            foreach ($slugs as $slug) {
                $this->assertTrue(
                    WidgetRegistry::exists($slug),
                    "Preset for {$role} includes unknown slug {$slug}"
                );
            }
        }
    }
}

}

