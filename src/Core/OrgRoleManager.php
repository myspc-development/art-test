<?php
namespace ArtPulse\Core;

class OrgRoleManager
{
    /**
     * Map org roles to capabilities.
     * @var array<string,array<int,string>>
     */
    private const CAPABILITIES = [
        'org_admin' => [
            'manage_org',
            'manage_events',
            'manage_users',
            'view_finance',
            'view_events',
            'view_analytics',
        ],
        'event_manager' => [
            'manage_events',
            'view_events',
            'view_analytics',
        ],
        'viewer' => [
            'view_events',
            'view_analytics',
        ],
    ];

    /**
     * Get a user's role for an organization.
     */
    public static function get_user_org_role(int $user_id, int $org_id): ?string
    {
        $user_org = intval(get_user_meta($user_id, 'ap_organization_id', true));
        if ($user_org !== $org_id) {
            return null;
        }
        $role = get_user_meta($user_id, 'ap_org_role', true);
        return $role ?: null;
    }

    /**
     * Determine if the current user has capability for an org.
     */
    public static function current_user_can(string $capability, int $org_id): bool
    {
        $user_id = get_current_user_id();
        $role    = self::get_user_org_role($user_id, $org_id);
        if (!$role || !isset(self::CAPABILITIES[$role])) {
            return false;
        }
        return in_array($capability, self::CAPABILITIES[$role], true);
    }
}
