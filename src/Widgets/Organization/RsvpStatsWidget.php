<?php
namespace ArtPulse\Widgets\Organization;

if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\Core\DashboardWidgetInterface;
use ArtPulse\Core\DashboardWidgetRegistry;

class RsvpStatsWidget implements DashboardWidgetInterface
{
    public static function id(): string
    {
        return 'rsvp_stats';
    }

    public static function label(): string
    {
        return esc_html__('RSVP Stats', 'artpulse');
    }

    public static function roles(): array
    {
        return ['organization', 'administrator'];
    }

    public static function description(): string
    {
        return esc_html__('Attendance trends for upcoming events.', 'artpulse');
    }

    public static function boot(): void
    {
        add_action('artpulse/widgets/register', [self::class, 'register'], 10, 1);
    }

    public static function register($registry = null): void
    {
        DashboardWidgetRegistry::register(
            self::id(),
            self::label(),
            'chart-line',
            self::description(),
            [self::class, 'render'],
            [
                'roles'      => self::roles(),
                'capability' => 'ap_manage_org',
                'category'   => 'analytics',
            ]
        );
    }

    public static function render(int $user_id = 0): string
    {
        if (!current_user_can('ap_manage_org') && !current_user_can('manage_options')) {
            return '';
        }
        $heading_id = self::id() . '-heading';
        ob_start();
        ?>
        <section role="region" aria-labelledby="<?php echo esc_attr($heading_id); ?>"
            data-widget="<?php echo esc_attr(self::id()); ?>"
            data-widget-id="<?php echo esc_attr(self::id()); ?>"
            class="ap-widget ap-<?php echo esc_attr(self::id()); ?>">
            <h2 id="<?php echo esc_attr($heading_id); ?>"><?php echo esc_html(self::label()); ?></h2>
            <p><?php echo esc_html__('No RSVP data available.', 'artpulse'); ?></p>
        </section>
        <?php
        return ob_get_clean();
    }
}

