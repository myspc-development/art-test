<?php
namespace ArtPulse\Core;

use ArtPulse\Support\WidgetIds;

class DashboardPresets
{
    /** @var string[] */
    private const ROLES = ['member','artist','organization'];

    /**
     * Cache of role → widget slugs.
     *
     * @var array<string, array<int, string>>
     */
    private static array $cache = [];

    /** Clear the cached presets. */
    public static function resetCache(): void
    {
        self::$cache = [];
    }

    /**
     * Return the canonical list of widget slugs for a role.
     * Looks for JSON under current and legacy paths; falls back to hard-coded defaults.
     *
     * @param string $role
     * @return array<int,string>
     */
    public static function forRole(string $role): array
    {
        $role = sanitize_key($role);
        if (!in_array($role, self::ROLES, true)) {
            $role = 'member';
        }

        if (isset(self::$cache[$role])) {
            return self::$cache[$role];
        }

        // plugin root from src/Core → ../../
        $root = dirname(__DIR__, 2);

        // Try current filename first, then legacy candidates
        $candidates = [
            "$root/data/preset-$role.json",
            "$root/data/presets/{$role}-default.json",
            "$root/data/presets/$role.json",
            // organization sometimes used compact/admin variants historically
            $role === 'organization' ? "$root/data/presets/organization-compact.json" : null,
            $role === 'organization' ? "$root/data/presets/organization-admin.json" : null,
            // earlier member/artist variants
            $role === 'member' ? "$root/data/presets/member-discovery.json" : null,
            $role === 'artist' ? "$root/data/presets/artist-default.json" : null,
            $role === 'artist' ? "$root/data/presets/artist-tools.json" : null,
        ];
        $candidates = array_values(array_filter($candidates, 'is_string'));

        $slugs = [];
        foreach ($candidates as $file) {
            if (@is_readable($file)) {
                $raw = @file_get_contents($file);
                if (is_string($raw) && $raw !== '') {
                    $json = json_decode($raw, true);
                    if (is_array($json)) {
                        $list = isset($json['widgets']) && is_array($json['widgets'])
                            ? $json['widgets']
                            : (array_keys($json) === range(0, count($json) - 1) ? $json : []);
                        foreach ($list as $slug) {
                            if (is_string($slug) && $slug !== '') {
                                $slugs[] = WidgetIds::canonicalize($slug);
                            }
                        }
                        if ($slugs) {
                            break;
                        }
                    }
                }
            }
        }

        // Fallback to the hard-coded canonical layout (docs/dashboard-mockup.tsx)
        if (!$slugs) {
            $fallback = [
                'member' => [
                    'widget_membership','widget_account_tools','widget_my_follows',
                    'widget_recommended_for_you','widget_local_events','widget_my_events','widget_site_stats'
                ],
                'artist' => [
                    'widget_artist_revenue_summary','widget_artist_artwork_manager','widget_artist_audience_insights',
                    'widget_artist_feed_publisher','widget_my_events','widget_site_stats'
                ],
                'organization' => [
                    'widget_audience_crm','widget_org_ticket_insights','widget_webhooks',
                    'widget_my_events','widget_site_stats'
                ],
            ];
            $slugs = $fallback[$role];
        }

        // De-dupe preserving order
        $slugs = array_values(array_unique($slugs));
        return self::$cache[$role] = $slugs;
    }
}
