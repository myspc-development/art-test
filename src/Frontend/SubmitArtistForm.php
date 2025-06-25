<?php
namespace ArtPulse\Frontend;

class SubmitArtistForm {
    public static function register(): void {
        add_shortcode('ap_submit_artist', [self::class, 'render']);
    }

    public static function render(): string {
        if (!defined('ARTPULSE_PLUGIN_FILE')) {
            return '';
        }

        if (function_exists('ap_enqueue_global_styles')) {
            ap_enqueue_global_styles();
        }

        wp_enqueue_script('ap-artist-submission-js');

        ob_start();
        ?>
        <form class="ap-artist-submission-form ap-form-container" enctype="multipart/form-data">
            <div class="form-group">
                <label class="ap-form-label" for="ap-artist-title"><?php esc_html_e('Artist Name', 'artpulse'); ?></label>
                <input class="ap-input" id="ap-artist-title" type="text" name="title" required />
            </div>
            <div class="form-group">
                <label class="ap-form-label" for="ap-artist-bio"><?php esc_html_e('Biography', 'artpulse'); ?></label>
                <textarea class="ap-input" id="ap-artist-bio" name="artist_bio" required></textarea>
            </div>
            <div class="form-group">
                <label class="ap-form-label" for="ap-artist-org"><?php esc_html_e('Organization ID', 'artpulse'); ?></label>
                <input class="ap-input" id="ap-artist-org" type="number" name="artist_org" />
            </div>
            <div class="form-group">
                <label class="ap-form-label" for="ap-artist-images"><?php esc_html_e('Images (max 5)', 'artpulse'); ?></label>
                <input class="ap-input" id="ap-artist-images" type="file" name="images[]" accept="image/*" multiple />
            </div>
            <div class="form-group">
                <button class="ap-form-button" type="submit"><?php esc_html_e('Submit', 'artpulse'); ?></button>
            </div>
        </form>
        <div class="ap-form-messages" role="status" aria-live="polite"></div>
        <?php
        return ob_get_clean();
    }
}
