<?php
namespace ArtPulse\Core;

class LoginRedirectManager
{
    public static function register(): void
    {
        add_filter('login_redirect', [self::class, 'handle'], 10, 3);
    }

    public static function handle($redirect_to, $requested_redirect_to, $user)
    {
        if (is_wp_error($user)) {
            return $redirect_to;
        }

        if (current_user_can('view_wp_admin') || ap_wp_admin_access_enabled()) {
            return $redirect_to;
        }

        if (user_can($user, 'organization')) {
            return Plugin::get_org_dashboard_url();
        }

        if (user_can($user, 'artist')) {
            return Plugin::get_artist_dashboard_url();
        }

        return Plugin::get_user_dashboard_url();
    }
}
