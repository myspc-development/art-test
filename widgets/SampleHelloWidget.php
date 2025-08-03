<?php
namespace ArtPulse\Widgets;

if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
if (!defined('ABSPATH')) { exit; }

use ArtPulse\Core\DashboardWidgetInterface;
use ArtPulse\Core\DashboardWidgetRegistry;

/**
 * Simple example widget that greets the logged in user.
 */
class SampleHelloWidget implements DashboardWidgetInterface {
    /** Register the widget. */
    public static function register(): void {
        DashboardWidgetRegistry::register(
            self::id(),
            self::label(),
            self::icon(),
            self::description(),
            [self::class, 'render'],
            [ 'roles' => self::roles() ]
        );
    }

    /** Widget unique ID. */
    public static function id(): string {
        return 'sample_hello';
    }

    /** Widget title. */
    public static function label(): string {
        return __('Hello Widget', 'artpulse');
    }

    /** Roles allowed to view the widget. */
    public static function roles(): array {
        return ['member', 'artist', 'organization'];
    }

    public static function description(): string {
        return __('Greets the current user.', 'artpulse');
    }

    public static function icon(): string {
        return 'admin-users';
    }

    /** Determine if the widget can be viewed. */
    public static function can_view(): bool {
        return is_user_logged_in();
    }

    /** Render the widget output. */
      public static function render(int $user_id = 0): string {
          if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) {
              return '';
          }

          $user_id = $user_id ?: get_current_user_id();

          if (!$user_id || !self::can_view()) {
              return '<div class="notice notice-error"><p>' . esc_html__('You do not have access.', 'artpulse') . '</p></div>';
          }

          $user = get_user_by('id', $user_id);
          if (!$user) {
              return '';
          }
          $name = $user->display_name ?: $user->user_login;
          return '<div>Hello, ' . esc_html($name) . '!</div>';
      }
}

SampleHelloWidget::register();
