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
            'widget_my_follows',
            'widget_local_events',
            'widget_recommended_for_you',
            'widget_my_events',
            'widget_account_tools',
            'widget_site_stats',
        ],
        'artist' => [
            'widget_artist_revenue_summary',
            'widget_artist_artwork_manager',
            'widget_artist_audience_insights',
            'widget_artist_feed_publisher',
            'widget_my_events',
            'widget_site_stats',
        ],
        'organization' => [
            'widget_audience_crm',
            'widget_org_ticket_insights',
            'widget_webhooks',
            'widget_my_events',
            'widget_site_stats',
        ],
        'new_member_intro' => [
            'widget_membership',
        ],
        'org_admin_start' => [
            'widget_audience_crm',
        ],
    ];

    /** @var string[] */
    private const ALLOWED_ROLES = ['member', 'artist', 'organization'];

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

        if (defined('AP_VERBOSE_DEBUG') && AP_VERBOSE_DEBUG && function_exists('is_user_logged_in') && is_user_logged_in()) {
            foreach ($invalid as $slug) {
                error_log('ArtPulse: Unknown widget slug: ' . $slug);
            }
        }

        return $preset;
    }

    /** @return array<int,string> */
    public static function forRole(string $role): array
    {
        $role = sanitize_key($role);
        if (!in_array($role, self::ALLOWED_ROLES, true)) {
            $role = 'member';
        }
        return self::get_preset_for_role($role);
    }
}

DashboardPresets::bootstrap();
