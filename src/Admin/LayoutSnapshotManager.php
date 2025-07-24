<?php
namespace ArtPulse\Admin;

/**
 * Manage dashboard layout snapshots for users.
 */
class LayoutSnapshotManager {
    public const META_KEY = 'ap_dashboard_layout_snapshots';

    public static function snapshot(int $user_id, string $role): void {
        $layout = UserLayoutManager::get_user_layout($user_id);
        if (empty($layout)) {
            return;
        }
        $snapshots = get_user_meta($user_id, self::META_KEY, true);
        if (!is_array($snapshots)) {
            $snapshots = [];
        }
        $snapshots[] = [
            'role' => sanitize_key($role),
            'time' => time(),
            'layout' => $layout,
        ];
        update_user_meta($user_id, self::META_KEY, $snapshots);
    }

    public static function restore_last(int $user_id): bool {
        $snapshots = get_user_meta($user_id, self::META_KEY, true);
        if (!is_array($snapshots) || empty($snapshots)) {
            return false;
        }
        $last = array_pop($snapshots);
        update_user_meta($user_id, self::META_KEY, $snapshots);
        if (isset($last['layout']) && is_array($last['layout'])) {
            UserLayoutManager::save_layout($user_id, $last['layout']);
            return true;
        }
        return false;
    }

    public static function get_snapshots(int $user_id): array {
        $snapshots = get_user_meta($user_id, self::META_KEY, true);
        return is_array($snapshots) ? $snapshots : [];
    }
}
