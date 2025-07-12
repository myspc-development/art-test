<?php
namespace ArtPulse\Core;

class DashboardController {
    public static function render_for_user($user_id) {
        $role = self::get_role($user_id);

        switch ($role) {
            case 'artist':
                return ArtistDashboardHome::render($user_id);
            case 'organization':
                return OrgDashboardManager::render($user_id);
            case 'member':
            default:
                return UserDashboardManager::render($user_id);
        }
    }

    public static function get_role($user_id) {
        $user = get_user_by('id', $user_id);
        return $user ? ($user->roles[0] ?? 'member') : 'guest';
    }
}
