<?php
namespace ArtPulse\Frontend;

class QaThreadShortcode {
    public static function register(): void {
        \ArtPulse\Core\ShortcodeRegistry::register('ap_event_qa', 'Event QA', [self::class, 'render']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue']);
    }

    public static function enqueue(): void {
        if (function_exists('ap_enqueue_global_styles')) {
            ap_enqueue_global_styles();
        }
        wp_enqueue_script(
            'ap-qa-thread',
            plugin_dir_url(ARTPULSE_PLUGIN_FILE) . 'assets/js/ap-qa-thread.js',
            ['wp-api-fetch'],
            '1.0.0',
            true
        );
        wp_localize_script('ap-qa-thread', 'APQa', [
            'apiRoot' => esc_url_raw(rest_url()),
            'nonce'   => wp_create_nonce('wp_rest'),
        ]);
    }

    public static function render($atts): string {
        $atts = shortcode_atts(['id' => get_the_ID()], $atts, 'ap_event_qa');
        $event_id = intval($atts['id']);
        if (!$event_id) return '';
        $can_post = is_user_logged_in();
        ob_start();
        ?>
        <div class="ap-qa-thread" data-event-id="<?= esc_attr($event_id); ?>" data-can-post="<?= $can_post ? '1' : '0'; ?>">
            <ul class="ap-qa-list"></ul>
            <?php if (is_user_logged_in()): ?>
                <form class="ap-qa-form"><textarea required name="content"></textarea><button type="submit">Post</button></form>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
