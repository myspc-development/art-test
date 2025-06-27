<?php

namespace ArtPulse\Frontend;

class ProfileEditShortcode {

    public static function register() {
        add_shortcode('ap_profile_edit', [self::class, 'render_form']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_styles']);
        self::handle_form_submission();
        add_action('wp_ajax_update_profile_field', [self::class, 'ajax_update_profile']);
    }

    public static function enqueue_styles() {
        if (function_exists('ap_enqueue_global_styles')) {
            add_filter('ap_bypass_shortcode_detection', '__return_true');
            ap_enqueue_global_styles();
        }

        wp_enqueue_media();
        wp_enqueue_script(
            'ap-profile-modal',
            plugins_url('assets/js/ap-profile-modal.js', ARTPULSE_PLUGIN_FILE),
            [],
            '1.0.0',
            true
        );
        wp_localize_script('ap-profile-modal', 'APProfileModal', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('ap_profile_edit_action'),
        ]);
    }

    public static function render_form() {
        if (!is_user_logged_in()) {
            return '<p>You must be logged in to edit your profile.</p>';
        }

        $user_id = get_current_user_id();
        $user = get_userdata($user_id);
        $bio = get_user_meta($user_id, 'description', true);
        $avatar = get_user_meta($user_id, 'ap_custom_avatar', true);
        $twitter = get_user_meta($user_id, 'ap_social_twitter', true);
        $instagram = get_user_meta($user_id, 'ap_social_instagram', true);
        $website = get_user_meta($user_id, 'ap_social_website', true);
        $country = get_user_meta($user_id, 'ap_country', true);
        $state   = get_user_meta($user_id, 'ap_state', true);
        $city    = get_user_meta($user_id, 'ap_city', true);

        $output = '';
        if (isset($_GET['ap_updated'])) {
            $output .= '<div class="notice success">Profile updated successfully.</div>';
        }

        ob_start();
        ?>
        <form method="post" enctype="multipart/form-data" class="ap-profile-edit-form ap-form-container">
            <?php wp_nonce_field('ap_profile_edit_action', 'ap_profile_nonce'); ?>
            <p>
                <label class="ap-form-label" for="display_name">Display Name</label><br>
                <input class="ap-input" type="text" name="display_name" id="display_name" value="<?php echo esc_attr($user->display_name); ?>" required>
            </p>
            <p>
                <label class="ap-form-label" for="description">Bio</label><br>
                <textarea class="ap-input" name="description" id="description" rows="5"><?php echo esc_textarea($bio); ?></textarea>
            </p>
            <p>
                <label class="ap-form-label" for="ap_avatar">Custom Avatar</label><br>
                <?php if ($avatar): ?>
                    <img src="<?php echo esc_url($avatar); ?>" alt="Current Avatar" width="100" /><br>
                <?php endif; ?>
                <input class="ap-input" type="file" name="ap_avatar" id="ap_avatar" accept="image/*">
            </p>
            <p>
                <label class="ap-form-label" for="ap_social_twitter">Twitter URL</label><br>
                <input class="ap-input" type="url" name="ap_social_twitter" id="ap_social_twitter" value="<?php echo esc_url($twitter); ?>">
            </p>
            <p>
                <label class="ap-form-label" for="ap_social_instagram">Instagram URL</label><br>
                <input class="ap-input" type="url" name="ap_social_instagram" id="ap_social_instagram" value="<?php echo esc_url($instagram); ?>">
            </p>
            <p>
                <label class="ap-form-label" for="ap_social_website">Website URL</label><br>
                <input class="ap-input" type="url" name="ap_social_website" id="ap_social_website" value="<?php echo esc_url($website); ?>">
            </p>
            <p>
                <label class="ap-form-label" for="ap_country">Country</label><br>
                <input class="ap-input ap-address-country ap-address-input" id="ap_country" type="text" name="ap_country" data-selected="<?php echo esc_attr($country); ?>" />
            </p>
            <p>
                <label class="ap-form-label" for="ap_state">State/Province</label><br>
                <input class="ap-input ap-address-state ap-address-input" id="ap_state" type="text" name="ap_state" data-selected="<?php echo esc_attr($state); ?>" />
            </p>
            <p>
                <label class="ap-form-label" for="ap_city">City</label><br>
                <input class="ap-input ap-address-city ap-address-input" id="ap_city" type="text" name="ap_city" data-selected="<?php echo esc_attr($city); ?>" />
            </p>
            <input type="hidden" name="address_components" id="ap-profile-address-components" value="<?php echo esc_attr(json_encode(['country' => $country, 'state' => $state, 'city' => $city])); ?>">
            <p>
                <input class="ap-form-button nectar-button" type="submit" name="ap_profile_submit" value="Update Profile">
            </p>
        </form>
        <?php
        return $output . ob_get_clean();
    }

    public static function handle_form_submission() {
        if (!isset($_POST['ap_profile_submit']) || !is_user_logged_in()) {
            return;
        }

        if (!isset($_POST['ap_profile_nonce']) || !wp_verify_nonce($_POST['ap_profile_nonce'], 'ap_profile_edit_action')) {
            return;
        }

        $user_id = get_current_user_id();
        $display_name = sanitize_text_field($_POST['display_name']);
        $description = sanitize_textarea_field($_POST['description']);
        $twitter = esc_url_raw($_POST['ap_social_twitter']);
        $instagram = esc_url_raw($_POST['ap_social_instagram']);
        $website = esc_url_raw($_POST['ap_social_website']);
        $components = [];
        if (!empty($_POST['address_components'])) {
            $decoded = json_decode(stripslashes($_POST['address_components']), true);
            if (is_array($decoded)) {
                $components = $decoded;
            }
        }

        $country = isset($components['country']) ? sanitize_text_field($components['country']) : '';
        $state   = isset($components['state']) ? sanitize_text_field($components['state']) : '';
        $city    = isset($components['city']) ? sanitize_text_field($components['city']) : '';

        wp_update_user([
            'ID' => $user_id,
            'display_name' => $display_name
        ]);

        update_user_meta($user_id, 'description', $description);
        update_user_meta($user_id, 'ap_social_twitter', $twitter);
        update_user_meta($user_id, 'ap_social_instagram', $instagram);
        update_user_meta($user_id, 'ap_social_website', $website);
        update_user_meta($user_id, 'ap_country', $country);
        update_user_meta($user_id, 'ap_state', $state);
        update_user_meta($user_id, 'ap_city', $city);

        // Handle Avatar Upload
        if (!empty($_FILES['ap_avatar']['tmp_name'])) {
            if (!function_exists('media_handle_upload')) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/media.php';
                require_once ABSPATH . 'wp-admin/includes/image.php';
            }

            $uploaded = media_handle_upload('ap_avatar', 0);
            if (!is_wp_error($uploaded)) {
                $avatar_url = wp_get_attachment_url($uploaded);
                update_user_meta($user_id, 'ap_custom_avatar', $avatar_url);
            }
        }

        if (function_exists('wc_add_notice')) {
            wc_add_notice('Profile updated successfully.', 'success');
        }

        wp_redirect(add_query_arg('ap_updated', '1', wp_get_referer()));
        exit;
    }

    public static function ajax_update_profile() {
        check_ajax_referer('ap_profile_edit_action', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Not logged in']);
        }

        $user_id = get_current_user_id();
        $display_name = sanitize_text_field($_POST['display_name'] ?? '');
        $description  = sanitize_textarea_field($_POST['description'] ?? '');
        $twitter      = esc_url_raw($_POST['ap_social_twitter'] ?? '');
        $instagram    = esc_url_raw($_POST['ap_social_instagram'] ?? '');
        $website      = esc_url_raw($_POST['ap_social_website'] ?? '');

        $components = [];
        if (!empty($_POST['address_components'])) {
            $decoded = json_decode(stripslashes($_POST['address_components']), true);
            if (is_array($decoded)) {
                $components = $decoded;
            }
        }

        $country = isset($components['country']) ? sanitize_text_field($components['country']) : '';
        $state   = isset($components['state']) ? sanitize_text_field($components['state']) : '';
        $city    = isset($components['city']) ? sanitize_text_field($components['city']) : '';

        wp_update_user([
            'ID'           => $user_id,
            'display_name' => $display_name,
        ]);

        update_user_meta($user_id, 'description', $description);
        update_user_meta($user_id, 'ap_social_twitter', $twitter);
        update_user_meta($user_id, 'ap_social_instagram', $instagram);
        update_user_meta($user_id, 'ap_social_website', $website);
        update_user_meta($user_id, 'ap_country', $country);
        update_user_meta($user_id, 'ap_state', $state);
        update_user_meta($user_id, 'ap_city', $city);

        if (!empty($_FILES['ap_avatar']['tmp_name'])) {
            if (!function_exists('media_handle_upload')) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/media.php';
                require_once ABSPATH . 'wp-admin/includes/image.php';
            }
            $uploaded = media_handle_upload('ap_avatar', 0);
            if (!is_wp_error($uploaded)) {
                $avatar_url = wp_get_attachment_url($uploaded);
                update_user_meta($user_id, 'ap_custom_avatar', $avatar_url);
            }
        }

        wp_send_json_success(['display_name' => $display_name]);
    }
}
