<?php
namespace ArtPulse\Core;

class OrgRoleMetaMigration
{
    public static function maybe_migrate(): void
    {
        if (get_option('ap_org_roles_table_migrated')) {
            return;
        }

        $users = get_users([
            'fields'     => 'ids',
            'meta_query' => [
                'relation' => 'OR',
                ['key' => 'ap_org_role', 'compare' => 'EXISTS'],
                ['key' => 'ap_org_roles', 'compare' => 'EXISTS'],
            ],
        ]);

        foreach ($users as $uid) {
            $org_id = absint(get_user_meta($uid, 'ap_organization_id', true));
            if (!$org_id) {
                continue;
            }

            $roles = get_user_meta($uid, 'ap_org_roles', true);
            if (is_string($roles)) {
                $roles = [$roles];
            }
            if (!is_array($roles) || empty($roles)) {
                $role = get_user_meta($uid, 'ap_org_role', true);
                $roles = $role ? [$role] : [];
            }

            if (!empty($roles)) {
                MultiOrgRoles::assign_roles($uid, $org_id, $roles);
            }

            delete_user_meta($uid, 'ap_org_role');
            delete_user_meta($uid, 'ap_org_roles');
        }

        update_option('ap_org_roles_table_migrated', 1);
    }
}
