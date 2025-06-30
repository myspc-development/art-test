<?php
namespace ArtPulse\Core;

class RoleAuditLogger
{
    public static function register(): void
    {
        add_action('admin_init', [self::class, 'maybe_install_table']);
    }

    public static function maybe_install_table(): void
    {
        global $wpdb;
        $table  = $wpdb->prefix . 'ap_role_audit';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists === $table) {
            return;
        }
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            org_id BIGINT NOT NULL,
            user_id BIGINT NOT NULL,
            admin_id BIGINT NOT NULL,
            old_roles TEXT NULL,
            new_roles TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY org_id (org_id)
        ) $charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function log(int $org_id, int $user_id, int $admin_id, array $old, array $new): void
    {
        global $wpdb;
        if (!isset($wpdb)) {
            return;
        }
        $table = $wpdb->prefix . 'ap_role_audit';
        $wpdb->insert($table, [
            'org_id'    => $org_id,
            'user_id'   => $user_id,
            'admin_id'  => $admin_id,
            'old_roles' => wp_json_encode($old),
            'new_roles' => wp_json_encode($new),
        ]);
    }
}
