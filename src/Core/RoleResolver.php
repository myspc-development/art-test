<?php
namespace ArtPulse\Core;

class RoleResolver
{
    public static function resolve(int $user_id = 0): string
    {
        // Allow preview via ?ap_preview_role=artist for admin users
        if (current_user_can('manage_options') && isset($_GET['ap_preview_role'])) {
            return sanitize_text_field($_GET['ap_preview_role']);
        }

        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        // Allow previewing another user's role via ?ap_preview_user=ID
        if (current_user_can('manage_options') && isset($_GET['ap_preview_user'])) {
            $preview = (int) $_GET['ap_preview_user'];
            if ($preview > 0) {
                $user_id = $preview;
            }
        }

        $user = get_userdata($user_id);
        if (!$user || empty($user->roles)) {
            return 'member';
        }

        $roles    = array_map('sanitize_key', $user->roles);
        $priority = ['member', 'artist', 'organization'];
        foreach ($priority as $r) {
            if (in_array($r, $roles, true)) {
                return $r;
            }
        }

        return $roles[0];
    }
}
