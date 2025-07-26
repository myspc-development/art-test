<?php
namespace ArtPulse\Dashboard;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles dashboard widget visibility and related notices.
 */
class WidgetVisibilityManager
{
    /**
     * Register WordPress hooks for widget visibility and admin notices.
     */
    public static function register(): void
    {
        add_action('wp_dashboard_setup', [self::class, 'filter_visible_widgets'], 99);
        add_action('admin_notices', [self::class, 'render_admin_notices']);
        add_action('admin_init', [self::class, 'handle_editor_notice_dismiss']);
        add_action('admin_notices', [self::class, 'render_org_editor_notice']);
        add_action('admin_notices', [self::class, 'render_empty_state_notice'], 100);
    }

    /**
     * Queue a transient admin notice.
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
     */
    public static function get_visibility_rules(): array
    {
        $rules = [
            'artpulse_analytics_widget' => [
                'capability'    => 'view_analytics',
                'exclude_roles' => [
                    'org_editor' => [
                        'notice' => __('Analytics are available to organization managers only.', 'artpulse'),
                        'type'   => 'info',
                    ],
                    'org_viewer',
                ],
            ],
        ];

        /**
         * Allow plugins to register additional visibility rules.
         *
         * @param array $rules Default visibility configuration.
         */
        return apply_filters('ap_dashboard_widget_visibility_rules', $rules);
    }

    /**
     * Remove widgets hidden for the current user and track state.
     */
    public static function filter_visible_widgets(): void
    {
        $current_user = wp_get_current_user();
        $roles        = (array) $current_user->roles;

        global $ap_hidden_widgets;
        $ap_hidden_widgets = [];

        $rules = self::get_visibility_rules();

        foreach ($rules as $widget => $config) {
            $cap          = $config['capability']    ?? null;
            $exclude      = $config['exclude_roles'] ?? [];
            $notice_roles = is_array($exclude) ? $exclude : [];

            $hide = false;
            if ($cap && !current_user_can($cap)) {
                $hide = true;
            }

            foreach ($roles as $role) {
                if (isset($notice_roles[$role])) {
                    $hide = true;
                    $msg  = $notice_roles[$role]['notice'] ?? '';
                    $type = $notice_roles[$role]['type'] ?? 'info';
                    if ($msg) {
                        if ($role === 'org_editor') {
                            if (!get_user_meta($current_user->ID, 'ap_dismiss_org_editor_notice', true)) {
                                update_user_meta($current_user->ID, 'ap_org_editor_notice_pending', $msg);
                            }
                        } else {
                            self::add_admin_notice($msg, $type);
                        }
                    }
                } elseif (in_array($role, (array) $exclude, true)) {
                    $hide = true;
                }
            }

            if ($hide) {
                remove_meta_box($widget, 'dashboard', 'normal');
                $ap_hidden_widgets[$widget] = true;
            }
        }
    }

    /**
     * Process dismissal of the org editor notice.
     */
    public static function handle_editor_notice_dismiss(): void
    {
        if (isset($_GET['ap_dismiss_editor_notice'])) {
            update_user_meta(get_current_user_id(), 'ap_dismiss_org_editor_notice', 1);
            delete_user_meta(get_current_user_id(), 'ap_org_editor_notice_pending');
            wp_safe_redirect(remove_query_arg('ap_dismiss_editor_notice'));
            exit;
        }
    }

    /**
     * Render the temporary notice for org editors.
     */
    public static function render_org_editor_notice(): void
    {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || $screen->id !== 'dashboard') {
            return;
        }

        $user_id = get_current_user_id();
        $msg     = get_user_meta($user_id, 'ap_org_editor_notice_pending', true);
        if (!$msg || get_user_meta($user_id, 'ap_dismiss_org_editor_notice', true)) {
            return;
        }

        $dismiss = add_query_arg('ap_dismiss_editor_notice', '1');
        echo '<div class="notice notice-info is-dismissible"><p>' . esc_html($msg) .
            ' <a href="' . esc_url($dismiss) . '">' . esc_html__('Dismiss', 'artpulse') . '</a></p></div>';
        delete_user_meta($user_id, 'ap_org_editor_notice_pending');
    }

    /**
     * Display a message if no widgets remain after filtering.
     */
    public static function render_empty_state_notice(): void
    {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
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
