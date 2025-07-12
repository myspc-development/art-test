<?php
namespace ArtPulse\Core;

class DashboardController {

    /**
     * Default widgets available to each role.
     *
     * @var array<string,string[]>
     */
    private static array $role_widgets = [
        'artist'       => ['widget_profile', 'widget_recent_events', 'widget_sales'],
        'organization' => ['widget_members', 'widget_finance', 'widget_rsvp'],
        'member'       => ['widget_news', 'widget_events', 'widget_favorites'],
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
                ],
            ],
            'artist_default' => [
                'title'  => 'Artist Default',
                'role'   => 'artist',
                'layout' => [
                    ['id' => 'widget_spotlights'],
                    ['id' => 'widget_applications'],
                ],
            ],
        ];
    }

    /**
     * Get the widgets assigned to a role.
     */
    public static function get_widgets_for_role(string $role): array
    {
        return self::$role_widgets[$role] ?? [];
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
