<?php
namespace ArtPulse\Frontend;

class ArtworkCommentsShortcode
{
    public static function register(): void
    {
        add_shortcode('ap_artwork_comments', [self::class, 'render']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_styles']);
    }

    public static function enqueue_styles(): void
    {
        if (function_exists('ap_enqueue_global_styles')) {
            ap_enqueue_global_styles();
        }
    }

    public static function render($atts = []): string
    {
        $atts = shortcode_atts([
            'id' => get_the_ID(),
        ], $atts, 'ap_artwork_comments');

        $artwork_id = intval($atts['id']);
        if (!$artwork_id) {
            return '';
        }

        ob_start();
        ?>
        <div class="ap-artwork-comments" data-artwork-id="<?= esc_attr($artwork_id); ?>">
            <ul class="ap-comment-list" role="status" aria-live="polite"></ul>
            <?php if (is_user_logged_in()): ?>
                <form class="ap-comment-form">
                    <textarea name="content" required></textarea>
                    <button type="submit"><?php esc_html_e('Submit', 'artpulse'); ?></button>
                </form>
            <?php else: ?>
                <p><?php esc_html_e('Please log in to comment.', 'artpulse'); ?></p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
