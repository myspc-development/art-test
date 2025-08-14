<?php
namespace ArtPulse\Widgets\Member;

if (!defined('ABSPATH')) { exit; }

use ArtPulse\Core\DashboardWidgetInterface;
use ArtPulse\Core\DashboardWidgetRegistry;

class WelcomeBoxWidget implements DashboardWidgetInterface {
    public static function id(): string { return 'welcome_box'; }

    public static function label(): string { return 'Welcome'; }

    public static function roles(): array { return ['member']; }

    public static function description(): string { return 'Personal greeting for the signed-in user.'; }

    public static function register(): void {
        DashboardWidgetRegistry::register(
            self::id(),
            self::label(),
            'smiley',
            self::description(),
            [self::class, 'render'],
            [ 'roles' => self::roles(), 'category' => 'general' ]
        );
    }

    public static function render(int $user_id = 0): string {
        $user       = wp_get_current_user();
        $name       = $user->display_name ?: $user->user_login;
        $text       = sprintf(esc_html__( 'Welcome back, %s!', 'artpulse' ), $name);
        $heading_id = sanitize_title(self::id()) . '-heading-' . uniqid();

        ob_start();
        ?>
        <section role="region" aria-labelledby="<?php echo esc_attr($heading_id); ?>"
            data-widget-id="<?php echo esc_attr(self::id()); ?>"
            class="ap-widget ap-<?php echo esc_attr(self::id()); ?>">
            <h2 id="<?php echo esc_attr($heading_id); ?>"><?php echo esc_html__( self::label(), 'artpulse' ); ?></h2>
            <p><?php echo esc_html($text); ?></p>
        </section>
        <?php
        return ob_get_clean();
    }
}

WelcomeBoxWidget::register();
