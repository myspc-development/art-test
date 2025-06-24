<?php
namespace ArtPulse\Core;

/**
 * Exposes custom actions for external integrations.
 */
class IntegrationHooks
{
    /**
     * Register hooks.
     */
    public static function register(): void
    {
        // Fire after new users have been assigned their default role.
        add_action('user_register', [self::class, 'onUserRegister'], 20, 1);
    }

    /**
     * Triggered when a new user account is created.
     */
    public static function onUserRegister(int $user_id): void
    {
        do_action('ap_user_registered', $user_id);
    }

    /**
     * Triggered whenever a membership level is upgraded.
     */
    public static function membershipUpgraded(int $user_id, string $level): void
    {
        do_action('ap_membership_upgraded', $user_id, $level);
    }

    /**
     * Triggered whenever a membership level is downgraded.
     */
    public static function membershipDowngraded(int $user_id, string $level): void
    {
        do_action('ap_membership_downgraded', $user_id, $level);
    }

    /**
     * Triggered when a membership expires.
     */
    public static function membershipExpired(int $user_id): void
    {
        do_action('ap_membership_expired', $user_id);
    }
}
