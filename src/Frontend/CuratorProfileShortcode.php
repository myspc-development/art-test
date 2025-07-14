<?php
namespace ArtPulse\Frontend;

use ArtPulse\Curator\CuratorManager;

class CuratorProfileShortcode
{
    public static function register(): void
    {
        add_shortcode('ap_curator', [self::class, 'render']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue']);
    }

    public static function enqueue(): void
    {
        if (function_exists('ap_enqueue_global_styles')) {
            ap_enqueue_global_styles();
        }
    }

    public static function render($atts): string
    {
        $atts = shortcode_atts([
            'slug' => '',
        ], $atts, 'ap_curator');

        $slug = sanitize_title($atts['slug']);
        if (!$slug) {
            return '';
        }

        $curator = CuratorManager::get_by_slug($slug);
        if (!$curator) {
            return '';
        }

        ob_start();
        ?>
        <div class="ap-curator-profile">
            <h2 class="ap-curator-name"><?php echo esc_html($curator['name']); ?></h2>
            <?php if (!empty($curator['bio'])) : ?>
                <div class="ap-curator-bio"><?php echo wpautop(esc_html($curator['bio'])); ?></div>
            <?php endif; ?>
            <?php if (!empty($curator['website'])) : ?>
                <p><a href="<?php echo esc_url($curator['website']); ?>" target="_blank" rel="noopener"><?php echo esc_html($curator['website']); ?></a></p>
            <?php endif; ?>
            <?php
            $collections = get_posts([
                'post_type'      => 'ap_collection',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'author'         => $curator['user_id'],
                'fields'         => 'ids',
                'no_found_rows'  => true,
            ]);
            if ($collections) : ?>
                <div class="ap-collections-grid">
                    <?php foreach ($collections as $cid) : ?>
                        <?php echo ap_get_collection_card($cid); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
