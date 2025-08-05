<?php
namespace ArtPulse\Crm;

class DonationModel
{
    public static function add(int $org_id, int $user_id, float $amount, string $method = ''): void
    {
        if ($org_id <= 0) {
            throw new \InvalidArgumentException('Organization ID must be a positive integer.');
        }

        if ($user_id <= 0) {
            throw new \InvalidArgumentException('User ID must be a positive integer.');
        }

        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than zero.');
        }

        if ($method === '') {
            throw new \InvalidArgumentException('Method cannot be empty.');
        }

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

    /**
     * Query donations with optional date range filters.
     *
     * @param int    $org_id Organization ID.
     * @param string $from   Optional start date (Y-m-d).
     * @param string $to     Optional end date (Y-m-d).
     * @return array<int, array<string, mixed>>
     */
    public static function query(int $org_id, string $from = '', string $to = ''): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_donations';
        $where = $wpdb->prepare('org_id = %d', $org_id);
        if ($from !== '') {
            $where .= $wpdb->prepare(' AND donated_at >= %s', $from . ' 00:00:00');
        }
        if ($to !== '') {
            $where .= $wpdb->prepare(' AND donated_at <= %s', $to . ' 23:59:59');
        }
        return $wpdb->get_results("SELECT * FROM $table WHERE $where ORDER BY donated_at DESC", ARRAY_A);
    }
}
