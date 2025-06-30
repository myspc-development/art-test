<?php
namespace ArtPulse\Core;

class DelegatedAccessManager
{
    public static function register(): void
    {
        add_action('admin_init', [self::class, 'maybe_install_table']);
        add_action('ap_daily_expiry_check', [self::class, 'expire_access']);
    }

    public static function install_table(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_delegated_access';
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id BIGINT AUTO_INCREMENT,
            PRIMARY KEY (id),
            org_id BIGINT NOT NULL,
            email VARCHAR(100) NOT NULL,
            roles TEXT NULL,
            user_id BIGINT NULL,
            start_date DATE NULL,
            expiry_date DATE NULL,
            invitation_token VARCHAR(64) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY org_id (org_id),
            KEY email (email),
            KEY expiry_date (expiry_date),
            KEY status (status)
        ) $charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function maybe_install_table(): void
    {
        global $wpdb;
        $table  = $wpdb->prefix . 'ap_delegated_access';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            self::install_table();
        }
    }

    public static function invite(int $org_id, string $email, array $roles, string $expiry_date): string
    {
        $token = wp_generate_password(32, false, false);
        global $wpdb;
        $table = $wpdb->prefix . 'ap_delegated_access';
        $wpdb->insert($table, [
            'org_id'          => $org_id,
            'email'           => sanitize_email($email),
            'roles'           => wp_json_encode(array_values($roles)),
            'expiry_date'     => $expiry_date,
            'invitation_token'=> $token,
            'status'          => 'pending',
        ]);
        return $token;
    }

    public static function accept_invitation(string $token, int $user_id): bool
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_delegated_access';
        $row = $wpdb->get_row($wpdb->prepare("SELECT id FROM $table WHERE invitation_token = %s AND status = 'pending'", $token));
        if (!$row) {
            return false;
        }
        $wpdb->update($table, [
            'user_id'   => $user_id,
            'start_date'=> current_time('mysql'),
            'status'    => 'active',
        ], [ 'id' => $row->id ]);
        return true;
    }

    public static function expire_access(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_delegated_access';
        $today = date('Y-m-d');
        $wpdb->query($wpdb->prepare("UPDATE $table SET status = 'expired' WHERE status = 'active' AND expiry_date IS NOT NULL AND expiry_date < %s", $today));
    }
}
