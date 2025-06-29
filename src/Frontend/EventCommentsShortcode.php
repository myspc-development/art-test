<?php
namespace ArtPulse\Frontend;

class EventCommentsShortcode
{
    public static function register(): void
    {
        add_shortcode('ap_event_comments', [self::class, 'render']);
    }

    public static function render($atts = []): string
    {
        $atts = shortcode_atts([
            'id' => get_the_ID(),
        ], $atts, 'ap_event_comments');

        $event_id = intval($atts['id']);
        if (!$event_id) {
            return '';
        }

        ob_start();
        ?>
        <div class="ap-event-comments" data-event-id="<?= esc_attr($event_id); ?>">
            <ul class="ap-comment-list" role="status" aria-live="polite"></ul>
            <?php if (is_user_logged_in()): ?>
                <form class="ap-comment-form">
                    <textarea name="content" required></textarea>
                    <button type="submit">Submit</button>
                </form>
            <?php else: ?>
                <p>Please log in to comment.</p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
