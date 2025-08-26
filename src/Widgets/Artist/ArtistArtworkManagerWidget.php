<?php
namespace ArtPulse\Widgets\Artist;

if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\Core\DashboardWidgetInterface;
use ArtPulse\Core\DashboardWidgetRegistry;

class ArtistArtworkManagerWidget implements DashboardWidgetInterface
{
    public static function id(): string
    {
        return 'artist_artwork_manager';
    }

    public static function label(): string
    {
        return esc_html__('Artwork Manager', 'artpulse');
    }

    public static function roles(): array
    {
        return ['artist'];
    }

    public static function description(): string
    {
        return esc_html__('Quick list of your latest artworks.', 'artpulse');
    }

    public static function register(): void
    {
        DashboardWidgetRegistry::register(
            self::id(),
            self::label(),
            'images-alt',
            self::description(),
            [self::class, 'render'],
            [
                'roles'    => self::roles(),
                'category' => 'content',
            ]
        );
    }

    public static function render(int $user_id = 0): string
    {
        $heading_id = self::id() . '-heading';
        $add_url    = function_exists('admin_url') ? admin_url('post-new.php?post_type=artpulse_artwork') : '#';
        ob_start();
        ?>
        <section role="region" aria-labelledby="<?php echo esc_attr($heading_id); ?>"
            data-widget="<?php echo esc_attr(self::id()); ?>"
            data-widget-id="<?php echo esc_attr(self::id()); ?>"
            class="ap-widget ap-<?php echo esc_attr(self::id()); ?>">
            <h2 id="<?php echo esc_attr($heading_id); ?>"><?php echo esc_html(self::label()); ?></h2>
            <ul class="ap-widget__list">
                <li><?php echo esc_html__('No artworks yet.', 'artpulse'); ?></li>
            </ul>
            <p><a class="ap-widget__cta" href="<?php echo esc_url($add_url); ?>"><?php echo esc_html__('Add New', 'artpulse'); ?></a></p>
        </section>
        <?php
        return ob_get_clean();
    }
}

ArtistArtworkManagerWidget::register();
