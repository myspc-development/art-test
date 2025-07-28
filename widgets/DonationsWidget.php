<?php
namespace ArtPulse\Widgets;

use ArtPulse\Core\DashboardWidgetRegistry;

class DonationsWidget {
    public static function register(): void {
        DashboardWidgetRegistry::register(
            self::get_id(),
            self::get_title(),
            'money-alt',
            __('Recent donations list.', 'artpulse'),
            [self::class, 'render'],
            ['roles' => ['organization'], 'group' => 'actions']
        );
    }

    public static function get_id(): string { return 'sample_donations'; }
    public static function get_title(): string { return __('Donations Widget', 'artpulse'); }
    public static function metadata(): array { return ['sample' => true]; }
    public static function can_view(): bool { return current_user_can('manage_options') || current_user_can('organization'); }

    public static function render(): void {
        if (!self::can_view()) {
            echo '<p class="ap-widget-no-access">' . esc_html__('You do not have access.', 'artpulse') . '</p>';
            return;
        }
        if (function_exists('ap_load_dashboard_template')) {
            echo ap_load_dashboard_template('widgets/donations.php');
        } else {
            echo '<p>' . esc_html__('Example donations data.', 'artpulse') . '</p>';
        }
    }
}

DonationsWidget::register();
