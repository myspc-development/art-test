<?php
namespace ArtPulse\Frontend;

class MessagesShortcode
{
    public static function register(): void
    {
        add_shortcode('ap_messages', [self::class, 'render']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue']);
    }

    public static function enqueue(): void
    {
        if (function_exists('ap_enqueue_global_styles')) {
            ap_enqueue_global_styles();
        }
        wp_enqueue_script('ap-messages-js');
    }

    public static function render(): string
    {
        if (!is_user_logged_in()) {
            return '<p>' . esc_html__('Please log in to view your messages.', 'artpulse') . '</p>';
        }

        ob_start();
        ?>
        <div class="ap-messages" id="ap-messages">
            <div class="ap-conversations">
                <h3><?php esc_html_e('Conversations', 'artpulse'); ?></h3>
                <ul id="ap-conversation-list"></ul>
            </div>
            <div class="ap-thread">
                <ul id="ap-message-list"></ul>
                <form id="ap-message-form" style="display:none;">
                    <input type="hidden" name="recipient_id" value="">
                    <textarea name="content" required></textarea>
                    <button type="submit"><?php esc_html_e('Send', 'artpulse'); ?></button>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
