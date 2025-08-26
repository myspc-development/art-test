<?php
namespace ArtPulse\Core;

/**
 * Role-based dashboard widget presets.
 */
class DashboardPresets
{
    /**
     * Mapping of role or preset keys to widget slugs.
     *
     * @var array<string, array<int, string>>
     */
    private static array $presets = [
        'member' => [
            'widget_membership',
            'widget_followed_artists',
            'upcoming_events_by_location',
            'recommended_for_you',
            'my-events',
            'account-tools',
            'site_stats',
        ],
        'artist' => [
            'artist_revenue_summary',
            'artist_artwork_manager',
            'artist_audience_insights',
            'artist_feed_publisher',
            'my-events',
            'site_stats',
        ],
        'organization' => [
            'lead_capture',
            'rsvp_stats',
            'webhooks',
            'my-events',
            'site_stats',
        ],
        'new_member_intro' => [
            'widget_membership',
        ],
        'org_admin_start' => [
            'lead_capture',
        ],
    ];

    /**
     * Retrieve preset widget slugs for a role or preset key.
     *
     * @param string $role Role or preset key.
     * @return array<int, string>
     */
    public static function get_preset_for_role(string $role): array
    {
        $key    = strtolower(trim($role));
        $preset = self::$presets[$key] ?? [];
        if (function_exists('apply_filters')) {
            $preset = (array) apply_filters('artpulse/dashboard/preset', $preset, $key);
        }

        $validSlugs = WidgetRegistry::list();
        $invalid    = array_diff($preset, $validSlugs);
        $preset     = array_values(array_intersect($preset, $validSlugs));

        if (defined('WP_DEBUG') && WP_DEBUG) {
            foreach ($invalid as $slug) {
                error_log('ArtPulse: Unknown widget slug: ' . $slug);
            }
        }

        return $preset;
    }
}
