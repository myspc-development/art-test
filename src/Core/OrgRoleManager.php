<?php
namespace ArtPulse\Core;

use ArtPulse\Core\RoleAuditLogger;

class OrgRoleManager
{
    /**
     * Map org roles to capabilities.
     * @var array<string,array<int,string>>
     */
    private const DEFAULT_ROLES = [
        'org_admin' => [
            'manage_org',
            'manage_events',
            'manage_users',
            'manage_finances',
            'export_data',
            'view_events',
            'view_finance',
            'view_analytics',
        ],
        'event_manager' => [
            'manage_events',
            'view_events',
            'view_analytics',
        ],
        'finance_manager' => [
            'view_finance',
            'manage_finances',
            'export_data',
        ],
        'viewer' => [
            'view_events',
            'view_analytics',
        ],
    ];

    /**
     * List of all capabilities that can be assigned.
     * @var array<int,string>
     */
    public const ALL_CAPABILITIES = [
        'view_events', 'edit_events', 'delete_events',
        'view_finance', 'manage_finances',
        'manage_users', 'assign_roles',
        'export_data', 'view_analytics', 'manage_org',
    ];

    /**
     * Retrieve role definitions for an organization.
     *
     * @param int $org_id Organization post ID.
     * @return array<string,array<string,mixed>>
     */
    public static function get_roles(int $org_id): array
    {
        $roles = get_post_meta($org_id, 'ap_org_roles', true);
        if (!is_array($roles) || empty($roles)) {
            $roles = [];
            foreach (self::DEFAULT_ROLES as $key => $caps) {
                $roles[$key] = [
                    'name' => ucwords(str_replace('_', ' ', $key)),
                    'caps' => $caps,
                ];
            }
        }
        return $roles;
    }

    /**
     * Persist role definitions for an organization.
     *
     * @param int   $org_id Organization post ID.
     * @param array $roles  Role map.
     */
    public static function save_roles(int $org_id, array $roles): void
    {
        update_post_meta($org_id, 'ap_org_roles', $roles);
    }

    /**
     * Get roles assigned to a user.
     *
     * @param int $user_id User ID.
     * @return array<int,string>
     */
    public static function get_user_roles(int $user_id): array
    {
        $roles = get_user_meta($user_id, 'ap_org_roles', true);
        if (is_string($roles)) {
            $roles = [$roles];
        }
        if (!is_array($roles) || empty($roles)) {
            $role = get_user_meta($user_id, 'ap_org_role', true);
            return $role ? [$role] : [];
        }
        return array_values($roles);
    }

    /**
     * Assign roles to a user.
     */
    public static function assign_roles(int $user_id, array $roles): void
    {
        $old = self::get_user_roles($user_id);
        update_user_meta($user_id, 'ap_org_roles', array_values(array_unique($roles)));
        if (!empty($roles)) {
            update_user_meta($user_id, 'ap_org_role', $roles[0]);
        }
        $org_id = intval(get_user_meta($user_id, 'ap_organization_id', true));
        RoleAuditLogger::log($org_id, $user_id, get_current_user_id(), $old, $roles);
    }

    /**
     * Determine if a user has a capability in an organization.
     */
    public static function user_can(int $user_id, int $org_id, string $capability): bool
    {
        $user_org = intval(get_user_meta($user_id, 'ap_organization_id', true));
        if ($user_org !== $org_id) {
            return false;
        }
        $roles = self::get_roles($org_id);
        $user_roles = self::get_user_roles($user_id);
        foreach ($user_roles as $r) {
            if (!empty($roles[$r]) && in_array($capability, $roles[$r]['caps'], true)) {
                return true;
            }
        }
        return false;
    }

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
        if (!$role) {
            return false;
        }
        $roles = self::get_roles($org_id);
        if (empty($roles[$role])) {
            return false;
        }
        return in_array($capability, $roles[$role]['caps'], true);
    }
}
