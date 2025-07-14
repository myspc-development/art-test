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

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ap_org_nonce']) && wp_verify_nonce($_POST['ap_org_nonce'], 'submit_org')) {
            self::handle_submission();
        }

        wp_enqueue_script('ap-org-submission-js');

        $fields = \ArtPulse\Admin\MetaBoxesOrganisation::get_registered_org_meta_fields();
        // Avoid duplicate name input; the title field already captures the
        // organization name.
        unset($fields['ead_org_name']);

        ob_start();
        ?>
<form method="post" class="submission-form ap-org-submission-form" enctype="multipart/form-data" data-no-ajax="true">
            <?php wp_nonce_field('submit_org', 'ap_org_nonce'); ?>
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

                <label class="ap-form-label"><?php esc_html_e('Images (max 5)', 'artpulse'); ?></label>
                <?php for ($i = 1; $i <= 5; $i++) : ?>
                <input id="ap-org-image-<?php echo $i; ?>" type="file" name="image_<?php echo $i; ?>" accept="image/*">
                <?php endfor; ?>
            </fieldset>

            <button type="submit" class="button-primary"><?php esc_html_e('Submit', 'artpulse'); ?></button>
        </form>
        <div class="ap-form-messages" role="status" aria-live="polite"></div>
        <?php
        return ob_get_clean();
    }

    public static function handle_submission(): void {
        $title = sanitize_text_field($_POST['title'] ?? '');
        if (!$title) {
            return;
        }

        $post_id = wp_insert_post([
            'post_title'  => $title,
            'post_type'   => 'artpulse_org',
            'post_status' => 'pending',
            'post_author' => get_current_user_id(),
        ]);

        if (is_wp_error($post_id)) {
            return;
        }

        if (!function_exists('media_handle_upload')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $image_ids = [];
        for ($i = 1; $i <= 5; $i++) {
            $key = 'image_' . $i;
            if (!empty($_FILES[$key]['tmp_name'])) {
                $id = media_handle_upload($key, $post_id);
                if (!is_wp_error($id)) {
                    $image_ids[] = $id;
                }
            }
        }

        if ($image_ids) {
            update_post_meta($post_id, '_ap_submission_images', $image_ids);
            set_post_thumbnail($post_id, $image_ids[0]);
        }

        if (function_exists('wc_add_notice')) {
            wc_add_notice('Organization submitted successfully!', 'success');
        }
    }
}
