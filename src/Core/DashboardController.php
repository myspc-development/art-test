<?php
namespace ArtPulse\Core;

use ArtPulse\Core\DashboardWidgetRegistry;

class DashboardController {

    /**
     * Default widgets available to each role.
     *
     * Widget IDs must match those registered via {@see DashboardWidgetRegistry}.
     *
     * @var array<string,string[]>
     */
    private static array $role_widgets = [
        // Default widgets for newly created members
        'member'       => [
            'widget_news',
            'widget_favorites',
            'widget_events',
            'notifications',
            'my_rsvps',
            'recommended_for_you',
        ],
        // Artist dashboard starter widgets
        'artist'       => [
            'widget_spotlights',
            'artist_inbox_preview',
            'artist_revenue_summary',
            'widget_followed_artists',
            'notifications',
        ],
        // Organization admin widgets
        'organization' => [
            'site_stats',
            'webhooks',
            'rsvp_stats',
            'artpulse_analytics_widget',
            'ap_donor_activity',
            'notifications',
        ],
    ];

    /**
     * Default layout presets keyed by unique identifier.
     *
     * @return array<string,array{title:string,role:string,layout:array<int,array{id:string}>}>
     */
    public static function get_default_presets(): array {
        return [
            'member_default' => [
                'title'  => 'Member Default',
                'role'   => 'member',
                'layout' => [
                    ['id' => 'widget_news'],
                    ['id' => 'widget_favorites'],
                    ['id' => 'widget_events'],
                    ['id' => 'instagram_widget'],
                ],
            ],
            'artist_default' => [
                'title'  => 'Artist Default',
                'role'   => 'artist',
                'layout' => [
                    ['id' => 'widget_spotlights'],
                ],
            ],
            // New sample layouts that can be applied from the dashboard UI
            'new_member_intro' => [
                'title'  => 'New Member Intro',
                'role'   => 'member',
                'layout' => self::load_preset_layout('member', 'discovery'),
            ],
            'artist_tools' => [
                'title'  => 'Artist Tools',
                'role'   => 'artist',
                'layout' => self::load_preset_layout('artist', 'tools'),
            ],
            'org_admin_start' => [
                'title'  => 'Organization Admin Start',
                'role'   => 'organization',
                'layout' => self::load_preset_layout('organization', 'admin'),
            ],
        ];
    }

    /**
     * Get the widgets assigned to a role.
     */
    public static function get_widgets_for_role(string $role): array
    {
        if (isset(self::$role_widgets[$role])) {
            $widgets = self::$role_widgets[$role];
        } else {
            $widgets = [];
        }

        $valid = [];
        foreach ($widgets as $id) {
            if (DashboardWidgetRegistry::get_widget($id)) {
                $valid[] = $id;
            } else {
                trigger_error('Dashboard widget not registered: ' . $id, E_USER_WARNING);
            }
        }

        return $valid;
    }

    /**
     * Determine the dashboard layout for a user. Checks user overrides then
     * falls back to the default widgets for their role.
     */
    public static function get_user_dashboard_layout(int $user_id): array
    {
        $role = self::get_role($user_id);

        $custom = get_user_meta($user_id, 'ap_dashboard_layout', true);
        if (!empty($custom) && is_array($custom)) {
            return $custom;
        }

        return array_map(
            fn($id) => ['id' => $id],
            self::get_widgets_for_role($role)
        );
    }

    /**
     * Remove a user's saved dashboard layout.
     */
    public static function reset_user_dashboard_layout(int $user_id): void
    {
        delete_user_meta($user_id, 'ap_dashboard_layout');
    }

    /**
     * Load a preset layout file for a role.
     */
    public static function load_preset_layout(string $role, string $preset): array
    {
        $file = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . "data/presets/{$role}-{$preset}.json";
        if (file_exists($file)) {
            $json = file_get_contents($file);
            $layout = json_decode($json, true);
            return is_array($layout) ? $layout : [];
        }
        return [];
    }
    public static function render_for_user($user_id) {
        $role = self::get_role($user_id);

        switch ($role) {
            case 'artist':
                return ArtistDashboardHome::render($user_id);
            case 'organization':
                return OrgDashboardManager::render($user_id);
            case 'member':
            default:
                return UserDashboardManager::render($user_id);
        }
    }

    public static function get_role($user_id): string {
        // Allow preview via ?ap_preview_role=artist for admin users
        if (current_user_can('manage_options') && isset($_GET['ap_preview_role'])) {
            return sanitize_text_field($_GET['ap_preview_role']);
        }

        $user = get_userdata($user_id);
        if (!$user || empty($user->roles)) {
            return 'member';
        }

        return $user->roles[0];
    }

    /**
     * Retrieve custom dashboard widget posts visible to the user's role.
     */
    public static function get_custom_widgets_for_user(int $user_id): array
    {
        $role = self::get_role($user_id);

        $args = [
            'post_type'      => 'dashboard_widget',
            'posts_per_page' => -1,
            'orderby'        => 'meta_value_num',
            'meta_key'       => 'widget_order',
            'order'          => 'ASC',
            'meta_query'     => [[
                'key'     => 'visible_to_roles',
                'value'   => $role,
                'compare' => 'LIKE',
            ]],
        ];

        return get_posts($args);
    }
}
