<?php
namespace ArtPulse\Dashboard;

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Support\OptionUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles dashboard widget visibility and related notices.
 */
/**
 * Dashboard widget visibility manager.
 *
 * Public methods are designed for unit testing. Passing the current user or
 * screen context allows pure execution in isolation.
 */
class WidgetVisibilityManager
{
    /**
     * Hidden widget identifiers keyed by id.
     *
     * @var array<string, bool>
     */
    private static array $hidden_widgets = [];

    /**
     * Retrieve IDs for widgets hidden during filtering.
     *
     * @return array<string, bool> Map of widget ids to hidden state.
     */
    public static function get_hidden_widgets(): array
    {
        return self::$hidden_widgets;
    }

    /**
     * Register WordPress hooks for widget visibility and admin notices.
     *
     * @return void
     */
    public static function register(): void
    {
        add_action('wp_dashboard_setup', [self::class, 'filter_visible_widgets'], 99);
        add_action('admin_notices', [self::class, 'render_admin_notices']);
        add_action('admin_notices', [self::class, 'render_empty_state_notice'], 100);
    }

    /**
     * Queue a transient admin notice.
     *
     * @param string $message Notice content.
     * @param string $type    Notice type (success, error, info).
     * @return void
     */
    public static function add_admin_notice(string $message, string $type = 'success'): void
    {
        $notices = get_transient('ap_admin_notices');
        if (!is_array($notices)) {
            $notices = [];
        }
        $notices[] = ['message' => wp_kses_post($message), 'type' => $type];
        set_transient('ap_admin_notices', $notices, MINUTE_IN_SECONDS);
    }

    /**
     * Output any queued admin notices.
     *
     * @return void
     */
    public static function render_admin_notices(): void
    {
        $notices = get_transient('ap_admin_notices');
        if (empty($notices)) {
            return;
        }
        foreach ($notices as $notice) {
            printf(
                '<div class="notice notice-%s"><p>%s</p></div>',
                esc_attr($notice['type']),
                $notice['message']
            );
        }
        delete_transient('ap_admin_notices');
    }

    /**
     * Normalize a role list into a de-duplicated, lower-cased array.
     *
     * @param mixed $roles Role list in various formats.
     */
    private static function normalizeRoleList($roles): array
    {
        if (is_string($roles)) {
            $s = trim($roles);
            if ($s !== '' && ($s[0] === '[' || $s[0] === '{')) {
                $decoded = json_decode($s, true);
                $roles   = is_array($decoded) ? $decoded : array_map('trim', explode(',', $s));
            } else {
                $roles = array_map('trim', explode(',', $s));
            }
        } elseif ($roles instanceof \Traversable) {
            $roles = iterator_to_array($roles);
        }
        if (!is_array($roles)) {
            $roles = [];
        }
        $roles = array_values(array_unique(array_filter(array_map(
            static fn($r) => strtolower(trim((string) $r)),
            $roles
        ))));
        return $roles;
    }

    /**
     * Retrieve visibility rules for dashboard widgets.
     *
     * Each array key is a widget id. The value may include:
     * - capability: required capability to view the widget.
     * - exclude_roles: list or map of roles that should not see the widget. When
     *   using an associative array the value may include `notice` and `type` for
     *   per-role admin messages.
     * - allowed_roles: explicit list of roles permitted to view the widget.
     *
     * @return array Filtered visibility configuration.
     */
    public static function get_visibility_rules(): array
    {
        $rules = [];

        // Include default WordPress dashboard widgets and their capabilities.
        $rules += [
            'dashboard_activity'    => ['capability' => 'edit_posts'],
            'dashboard_quick_press' => ['capability' => 'edit_posts'],
            'dashboard_site_health' => ['capability' => 'view_site_health_checks'],
            'artpulse_analytics_widget' => ['capability' => 'view_analytics'],
        ];

        // Build role-based visibility from the registered widget → role map.
        if (class_exists(\ArtPulse\Core\DashboardWidgetRegistry::class)) {
            \ArtPulse\Core\DashboardWidgetRegistry::init();
            $map   = \ArtPulse\Core\DashboardWidgetRegistry::get_role_widget_map();
            $roles = array_keys($map);
            $defs  = \ArtPulse\Core\DashboardWidgetRegistry::get_all();

            $allowed = [];
            foreach ($map as $role => $widgets) {
                foreach ($widgets as $item) {
                    $id = sanitize_key($item['id'] ?? '');
                    if (!$id) {
                        continue;
                    }
                    $allowed[$id][$role] = true;
                }
            }

            foreach ($allowed as $id => $allow_roles) {
                $rule      = $rules[$id] ?? [];
                $allow     = array_keys($allow_roles);
                $exclude   = array_values(array_diff($roles, $allow));
                if ($exclude) {
                    $rule['exclude_roles'] = $exclude;
                }
                if ($allow) {
                    $rule['allowed_roles'] = $allow;
                }
                if (!empty($defs[$id]['capability'])) {
                    $rule['capability'] = sanitize_text_field($defs[$id]['capability']);
                }
                $rules[$id] = $rule;
            }
        }

        // Merge in any saved configuration from the database.
        $saved = OptionUtils::get_array_option('artpulse_widget_roles');
        $saved = is_array($saved) ? $saved : [];
        foreach ($saved as $widget => $config) {
            $widget = sanitize_key($widget);
            $config = is_array($config) ? $config : [];

            $merged = [];

            if (isset($config['capability'])) {
                $cap = sanitize_text_field($config['capability']);
                if ($cap !== '') {
                    $merged['capability'] = $cap;
                }
            }

            if (isset($config['exclude_roles'])) {
                $roles = self::normalizeRoleList($config['exclude_roles']);
                if ($roles) {
                    $merged['exclude_roles'] = $roles;
                }
            }

            if (isset($config['allowed_roles'])) {
                $roles = self::normalizeRoleList($config['allowed_roles']);
                if ($roles) {
                    $merged['allowed_roles'] = $roles;
                }
            }

            if ($merged) {
                $rules[$widget] = array_merge($rules[$widget] ?? [], $merged);
            }
        }

        /**
         * Allow plugins to register additional visibility rules.
         * Plugins may filter 'ap_dashboard_widget_visibility_rules' to add or
         * modify entries without touching core defaults.
         *
         * @param array $rules Default visibility configuration.
         * @return array Filtered rules.
         */
        return apply_filters('ap_dashboard_widget_visibility_rules', $rules);
    }

    /**
     * Determine if a widget should be visible to a user.
     */
    public static function isVisible(string $widget, ?int $user_id = null): bool
    {
        $preview = isset($_GET['ap_preview_role']) ? sanitize_key($_GET['ap_preview_role']) : null;
        $previewing = in_array($preview, ['member', 'artist', 'organization'], true);

        if (current_user_can('manage_options') && !$previewing) {
            return true;
        }

        $user = $user_id !== null ? get_userdata($user_id) : wp_get_current_user();
        if (!($user instanceof \WP_User) || !$user->exists()) {
            return false;
        }

        $roles = self::normalizeRoleList($user->roles ?? []);

        $rules  = self::get_visibility_rules();
        $config = $rules[$widget] ?? [];

        $cap     = $config['capability'] ?? null;
        $exclude = self::normalizeRoleList($config['exclude_roles'] ?? []);
        $allowed = self::normalizeRoleList($config['allowed_roles'] ?? []);

        if ($cap && !user_can($user, $cap)) {
            return false;
        }
        if ($allowed && empty(array_intersect($roles, $allowed))) {
            return false;
        }
        if ($exclude && array_intersect($roles, $exclude)) {
            return false;
        }

        return true;
    }

    /**
     * Remove widgets hidden for the current user and track state.
     *
     * Passing a \WP_User instance allows unit testing without relying on the
     * global user state.
     *
     * @param \WP_User|null $user Optional user to evaluate. Defaults to the
     * current user.
     * @return void
     */
    public static function filter_visible_widgets($user = null): void
    {
        if (null !== $user && !($user instanceof \WP_User)) {
            return;
        }

        $preview = isset($_GET['ap_preview_role']) ? sanitize_key($_GET['ap_preview_role']) : null;
        $preview_valid = $preview && in_array($preview, array('member', 'artist', 'organization'), true);
        if (current_user_can('manage_options') && !$preview_valid) {
            self::$hidden_widgets = array();
            return;
        }

        $current_user = $user instanceof \WP_User ? $user : wp_get_current_user();
        if (!($current_user instanceof \WP_User) || !$current_user->exists()) {
            return;
        }

        $roles = self::normalizeRoleList($current_user->roles ?? []);

        self::$hidden_widgets = [];

        $rules = self::get_visibility_rules();

        foreach ($rules as $widget => $config) {
            $cap         = $config['capability'] ?? null;
            $exclude_raw = $config['exclude_roles'] ?? [];
            $allowed     = self::normalizeRoleList($config['allowed_roles'] ?? []);
            $notice_roles = is_array($exclude_raw) && array_keys($exclude_raw) !== range(0, count($exclude_raw) - 1)
                ? $exclude_raw
                : [];
            $exclude = $notice_roles ? self::normalizeRoleList(array_keys($notice_roles)) : self::normalizeRoleList($exclude_raw);

            $hide = false;
            if ($cap && !user_can($current_user, $cap)) {
                $hide = true;
            }

            if ($allowed && empty(array_intersect($roles, $allowed))) {
                $hide = true;
            }

            foreach ($roles as $role) {
                if (isset($notice_roles[$role])) {
                    $hide = true;
                    $msg  = $notice_roles[$role]['notice'] ?? '';
                    $type = $notice_roles[$role]['type'] ?? 'info';
                    if ($msg) {
                        self::add_admin_notice($msg, $type);
                    }
                } elseif (in_array($role, $exclude, true)) {
                    $hide = true;
                }
            }

            if ($hide) {
                remove_meta_box($widget, 'dashboard', 'normal');
                self::$hidden_widgets[$widget] = true;
                do_action('ap_widget_hidden', $widget, $current_user->ID);
            }
        }
    }


    /**
     * Display a message if no widgets remain after filtering.
     *
     * @param object|null $screen Optional screen context.
     * @return void
     */
    public static function render_empty_state_notice($screen = null): void
    {
        $screen = $screen ?: (function_exists('get_current_screen') ? get_current_screen() : null);
        if (!$screen || $screen->id !== 'dashboard') {
            return;
        }

        global $wp_meta_boxes;
        $count = 0;
        if (isset($wp_meta_boxes['dashboard'])) {
            foreach ($wp_meta_boxes['dashboard'] as $ctx) {
                foreach ($ctx as $priority) {
                    $count += is_array($priority) ? count($priority) : 0;
                }
            }
        }

        if ($count === 0) {
            error_log('Dashboard empty for role ' . (wp_get_current_user()->roles[0] ?? 'unknown'));
            /**
             * Provide a help URL when the dashboard is empty.
             *
             * @param string $url Default empty string.
             */
            $url = apply_filters('ap_dashboard_empty_help_url', '');
            echo '<div class="notice notice-info is-dismissible"><p>' .
                esc_html__('No widgets available for your role. Contact admin to enable access.', 'artpulse');
            if ($url) {
                echo ' <a href="' . esc_url($url) . '" target="_blank">' . esc_html__('Learn more', 'artpulse') . '</a>';
            }
            echo '</p></div>';
        }
    }
}
