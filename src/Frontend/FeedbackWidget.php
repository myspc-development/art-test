<?php
namespace ArtPulse\Frontend;

class FeedbackWidget
{
    public static function register(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'enqueue']);
        add_action('wp_footer', [self::class, 'render']);
    }

    public static function enqueue(): void
    {
        if (is_admin()) {
            return;
        }
        $base = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . '/assets/js/ap-feedback.js';
        if (file_exists($base)) {
            wp_enqueue_script(
                'ap-feedback',
                plugin_dir_url(ARTPULSE_PLUGIN_FILE) . '/assets/js/ap-feedback.js',
                ['jquery'],
                filemtime($base),
                true
            );
            wp_localize_script('ap-feedback', 'APFeedback', [
                'ajaxUrl'   => admin_url('admin-ajax.php'),
                'nonce'     => wp_create_nonce('ap_feedback_nonce'),
                'thanks'    => __('Thanks for your feedback!', 'artpulse'),
                'apiRoot'   => esc_url_raw(rest_url()),
                'restNonce' => wp_create_nonce('wp_rest'),
            ]);
            $css = plugin_dir_url(ARTPULSE_PLUGIN_FILE) . '/assets/css/ap-feedback.css';
            wp_enqueue_style('ap-feedback', $css, [], '1.0');
        }
    }

    public static function render(): void
    {
        if (is_admin()) {
            return;
        }
        ?>
        <button id="ap-feedback-button" aria-label="<?php esc_attr_e('Give Feedback', 'artpulse'); ?>" title="<?php esc_attr_e('Give Feedback', 'artpulse'); ?>">
            🗨️ <?php esc_html_e('Feedback', 'artpulse'); ?>
        </button>
        <div id="ap-feedback-modal" role="dialog" aria-modal="true" hidden>
            <button type="button" id="ap-feedback-close" class="ap-feedback-close">×</button>
            <form id="ap-feedback-form">
                <label for="ap-feedback-type"><?php esc_html_e('Type of Feedback', 'artpulse'); ?></label>
                <select id="ap-feedback-type" name="type" required>
                    <option value="bug"><?php esc_html_e('Bug Report', 'artpulse'); ?></option>
                    <option value="feature"><?php esc_html_e('Feature Request', 'artpulse'); ?></option>
                    <option value="general"><?php esc_html_e('General Feedback', 'artpulse'); ?></option>
                </select>
                <label for="ap-feedback-description"><?php esc_html_e('Description', 'artpulse'); ?></label>
                <textarea id="ap-feedback-description" name="description" required></textarea>
                <label for="ap-feedback-email"><?php esc_html_e('Email (optional)', 'artpulse'); ?></label>
                <input type="email" id="ap-feedback-email" name="email" placeholder="you@example.com">
                <?php wp_nonce_field('ap_feedback_nonce', 'nonce'); ?>
                <button type="submit"><?php esc_html_e('Send Feedback', 'artpulse'); ?></button>
            </form>
            <div id="ap-feedback-message" role="status" aria-live="polite"></div>
            <ul id="ap-feedback-list" class="ap-feedback-list"></ul>
        </div>
        <?php
    }
}
