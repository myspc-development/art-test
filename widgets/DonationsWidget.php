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
            [
                'roles'   => ['organization'],
                'section' => self::get_section(),
            ]
        );
    }

    public static function get_id(): string { return 'sample_donations'; }
    public static function get_title(): string { return __('Donations Widget', 'artpulse'); }
    public static function get_section(): string { return 'actions'; }
    public static function metadata(): array { return ['sample' => true]; }
    public static function can_view(int $user_id): bool {
        return user_can($user_id, 'manage_options') || user_can($user_id, 'organization');
    }

      public static function render(int $user_id = 0): string {
          $user_id = $user_id ?: get_current_user_id();
          if (!self::can_view($user_id)) {
              return '<div class="notice notice-error"><p>' . esc_html__('You do not have access.', 'artpulse') . '</p></div>';
          }
          $template = locate_template('widgets/donations.php');
        if (!$template) {
            $template = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'templates/widgets/donations.php';
        }
        ob_start();
        if ($template && file_exists($template)) {
            load_template($template, false);
        } else {
            echo '<p>' . esc_html__('Example donations data.', 'artpulse') . '</p>';
        }
        return ob_get_clean();
    }
}

DonationsWidget::register();
