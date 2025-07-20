<?php
namespace ArtPulse\Core;

class AccessControlManager
{
    public static function register()
    {
        add_action('template_redirect', [self::class,'checkAccess']);
    }

    public static function checkAccess()
    {
        if ( is_singular(['artpulse_event','artpulse_artwork']) ) {
            $user_id  = get_current_user_id();
            $level    = get_user_meta($user_id, 'ap_membership_level', true);
            $settings = get_option('artpulse_settings', []);
            $user     = wp_get_current_user();

            if (self::needsRedirect($user, $level, $settings)) {
                wp_redirect(home_url());
                exit;
            }
        }
    }

    /**
     * Determine if a user viewing a protected post should be redirected.
     */
    public static function needsRedirect(\WP_User $user, string $level, array $settings): bool
    {
        if (
            (!empty($settings['override_artist_membership']) && user_can($user, 'artist')) ||
            (!empty($settings['override_org_membership']) && user_can($user, 'organization')) ||
            (!empty($settings['override_member_membership']) && user_can($user, 'member'))
        ) {
            return false;
        }

        return $level === 'Free';
    }
}
