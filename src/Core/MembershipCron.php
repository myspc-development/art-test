<?php
namespace ArtPulse\Core;

class MembershipCron
{
    public static function register()
    {
        add_action('ap_daily_expiry_check', [self::class, 'checkExpiries']);
    }

    public static function checkExpiries()
    {
        $users = get_users([
            'meta_key'     => 'ap_membership_expires',
            'meta_compare' => 'EXISTS',
            'number'       => 500,
        ]);

        $now         = current_time('timestamp');
        $warning_day = strtotime('+7 days', $now);

        foreach ($users as $user) {
            $expires = intval(get_user_meta($user->ID, 'ap_membership_expires', true));
            if (!$expires) {
                continue;
            }

            if ($expires === $warning_day) {
                MembershipNotifier::sendExpiryWarningEmail($user);
            }

            if ($expires < $now) {
                update_user_meta($user->ID, 'ap_membership_level', 'Free');
            }
        }
    }
}
