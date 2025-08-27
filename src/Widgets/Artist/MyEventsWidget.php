<?php
namespace ArtPulse\Widgets\Artist;

if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\Core\DashboardWidgetInterface;
use ArtPulse\Core\DashboardWidgetRegistry;

class MyEventsWidget implements DashboardWidgetInterface
{
    public static function id(): string
    {
        return 'widget_my_events';
    }

    public static function label(): string
    {
        return esc_html__('My Events', 'artpulse');
    }

    public static function roles(): array
    {
        return ['artist', 'member', 'organization'];
    }

    public static function description(): string
    {
        return esc_html__('Events you manage.', 'artpulse');
    }

    public static function register(): void
    {
        DashboardWidgetRegistry::alias('myevents', self::id());
        DashboardWidgetRegistry::alias('my-events', self::id());
        DashboardWidgetRegistry::register(
            self::id(),
            self::label(),
            'calendar',
            self::description(),
            [self::class, 'render'],
            [
                'roles'    => self::roles(),
                'category' => 'events',
            ]
        );
    }

    public static function render(int $user_id = 0): string
    {
        $heading_id = self::id() . '-heading';
        $add_url    = function_exists('admin_url') ? admin_url('post-new.php?post_type=artpulse_event') : '#';
        ob_start();
        ?>
        <section role="region" aria-labelledby="<?php echo esc_attr($heading_id); ?>"
            data-widget="<?php echo esc_attr(self::id()); ?>"
            data-widget-id="<?php echo esc_attr(self::id()); ?>"
            class="ap-widget ap-<?php echo esc_attr(self::id()); ?>">
            <h2 id="<?php echo esc_attr($heading_id); ?>"><?php echo esc_html(self::label()); ?></h2>
            <p><?php echo esc_html__('No events found.', 'artpulse'); ?></p>
            <p><a class="ap-widget__cta" href="<?php echo esc_url($add_url); ?>"><?php echo esc_html__('Create Event', 'artpulse'); ?></a></p>
        </section>
        <?php
        return ob_get_clean();
    }
}

MyEventsWidget::register();
