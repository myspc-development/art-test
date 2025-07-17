<?php
namespace ArtPulse\Community;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class ReferralManager
{
    public static function register(): void
    {
        add_action('init', [self::class, 'maybe_install_table']);
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function maybe_install_table(): void
    {
        global $wpdb;
        $table  = $wpdb->prefix . 'ap_referrals';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            $charset = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $table (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                referrer_id BIGINT NOT NULL,
                code VARCHAR(20) NOT NULL,
                redeemed_by BIGINT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                redeemed_at DATETIME NULL,
                UNIQUE KEY code (code),
                KEY referrer_id (referrer_id)
            ) $charset;";
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/referral/redeem', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'redeem'],
            'permission_callback' => fn() => is_user_logged_in(),
            'args'                => [
                'code' => ['type' => 'string', 'required' => true],
            ],
        ]);
    }

    public static function create_code(int $user_id): string
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_referrals';
        do {
            $code = wp_generate_password(8, false);
            $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE code = %s", $code));
        } while ($exists);

        $wpdb->insert($table, [
            'referrer_id' => $user_id,
            'code'        => $code,
            'created_at'  => current_time('mysql'),
        ]);

        return $code;
    }

    public static function redeem(WP_REST_Request $req): WP_REST_Response|WP_Error
    {
        global $wpdb;
        $code    = sanitize_text_field($req->get_param('code'));
        $user_id = get_current_user_id();
        $table   = $wpdb->prefix . 'ap_referrals';

        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE code = %s", $code));
        if (!$row) {
            return new WP_Error('invalid_code', 'Invalid code', ['status' => 404]);
        }
        if ($row->redeemed_by) {
            return new WP_Error('already_redeemed', 'Code already redeemed', ['status' => 400]);
        }

        $wpdb->update($table, [
            'redeemed_by' => $user_id,
            'redeemed_at' => current_time('mysql'),
        ], ['id' => $row->id]);

        do_action('ap_referral_redeemed', (int) $row->referrer_id);

        return rest_ensure_response(['redeemed' => true]);
    }

    public static function get_referral_count(int $user_id): int
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_referrals';
        return (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE referrer_id = %d AND redeemed_by IS NOT NULL", $user_id));
    }
}
