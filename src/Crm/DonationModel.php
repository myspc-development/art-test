<?php
namespace ArtPulse\Crm;

class DonationModel
{
    public static function add(int $org_id, int $user_id, float $amount, string $method): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_donations';
        $wpdb->insert($table, [
            'org_id'     => $org_id,
            'user_id'    => $user_id,
            'amount'     => $amount,
            'method'     => sanitize_text_field($method),
            'donated_at' => current_time('mysql'),
        ]);

        do_action('ap_donation_recorded', $org_id, $user_id, $amount);
    }

    public static function get_by_org(int $org_id): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_donations';
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE org_id = %d ORDER BY donated_at DESC", $org_id), ARRAY_A);
    }

    public static function get_summary(int $org_id): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_donations';
        $row = $wpdb->get_row($wpdb->prepare("SELECT COUNT(DISTINCT user_id) as donors, SUM(amount) as total FROM $table WHERE org_id = %d", $org_id));
        return [
            'count' => intval($row->donors),
            'total' => floatval($row->total),
        ];
    }
}
