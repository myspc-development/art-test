<?php
namespace ArtPulse\Core;

class ReportSubscriptionManager
{
    public static function register(): void
    {
        add_action('init', [self::class, 'maybe_install_table']);
        add_filter('cron_schedules', [self::class, 'add_cron_schedules']);
        add_action('init', [self::class, 'schedule_cron']);
        add_action('ap_weekly_org_reports', [self::class, 'send_weekly_reports']);
        add_action('ap_monthly_org_reports', [self::class, 'send_monthly_reports']);
    }

    public static function install_table(): void
    {
        global $wpdb;
        $table   = $wpdb->prefix . 'ap_org_report_subscriptions';
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id INT AUTO_INCREMENT PRIMARY KEY,
            org_id INT,
            email VARCHAR(255),
            frequency VARCHAR(10),
            format VARCHAR(10),
            report_type VARCHAR(20),
            last_sent DATETIME,
            KEY org_id (org_id)
        ) $charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function maybe_install_table(): void
    {
        global $wpdb;
        $table  = $wpdb->prefix . 'ap_org_report_subscriptions';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            self::install_table();
        }
    }

    public static function add_cron_schedules(array $schedules): array
    {
        if (!isset($schedules['monthly'])) {
            $schedules['monthly'] = [
                'interval' => 30 * DAY_IN_SECONDS,
                'display'  => __('Once Monthly', 'artpulse'),
            ];
        }
        return $schedules;
    }

    public static function schedule_cron(): void
    {
        if (!wp_next_scheduled('ap_weekly_org_reports')) {
            wp_schedule_event(strtotime('next Monday 6am'), 'weekly', 'ap_weekly_org_reports');
        }
        if (!wp_next_scheduled('ap_monthly_org_reports')) {
            wp_schedule_event(strtotime('first day of next month 6am'), 'monthly', 'ap_monthly_org_reports');
        }
    }

    public static function send_weekly_reports(): void
    {
        self::send_reports('weekly');
    }

    public static function send_monthly_reports(): void
    {
        self::send_reports('monthly');
    }

    private static function send_reports(string $frequency): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_org_report_subscriptions';
        $subs  = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE frequency = %s", $frequency));
        foreach ($subs as $sub) {
            $data = [
                'Org ID' => $sub->org_id,
                'Type'   => $sub->report_type,
            ];
            if ($sub->report_type === 'grant' && $sub->format === 'pdf') {
                $path = \ArtPulse\Reporting\GrantReportBuilder::generate_pdf([
                    'title'   => 'Grant Report',
                    'summary' => [
                        'Org ID' => $sub->org_id,
                    ],
                    'events'  => [],
                    'donors'  => [],
                ]);
            } elseif ($sub->format === 'csv') {
                $path = \ArtPulse\Reporting\SnapshotBuilder::generate_csv([
                    'title' => 'Org Report',
                    'data'  => $data,
                ]);
            } else {
                $path = \ArtPulse\Reporting\SnapshotBuilder::generate_pdf([
                    'title' => 'Org Report',
                    'data'  => $data,
                ]);
            }
            if ($path && file_exists($path)) {
                wp_mail(
                    $sub->email,
                    __('Organization Report', 'artpulse'),
                    __('See attached report.', 'artpulse'),
                    [],
                    [$path]
                );
                unlink($path);
                $wpdb->update($table, ['last_sent' => current_time('mysql')], ['id' => $sub->id]);
            }
        }
    }
}
