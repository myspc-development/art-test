<?php
namespace ArtPulse\Dashboard;

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
     * Retrieve visibility rules for dashboard widgets.
     *
     * Each array key is a widget id. The value may include:
     * - capability: required capability to view the widget.
     * - exclude_roles: list or map of roles that should not see the widget. When
     *   using an associative array the value may include `notice` and `type` for
     *   per-role admin messages.
     *
     * @return array Filtered visibility configuration.
     */
    public static function get_visibility_rules(): array
    {
        $rules = [
            'artpulse_analytics_widget' => [
                'capability' => 'view_analytics',
            ],
        ];

        /**
         * Allow plugins to register additional visibility rules.
         *
         * @param array $rules Default visibility configuration.
         * @return array Filtered rules.
         */
        return apply_filters('ap_dashboard_widget_visibility_rules', $rules);
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

        $current_user = $user instanceof \WP_User ? $user : wp_get_current_user();
        if (!($current_user instanceof \WP_User) || !$current_user->exists()) {
            return;
        }

        $roles = (array) $current_user->roles;

        self::$hidden_widgets = [];

        $rules = self::get_visibility_rules();

        foreach ($rules as $widget => $config) {
            $cap          = $config['capability']    ?? null;
            $exclude      = $config['exclude_roles'] ?? [];
            $notice_roles = is_array($exclude) ? $exclude : [];

            $hide = false;
            if ($cap && !user_can($current_user, $cap)) {
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
                } elseif (in_array($role, (array) $exclude, true)) {
                    $hide = true;
                }
            }

            if ($hide) {
                remove_meta_box($widget, 'dashboard', 'normal');
                self::$hidden_widgets[$widget] = true;
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
            /**
             * Provide a help URL when the dashboard is empty.
             *
             * @param string $url Default empty string.
             */
            $url = apply_filters('ap_dashboard_empty_help_url', '');
            echo '<div class="notice notice-info"><p>' .
                esc_html__('No dashboard content available.', 'artpulse');
            if ($url) {
                echo ' <a href="' . esc_url($url) . '" target="_blank">' . esc_html__('Learn more', 'artpulse') . '</a>';
            }
            echo '</p></div>';
        }
    }
}
