<?php
namespace ArtPulse\Core;

class MultiOrgRoles
{
    public static function register(): void
    {
        add_action('init', [self::class, 'maybe_install_table']);
    }

    public static function maybe_install_table(): void
    {
        global $wpdb;
        $table  = $wpdb->prefix . 'ap_org_roles';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            self::install_table();
        }
    }

    public static function install_table(): void
    {
        global $wpdb;
        $table   = $wpdb->prefix . 'ap_org_roles';
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table (
            user_id BIGINT NOT NULL,
            org_id BIGINT NOT NULL,
            role varchar(191) NOT NULL,
            site_id BIGINT NULL,
            assigned_at DATETIME NULL,
            PRIMARY KEY  (user_id, org_id, role)
        ) $charset;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function assign_roles(int $user_id, int $org_id, array $roles): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_org_roles';

        $wpdb->delete($table, ['user_id' => $user_id, 'org_id' => $org_id]);
        foreach ($roles as $role) {
            $wpdb->insert(
                $table,
                [
                    'user_id'     => $user_id,
                    'org_id'      => $org_id,
                    'role'        => sanitize_key($role),
                    'site_id'     => is_multisite() ? get_current_blog_id() : null,
                    'assigned_at' => current_time('mysql'),
                ]
            );
        }
    }

    public static function get_user_roles(int $user_id, int $org_id): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_org_roles';

        return (array) $wpdb->get_col(
            $wpdb->prepare(
                "SELECT role FROM $table WHERE user_id = %d AND org_id = %d",
                $user_id,
                $org_id
            )
        );
    }
}

function ap_user_has_org_role(int $user_id, int $org_id, ?string $role = null): bool
{
    global $wpdb;
    $table = $wpdb->prefix . 'ap_org_roles';
    $sql   = "SELECT COUNT(*) FROM $table WHERE user_id = %d AND org_id = %d";
    $args  = [$user_id, $org_id];
    if ($role) {
        $sql  .= " AND role = %s";
        $args[] = sanitize_key($role);
    }

    $count = (int) $wpdb->get_var($wpdb->prepare($sql, ...$args));
    return $count > 0;
}

