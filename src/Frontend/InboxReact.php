<?php
namespace ArtPulse\Frontend;

use ArtPulse\Community\DirectMessages;

class InboxReact {
    public static function register(): void {
        add_shortcode('ap_inbox_app', [self::class, 'render']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue']);
    }

    public static function enqueue(): void {
        $post = get_post();
        if ($post instanceof \WP_Post && has_shortcode($post->post_content, 'ap_inbox_app')) {
            wp_enqueue_script(
                'ap-inbox-app',
                plugins_url('assets/js/inbox-app.js', ARTPULSE_PLUGIN_FILE),
                ['wp-element', 'wp-api-fetch'],
                filemtime(plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'assets/js/inbox-app.js'),
                true
            );
            $messages = [];
            if (is_user_logged_in()) {
                $messages = DirectMessages::list_conversations(get_current_user_id());
            }
            wp_localize_script('ap-inbox-app', 'APInbox', [
                'apiRoot'  => esc_url_raw(rest_url()),
                'nonce'    => wp_create_nonce('wp_rest'),
                'messages' => $messages,
                'threadId' => 0,
                'attachments' => []
            ]);
        }
    }

    public static function render(): string {
        if (!is_user_logged_in()) {
            return '<p>' . esc_html__('Please log in to view your inbox.', 'artpulse') . '</p>';
        }
        return '<div id="ap-inbox-app"></div>';
    }
}
