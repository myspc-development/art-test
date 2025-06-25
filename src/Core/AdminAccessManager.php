<?php
namespace ArtPulse\Core;

/**
 * Restrict access to wp-admin and hide the admin toolbar for non-admin users.
 */
class AdminAccessManager
{
    /**
     * Register hooks.
     */
    public static function register(): void
    {
        add_filter('show_admin_bar', [self::class, 'maybe_hide_admin_bar']);
        add_action('admin_init', [self::class, 'maybe_redirect_admin']);
    }

    /**
     * Hide the admin bar for users without manage_options capability.
     */
    public static function maybe_hide_admin_bar(bool $show): bool
    {
        if (!current_user_can('manage_options')) {
            return false;
        }
        return $show;
    }

    /**
     * Redirect non-admin users away from wp-admin.
     */
    public static function maybe_redirect_admin(): void
    {
        if (wp_doing_ajax() || !is_user_logged_in() || current_user_can('manage_options')) {
            return;
        }

        $user   = wp_get_current_user();
        $roles  = (array) $user->roles;
        $target = Plugin::get_user_dashboard_url();

        if (in_array('organization', $roles, true)) {
            $target = Plugin::get_org_dashboard_url();
        }

        wp_safe_redirect($target);
        exit;
    }
}
