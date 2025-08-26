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

    /** @var bool */
    private static bool $bootstrapped = false;

    /** Normalize preset slugs to canonical IDs */
    public static function bootstrap(): void
    {
        if (self::$bootstrapped) {
            return;
        }
        self::$bootstrapped = true;
        foreach (self::$presets as $key => $slugs) {
            self::$presets[$key] = array_map([WidgetRegistry::class, 'normalize_slug'], $slugs);
        }
    }

    /**
     * Retrieve preset widget slugs for a role or preset key.
     *
     * @param string $role Role or preset key.
     * @return array<int, string>
     */
    public static function get_preset_for_role(string $role): array
    {
        self::bootstrap();
        $key    = strtolower(trim($role));
        $preset = self::$presets[$key] ?? [];
        if (function_exists('apply_filters')) {
            $preset = (array) apply_filters('artpulse/dashboard/preset', $preset, $key);
        }

        $preset = array_map([WidgetRegistry::class, 'normalize_slug'], $preset);
        $validSlugs = WidgetRegistry::get_canonical_ids();
        $invalid    = array_diff($preset, $validSlugs);
        $preset     = array_values(array_intersect($preset, $validSlugs));

        if (defined('ARTPULSE_DEBUG_VERBOSE') && ARTPULSE_DEBUG_VERBOSE && function_exists('is_user_logged_in') && is_user_logged_in()) {
            foreach ($invalid as $slug) {
                error_log('ArtPulse: Unknown widget slug: ' . $slug);
            }
        }

        return $preset;
    }
}

DashboardPresets::bootstrap();
