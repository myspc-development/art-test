<?php
namespace ArtPulse\Admin;

class PortfolioSyncLogsPage
{
    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'addMenu']);
    }

    public static function addMenu(): void
    {
        add_submenu_page(
            'artpulse-settings',
            __('Portfolio Sync Logs', 'artpulse'),
            __('Portfolio Sync Logs', 'artpulse'),
            'manage_options',
            'ap-portfolio-logs',
            [self::class, 'render']
        );
    }

    public static function render(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_portfolio_sync_logs';
        $rows  = $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC LIMIT 50");
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Portfolio Sync Logs', 'artpulse') . '</h1>';
        echo '<table class="widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>' . esc_html__('Time', 'artpulse') . '</th>';
        echo '<th>' . esc_html__('Action', 'artpulse') . '</th>';
        echo '<th>' . esc_html__('Message', 'artpulse') . '</th>';
        echo '<th>' . esc_html__('Data', 'artpulse') . '</th>';
        echo '</tr></thead><tbody>';
        if ($rows) {
            foreach ($rows as $row) {
                echo '<tr>';
                echo '<td>' . esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($row->logged_at))) . '</td>';
                echo '<td>' . esc_html($row->action) . '</td>';
                echo '<td>' . esc_html($row->message) . '</td>';
                $meta = $row->metadata ? json_encode(json_decode($row->metadata, true), JSON_UNESCAPED_SLASHES) : '';
                echo '<td><code>' . esc_html($meta) . '</code></td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="4">' . esc_html__('No logs found.', 'artpulse') . '</td></tr>';
        }
        echo '</tbody></table>';
        echo '</div>';
    }
}
