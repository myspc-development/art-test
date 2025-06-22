<?php
namespace ArtPulse\Frontend;

class OrganizationSubmissionForm {
    public static function register(): void {
        add_shortcode('ap_submit_organization', [self::class, 'render']);
    }

    public static function render(): string {
        if (!defined('ARTPULSE_PLUGIN_FILE')) {
            return '';
        }

        wp_enqueue_script('ap-org-submission-js');

        ob_start();
        ?>
        <form class="ap-org-submission-form">
            <p>
                <label for="ap-org-title"><?php esc_html_e('Organization Name', 'artpulse'); ?></label><br>
                <input id="ap-org-title" type="text" name="title" required />
            </p>
            <p>
                <label for="ap-org-description"><?php esc_html_e('Description', 'artpulse'); ?></label><br>
                <textarea id="ap-org-description" name="description" required></textarea>
            </p>
            <p>
                <label for="ap-org-website"><?php esc_html_e('Website', 'artpulse'); ?></label><br>
                <input id="ap-org-website" type="url" name="org_website" required />
            </p>
            <p>
                <label for="ap-org-email"><?php esc_html_e('Email', 'artpulse'); ?></label><br>
                <input id="ap-org-email" type="email" name="org_email" required />
            </p>
            <p>
                <label for="ap-org-images"><?php esc_html_e('Images (max 5)', 'artpulse'); ?></label><br>
                <input id="ap-org-images" type="file" name="images[]" accept="image/*" multiple />
            </p>
            <p>
                <button type="submit"><?php esc_html_e('Submit', 'artpulse'); ?></button>
            </p>
        </form>
        <div class="ap-org-submission-message" role="status" aria-live="polite"></div>
        <?php
        return ob_get_clean();
    }
}
