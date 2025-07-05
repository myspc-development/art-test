<?php
namespace ArtPulse\Frontend;

class EventChatShortcode
{
    public static function register(): void
    {
        add_shortcode('ap_event_chat', [self::class, 'render']);
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
        ], $atts, 'ap_event_chat');

        $event_id = intval($atts['id']);
        if (!$event_id) {
            return '';
        }

        ob_start();
        ?>
        <div class="ap-event-chat" data-event-id="<?= esc_attr($event_id); ?>">
            <ul class="ap-chat-list" role="status" aria-live="polite"></ul>
            <?php if (is_user_logged_in()): ?>
                <form class="ap-chat-form">
                    <input type="text" name="content" required>
                    <button type="submit">Send</button>
                </form>
            <?php else: ?>
                <p><?php esc_html_e('Please log in to chat.', 'artpulse'); ?></p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
