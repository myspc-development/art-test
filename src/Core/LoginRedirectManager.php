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

        $roles = (array) $user->roles;

        if (in_array('organization', $roles, true)) {
            return Plugin::get_org_dashboard_url();
        }

        if (in_array('artist', $roles, true)) {
            return Plugin::get_artist_dashboard_url();
        }

        return Plugin::get_user_dashboard_url();
    }
}
