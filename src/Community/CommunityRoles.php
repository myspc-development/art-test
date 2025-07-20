<?php
namespace ArtPulse\Community;

/**
 * Role helper for community layer.
 */
class CommunityRoles
{
    public const PUBLIC_USER    = 'public_user';
    public const VERIFIED_ARTIST = 'verified_artist';
    public const MODERATOR      = 'moderator';
    public const ADMINISTRATOR  = 'administrator';

    /**
     * Return the community role for a user.
     */
    public static function get_role(int $user_id): string
    {
        $role = get_user_meta($user_id, 'community_role', true);
        if (!$role) {
            $user = get_user_by('id', $user_id);
            if ($user && user_can($user, 'administrator')) {
                return self::ADMINISTRATOR;
            }
            return self::PUBLIC_USER;
        }
        if (!in_array($role, [self::PUBLIC_USER, self::VERIFIED_ARTIST, self::MODERATOR, self::ADMINISTRATOR], true)) {
            return self::PUBLIC_USER;
        }
        return $role;
    }

    /**
     * Check if user role matches allowed roles.
     */
    public static function has_role(int $user_id, array $allowed): bool
    {
        return in_array(self::get_role($user_id), $allowed, true);
    }

    public static function can_post_thread(int $user_id): bool
    {
        return self::has_role($user_id, [self::VERIFIED_ARTIST, self::MODERATOR, self::ADMINISTRATOR]);
    }

    public static function can_tag(int $user_id): bool
    {
        return self::has_role($user_id, [self::VERIFIED_ARTIST, self::MODERATOR, self::ADMINISTRATOR]);
    }

    public static function can_moderate(int $user_id): bool
    {
        return self::has_role($user_id, [self::MODERATOR, self::ADMINISTRATOR]);
    }

    public static function can_block(int $user_id): bool
    {
        return self::can_moderate($user_id);
    }

    /**
     * Assign default community role on registration.
     */
    public static function assign_default_role(int $user_id): void
    {
        if (!get_user_meta($user_id, 'community_role', true)) {
            update_user_meta($user_id, 'community_role', self::PUBLIC_USER);
        }
    }

    public static function register(): void
    {
        add_action('user_register', [self::class, 'assign_default_role']);
    }
}
