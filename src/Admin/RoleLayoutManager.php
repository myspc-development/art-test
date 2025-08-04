<?php
namespace ArtPulse\Admin;


/**
 * Manage widget layouts for roles.
 */
class RoleLayoutManager
{
    /**
     * Get the layout for a given role.
     */
    public static function get_layout_for_role(string $role): array
    {
        return UserLayoutManager::get_role_layout($role)['layout'];
    }
}

