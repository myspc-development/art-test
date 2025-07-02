<?php
namespace ArtPulse\Frontend;

class OrganizationSubmissionForm {
    public static function register(): void {
        add_shortcode('ap_submit_organization', [self::class, 'render']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_styles']);
    }

    public static function enqueue_styles(): void {
        if (function_exists('ap_enqueue_global_styles')) {
            ap_enqueue_global_styles();
        }
    }

    public static function render(): string {
        if (!defined('ARTPULSE_PLUGIN_FILE')) {
            return '';
        }

        wp_enqueue_script('ap-org-submission-js');

        $fields = \ArtPulse\Admin\MetaBoxesOrganisation::get_registered_org_meta_fields();
        // Avoid duplicate name input; the title field already captures the
        // organization name.
        unset($fields['ead_org_name']);

        ob_start();
        ?>
<form class="submission-form ap-org-submission-form" enctype="multipart/form-data">
            <fieldset class="form-section">
                <legend>Contact Info</legend>
                <label for="ap-org-title"><?php esc_html_e('Organization Name', 'artpulse'); ?></label>
                <input id="ap-org-title" type="text" name="title">

                <label for="ap-org-country"><?php esc_html_e('Country', 'artpulse'); ?></label>
                <input id="ap-org-country" type="text">
            </fieldset>

            <fieldset class="form-section">
                <legend>Details</legend>
                <label for="ap-org-city"><?php esc_html_e('City', 'artpulse'); ?></label>
                <input id="ap-org-city" type="text">

                <label for="ap-org-images"><?php esc_html_e('Images (max 5)', 'artpulse'); ?></label>
                <input id="ap-org-images" type="file" name="images[]" accept="image/*" multiple>
            </fieldset>

            <button type="submit" class="button-primary"><?php esc_html_e('Submit', 'artpulse'); ?></button>
        </form>
        <div class="ap-form-messages" role="status" aria-live="polite"></div>
        <?php
        return ob_get_clean();
    }
}
