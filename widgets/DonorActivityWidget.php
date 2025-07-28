<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
if (!defined('ABSPATH')) { exit; }

use ArtPulse\Crm\DonationModel;
use ArtPulse\Core\DashboardWidgetRegistry;

class DonorActivityWidget
{
    public static function register(): void
    {
        DashboardWidgetRegistry::register(
            'ap_donor_activity',
            __('Donor Activity', 'artpulse'),
            'chart-line',
            __('Recent donations for your organization.', 'artpulse'),
            [self::class, 'render'],
            [ 'roles' => ['organization'] ]
        );
    }

    public static function render(): void
    {
        if (defined("IS_DASHBOARD_BUILDER_PREVIEW")) return;
        $org_id = get_user_meta(get_current_user_id(), 'ap_organization_id', true);
        if (!$org_id) {
            esc_html_e('No organization assigned.', 'artpulse');
            return;
        }

        $from = sanitize_text_field($_GET['donor_from'] ?? '');
        $to   = sanitize_text_field($_GET['donor_to'] ?? '');
        echo '<form method="get" style="margin-bottom:10px">';
        echo '<input type="hidden" name="page" value="dashboard" />';
        echo '<label>' . esc_html__('From', 'artpulse') . ' <input type="date" name="donor_from" value="' . esc_attr($from) . '" /></label> ';
        echo '<label>' . esc_html__('To', 'artpulse') . ' <input type="date" name="donor_to" value="' . esc_attr($to) . '" /></label> ';
        submit_button(__('Filter','artpulse'), 'secondary', '','false');
        echo '</form>';

        $rows = DonationModel::query((int) $org_id, $from, $to);
        if (!$rows) {
            esc_html_e('No donations found.', 'artpulse');
            return;
        }
        echo '<table class="widefat striped"><thead><tr><th>' . esc_html__('Donor','artpulse') . '</th><th>' . esc_html__('Amount','artpulse') . '</th><th>' . esc_html__('Date','artpulse') . '</th></tr></thead><tbody>';
        foreach ($rows as $row) {
            $user = get_user_by('ID', $row['user_id']);
            $name = $user ? $user->display_name : __('Anonymous', 'artpulse');
            echo '<tr><td>' . esc_html($name) . '</td><td>$' . number_format_i18n($row['amount'], 2) . '</td><td>' . esc_html(date_i18n(get_option('date_format'), strtotime($row['donated_at']))) . '</td></tr>';
        }
        echo '</tbody></table>';
    }
}

DonorActivityWidget::register();
