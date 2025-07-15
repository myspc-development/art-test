<?php
namespace ArtPulse\Frontend;

class MessagesShortcode
{
    public static function register(): void
    {
        add_shortcode('ap_messages', [self::class, 'render']);
        add_shortcode('ap_inbox', [self::class, 'render']);
        add_shortcode('ap_message_form', [self::class, 'render_form']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue']);
    }

    public static function enqueue(): void
    {
        $post = get_post();
        if ($post instanceof \WP_Post && has_shortcode($post->post_content, 'ap_messages')) {
            if (function_exists('ap_enqueue_global_styles')) {
                ap_enqueue_global_styles();
            }
            wp_enqueue_script('ap-messages-js');
        }
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
                <ul id="ap-message-list" aria-live="polite"></ul>
                <form id="ap-message-form">
                    <input type="hidden" name="recipient_id" value="">
                    <label for="ap-message-content"><?php esc_html_e('Message', 'artpulse'); ?></label>
                    <textarea id="ap-message-content" name="content" required></textarea>
                    <button type="submit"><?php esc_html_e('Send', 'artpulse'); ?></button>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function render_form(): string
    {
        if (!is_user_logged_in()) {
            return '';
        }

        ob_start();
        ?>
        <form id="ap-message-form-shortcode">
            <input type="hidden" name="recipient_id" value="">
            <label for="ap-message-content-sc"><?php esc_html_e('Message', 'artpulse'); ?></label>
            <textarea id="ap-message-content-sc" name="content" required></textarea>
            <button type="submit"><?php esc_html_e('Send', 'artpulse'); ?></button>
        </form>
        <?php
        return ob_get_clean();
    }
}
