<?php
namespace ArtPulse\Core;

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Frontend\ArtistDashboardShortcode;
use ArtPulse\Frontend\OrganizationDashboardShortcode;

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
            'membership',
            'upgrade',
            'account-tools',
            'recommended_for_you',
            'my_rsvps',
            'favorites',
            'local-events',
            'my-follows',
            'notifications',
            'messages',
            'dashboard_feedback',
            'cat_fact',
        ],
        // Artist dashboard starter widgets
        'artist'       => [
            'artist_feed_publisher',
            'artist_audience_insights',
            'artist_spotlight',
            'artist_revenue_summary',
            'my_events',
            'messages',
            'notifications',
            'dashboard_feedback',
            'cat_fact',
        ],
        // Organization admin widgets
        // Synced with widget manifest. Guard below will warn on drift.
        'organization' => [
            'org_event_overview',
            'artpulse_analytics_widget',
            'rsvp_stats',
            'my-events',
            'org_ticket_insights',
            'org_team_roster',
            'audience_crm',
            'org_broadcast_box',
            'org_approval_center',
            'webhooks',
            'support-history',
        ],
    ];

    /** @var bool */
    private static bool $defaults_checked = false;

    /**
     * Default layout presets keyed by unique identifier.
     *
     * Preset layouts are filtered so only widgets the specified role can access
     * are returned. Unregistered widgets, widgets limited to other roles and
     * widgets requiring capabilities the role lacks are automatically removed.
     *
     * @return array<string,array{title:string,role:string,layout:array<int,array{id:string}>}>
     */
    public static function get_default_presets(): array {
        $presets = [
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
                    ['id' => 'activity_feed'],
                    ['id' => 'artist_inbox_preview'],
                    ['id' => 'artist_revenue_summary'],
                    ['id' => 'artist_spotlight'],
                    ['id' => 'widget_favorites'],
                    ['id' => 'qa_checklist'],
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

        // Remove widgets the role cannot access.
        foreach ($presets as $key => $preset) {
            $presets[$key]['layout'] = self::filter_accessible_layout(
                $preset['layout'],
                $preset['role']
            );
        }

        return $presets;
    }

    /**
     * Filter a preset layout so it only contains widgets the role can access.
     */
    private static function filter_accessible_layout(array $layout, string $role): array
    {
        $filtered = [];
        $role_obj = function_exists('get_role') ? get_role($role) : null;

        foreach ($layout as $entry) {
            $id = $entry['id'] ?? '';
            if (!$id) {
                continue;
            }

            $config = DashboardWidgetRegistry::get($id);
            if (!$config) {
                continue; // unregistered widget
            }

            $widget_roles = isset($config['roles']) ? (array) $config['roles'] : [];
            if ($widget_roles && !in_array($role, $widget_roles, true)) {
                continue; // role mismatch
            }

            $cap = $config['capability'] ?? '';
            if ($cap && $role !== 'administrator') {
                if (!$role_obj || !$role_obj->has_cap($cap)) {
                    continue; // capability not allowed for role
                }
            }

            $filtered[] = ['id' => $id];
        }

        return $filtered;
    }

    /**
     * Verify that default widget IDs are registered and log a warning once.
     */
    private static function verify_default_widgets(): void
    {
        if (self::$defaults_checked) {
            return;
        }

        self::$defaults_checked = true;

        $missing = [];
        foreach (self::$role_widgets as $ids) {
            foreach ($ids as $id) {
                if (!DashboardWidgetRegistry::get($id)) {
                    $missing[] = $id;
                }
            }
        }

        if ($missing) {
            trigger_error(
                'Unregistered dashboard widget defaults: ' . implode(', ', array_unique($missing)),
                E_USER_WARNING
            );
        }
    }

    /**
     * Get the widgets assigned to a role.
     */
    public static function get_widgets_for_role(string $role): array
    {
        self::verify_default_widgets();

        if (isset(self::$role_widgets[$role])) {
            $widgets = self::$role_widgets[$role];
        } else {
            $widgets = [];
        }

        $valid = [];
        foreach ($widgets as $id) {
            if (DashboardWidgetRegistry::get($id)) {
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
        if (current_user_can('manage_options') && isset($_GET['ap_preview_user'])) {
            $preview = (int) $_GET['ap_preview_user'];
            if ($preview > 0) {
                $user_id = $preview;
            }
        }

        $role = self::get_role($user_id);

        // Load the raw layout from user meta, options, or defaults
        $custom = get_user_meta($user_id, 'ap_dashboard_layout', true);
        $layout = [];

        if (!empty($custom) && is_array($custom)) {
            $layout = $custom;
        } else {
            $layouts = get_option('ap_dashboard_widget_config', []);
            if (!empty($layouts[$role]) && is_array($layouts[$role])) {
                $layout = $layouts[$role];
            } else {
                $layout = array_map(
                    fn($id) => ['id' => $id],
                    self::get_widgets_for_role($role)
                );
            }
        }

        // Filter out any widgets not registered for this role
        $all = DashboardWidgetRegistry::get_all();
        $filtered = array_values(array_filter(
            $layout,
            static function ($w) use ($role, $all) {
                $id = $w['id'] ?? null;
                if (!$id || !isset($all[$id])) {
                    return false;
                }
                $roles = isset($all[$id]['roles']) ? (array) $all[$id]['roles'] : [];
                return in_array($role, $roles, true);
            }
        ));

        if (empty($filtered)) {
            /**
             * Fires when a user's dashboard layout resolves to an empty set.
             *
             * Plugins may use this to display a notice or offer to load a preset
             * layout for the current role.
             */
            do_action('ap_dashboard_empty_layout', $user_id, $role);
        }

        return $filtered;
    }

    /**
     * Reset a user's dashboard layout if corrupted or contains widgets not allowed
     * for their role.
     *
     * @return bool True when the layout was reset.
     */
    public static function reset_user_dashboard_layout(int $user_id): bool
    {
        $role = self::get_role($user_id);
        $default_ids = self::get_widgets_for_role($role);

        if (empty($default_ids)) {
            delete_user_meta($user_id, 'ap_dashboard_layout');
            return false;
        }

        $current = get_user_meta($user_id, 'ap_dashboard_layout', true);
        $needs_reset = false;

        if (!is_array($current) || empty($current)) {
            $needs_reset = true;
        } else {
            $ids = [];
            foreach ($current as $item) {
                if (is_array($item) && isset($item['id'])) {
                    $ids[] = sanitize_key($item['id']);
                } elseif (is_string($item)) {
                    $ids[] = sanitize_key($item);
                }
            }
            $allowed = array_keys(DashboardWidgetRegistry::get_widgets($role, $user_id));
            $missing = array_diff($default_ids, $ids);
            $unauth = array_diff($ids, $allowed);
            if (count($missing) >= floor(count($default_ids) / 2) || !empty($unauth)) {
                $needs_reset = true;
            }
        }

        if ($needs_reset) {
            $layout = array_map(fn($id) => ['id' => $id], $default_ids);
            update_user_meta($user_id, 'ap_dashboard_layout', $layout);
            return true;
        }

        return false;
    }

    /**
     * Load a preset layout file for a role.
     */
    public static function load_preset_layout(string $role, string $preset): array
    {
        $file = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . "data/presets/{$role}-{$preset}.json";
        if (file_exists($file)) {
            $json   = file_get_contents($file);
            $layout = json_decode($json, true);

            if (!is_array($layout)) {
                return [];
            }

            $clean = [];
            foreach ($layout as $entry) {
                if (!is_array($entry) || !isset($entry['id'])) {
                    continue;
                }

                $id = DashboardWidgetRegistry::map_to_core_id(sanitize_key($entry['id']));

                if (!DashboardWidgetRegistry::get($id)) {
                    error_log("[Dashboard Preset] Widget {$id} not registered");
                    continue;
                }

                $entry['id'] = $id;
                $clean[]     = $entry;
            }

            return $clean;
        }
        return [];
    }
    public static function render_for_user($user_id) {
        return UserDashboardManager::renderDashboard([]);
    }

    /**
     * Render the current user's dashboard.
     *
     * This helper loads the correct dashboard for the logged-in user and
     * returns the generated HTML. When no user is logged in a small
     * login prompt is returned instead of triggering errors.
     */
    public static function render(): string
    {
        if (!is_user_logged_in()) {
            return '<p>' . esc_html__('Please log in to view your dashboard.', 'artpulse') . '</p>';
        }

        return self::render_for_user(get_current_user_id());
    }

    /**
     * Helper alias for get_role().
     */
    public static function get_user_role($user_id = null): string
    {
        $user_id = $user_id ?: get_current_user_id();
        return self::get_role($user_id);
    }

    public static function get_role($user_id): string {
        // Allow preview via ?ap_preview_role=artist for admin users
        if (current_user_can('manage_options') && isset($_GET['ap_preview_role'])) {
            return sanitize_text_field($_GET['ap_preview_role']);
        }

        if (current_user_can('manage_options') && isset($_GET['ap_preview_user'])) {
            $preview = (int) $_GET['ap_preview_user'];
            if ($preview > 0) {
                $user_id = $preview;
            }
        }

        $user = get_userdata($user_id);
        if (!$user || empty($user->roles)) {
            return 'member';
        }

        $roles = array_map('sanitize_key', $user->roles);
        $priority = ['member', 'artist', 'organization'];
        foreach ($priority as $r) {
            if (in_array($r, $roles, true)) {
                return $r;
            }
        }

        return $roles[0];
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
