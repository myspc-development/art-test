<?php
namespace ArtPulse\Core;

use ArtPulse\Core\RoleAuditLogger;
use ArtPulse\Core\MultiOrgRoles;

class OrgRoleManager
{
    /**
     * Default role definitions loaded when an organization has none saved.
     *
     * @return array<string,array<string,mixed>>
     */
    private static function default_roles(): array
    {
        $file = dirname(ARTPULSE_PLUGIN_FILE) . '/config/roles.php';
        if (file_exists($file)) {
            $roles = include $file;
            if (is_array($roles)) {
                return $roles;
            }
        }

        return [
            'admin'   => [
                'name'        => 'Org Admin',
                'description' => 'Full access',
            ],
            'curator' => [
                'name'        => 'Curator',
                'description' => 'Manages exhibitions',
            ],
            'editor'  => [
                'name'        => 'Content Editor',
                'description' => 'Edits org posts',
            ],
        ];
    }

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
            $roles = self::default_roles();
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
    public static function get_user_roles(int $user_id, int $org_id = 0): array
    {
        if (!$org_id) {
            $org_id = absint(get_user_meta($user_id, 'ap_organization_id', true));
        }

        if ($org_id) {
            $table_roles = MultiOrgRoles::get_user_roles($user_id, $org_id);
            if (!empty($table_roles)) {
                return array_values($table_roles);
            }
        }

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
    public static function assign_roles(int $user_id, array $roles, int $org_id = 0): void
    {
        if (!$org_id) {
            $org_id = absint(get_user_meta($user_id, 'ap_organization_id', true));
        }

        $old = self::get_user_roles($user_id, $org_id);
        if ($org_id) {
            MultiOrgRoles::assign_roles($user_id, $org_id, $roles);
        }
        RoleAuditLogger::log($org_id, $user_id, get_current_user_id(), $old, $roles);
    }

    /**
     * Determine if a user has a capability in an organization.
     */
    public static function user_can(int $user_id, int $org_id, string $capability): bool
    {
        $roles = self::get_roles($org_id);
        $user_roles = self::get_user_roles($user_id, $org_id);
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
        $roles = MultiOrgRoles::get_user_roles($user_id, $org_id);
        return $roles[0] ?? null;
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
