<?php
namespace ArtPulse\Widgets\Organization;

if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\Core\DashboardWidgetInterface;
use ArtPulse\Core\DashboardWidgetRegistry;

class MyEventsWidget implements DashboardWidgetInterface
{
    public static function id(): string
    {
        return 'my-events';
    }

    public static function label(): string
    {
        return esc_html__('My Events', 'artpulse');
    }

    public static function roles(): array
    {
        return ['organization', 'administrator'];
    }

    public static function description(): string
    {
        return esc_html__('Events you manage.', 'artpulse');
    }

    public static function boot(): void
    {
        add_action('artpulse/widgets/register', [self::class, 'register'], 10, 1);
    }

    public static function register($registry = null): void
    {
        DashboardWidgetRegistry::alias('myevents', self::id());
        DashboardWidgetRegistry::register(
            self::id(),
            self::label(),
            'calendar',
            self::description(),
            [self::class, 'render'],
            [
                'roles'      => self::roles(),
                'capability' => 'ap_manage_org',
                'category'   => 'events',
            ]
        );
    }

    public static function render(int $user_id = 0): string
    {
        if (!current_user_can('ap_manage_org') && !current_user_can('manage_options')) {
            $heading_id = self::id() . '-heading';
            ob_start();
            ?>
            <section role="region" aria-labelledby="<?php echo esc_attr($heading_id); ?>"
                data-widget="<?php echo esc_attr(self::id()); ?>"
                data-widget-id="<?php echo esc_attr(self::id()); ?>"
                class="ap-widget ap-<?php echo esc_attr(self::id()); ?>">
                <h2 id="<?php echo esc_attr($heading_id); ?>"><?php echo esc_html(self::label()); ?></h2>
                <p><?php echo esc_html__('Permission denied', 'artpulse'); ?></p>
            </section>
            <?php
            return ob_get_clean();
        }
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

