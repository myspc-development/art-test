<?php
namespace ArtPulse\Frontend;

class ArtistProfileFormShortcode {
    public static function register(): void {
        add_shortcode('ap_artist_profile_form', [self::class, 'render_form']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_styles']);
        add_action('init', [self::class, 'maybe_handle_form']);
    }

    public static function enqueue_styles(): void {
        if (function_exists('ap_enqueue_global_styles')) {
            add_filter('ap_bypass_shortcode_detection', '__return_true');
            ap_enqueue_global_styles();
        }
        wp_enqueue_media();
    }

    public static function render_form(): string {
        if (!is_user_logged_in()) {
            return '<p>' . esc_html__('You must be logged in to edit your profile.', 'artpulse') . '</p>';
        }

        $user_id  = get_current_user_id();
        $bio      = get_user_meta($user_id, 'description', true);
        $avatar   = get_user_meta($user_id, 'ap_custom_avatar', true);
        $twitter  = get_user_meta($user_id, 'ap_social_twitter', true);
        $instagram= get_user_meta($user_id, 'ap_social_instagram', true);
        $website  = get_user_meta($user_id, 'ap_social_website', true);
        $country  = get_user_meta($user_id, 'ap_country', true);
        $state    = get_user_meta($user_id, 'ap_state', true);
        $city     = get_user_meta($user_id, 'ap_city', true);

        ob_start();
        if (isset($_GET['ap_updated'])) {
            echo '<div class="notice success">' . esc_html__('Profile updated successfully.', 'artpulse') . '</div>';
        }
        ?>
        <form method="post" enctype="multipart/form-data" class="ap-artist-profile-form ap-form-container bg-white rounded-2xl shadow p-6 space-y-4" data-no-ajax="true">
            <?php wp_nonce_field('ap_artist_profile_form', 'ap_artist_profile_nonce'); ?>
            <div>
                <label class="ap-form-label" for="ap_description"><?php esc_html_e('Bio', 'artpulse'); ?></label>
                <textarea class="ap-input" id="ap_description" name="description" rows="5"><?php echo esc_textarea($bio); ?></textarea>
            </div>
            <div>
                <label class="ap-form-label" for="ap_avatar"><?php esc_html_e('Profile Picture', 'artpulse'); ?></label>
                <?php if ($avatar): ?>
                    <img id="ap-avatar-preview" src="<?php echo esc_url($avatar); ?>" alt="" class="mb-2 w-24 h-24 object-cover rounded-full" />
                <?php endif; ?>
                <input class="ap-input" type="file" name="ap_avatar" id="ap_avatar" accept="image/*" />
            </div>
            <div>
                <label class="ap-form-label" for="ap_social_twitter"><?php esc_html_e('Twitter URL', 'artpulse'); ?></label>
                <input class="ap-input" type="url" name="ap_social_twitter" id="ap_social_twitter" value="<?php echo esc_url($twitter); ?>" />
            </div>
            <div>
                <label class="ap-form-label" for="ap_social_instagram"><?php esc_html_e('Instagram URL', 'artpulse'); ?></label>
                <input class="ap-input" type="url" name="ap_social_instagram" id="ap_social_instagram" value="<?php echo esc_url($instagram); ?>" />
            </div>
            <div>
                <label class="ap-form-label" for="ap_social_website"><?php esc_html_e('Website URL', 'artpulse'); ?></label>
                <input class="ap-input" type="url" name="ap_social_website" id="ap_social_website" value="<?php echo esc_url($website); ?>" />
            </div>
            <div>
                <label class="ap-form-label" for="ap_country"><?php esc_html_e('Country', 'artpulse'); ?></label>
                <input class="ap-input ap-address-country ap-address-input" id="ap_country" type="text" name="ap_country" data-selected="<?php echo esc_attr($country); ?>" />
            </div>
            <div>
                <label class="ap-form-label" for="ap_state"><?php esc_html_e('State/Province', 'artpulse'); ?></label>
                <input class="ap-input ap-address-state ap-address-input" id="ap_state" type="text" name="ap_state" data-selected="<?php echo esc_attr($state); ?>" />
            </div>
            <div>
                <label class="ap-form-label" for="ap_city"><?php esc_html_e('City', 'artpulse'); ?></label>
                <input class="ap-input ap-address-city ap-address-input" id="ap_city" type="text" name="ap_city" data-selected="<?php echo esc_attr($city); ?>" />
            </div>
            <input type="hidden" name="address_components" id="ap-address-components" value="<?php echo esc_attr(json_encode(['country'=>$country,'state'=>$state,'city'=>$city])); ?>" />
            <div>
                <button class="ap-form-button nectar-button" type="submit" name="ap_artist_profile_submit"><?php esc_html_e('Save Profile', 'artpulse'); ?></button>
            </div>
        </form>
        <?php
        return ob_get_clean();
    }

    public static function maybe_handle_form(): void {
        if (!is_user_logged_in() || !isset($_POST['ap_artist_profile_submit'])) {
            return;
        }

        if (!isset($_POST['ap_artist_profile_nonce']) || !wp_verify_nonce($_POST['ap_artist_profile_nonce'], 'ap_artist_profile_form')) {
            return;
        }

        $user_id    = get_current_user_id();
        $bio        = sanitize_textarea_field($_POST['description'] ?? '');
        $twitter    = esc_url_raw($_POST['ap_social_twitter'] ?? '');
        $instagram  = esc_url_raw($_POST['ap_social_instagram'] ?? '');
        $website    = esc_url_raw($_POST['ap_social_website'] ?? '');
        $components = [];
        if (!empty($_POST['address_components'])) {
            $decoded = json_decode(stripslashes($_POST['address_components']), true);
            if (is_array($decoded)) {
                $components = $decoded;
            }
        }
        $country = sanitize_text_field($components['country'] ?? '');
        $state   = sanitize_text_field($components['state'] ?? '');
        $city    = sanitize_text_field($components['city'] ?? '');

        update_user_meta($user_id, 'description', $bio);
        update_user_meta($user_id, 'ap_social_twitter', $twitter);
        update_user_meta($user_id, 'ap_social_instagram', $instagram);
        update_user_meta($user_id, 'ap_social_website', $website);
        update_user_meta($user_id, 'ap_country', $country);
        update_user_meta($user_id, 'ap_state', $state);
        update_user_meta($user_id, 'ap_city', $city);

        if (!function_exists('media_handle_upload')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        if (!empty($_FILES['ap_avatar']['tmp_name'])) {
            $uploaded = media_handle_upload('ap_avatar', 0);
            if (!is_wp_error($uploaded)) {
                update_user_meta($user_id, 'ap_custom_avatar', wp_get_attachment_url($uploaded));
            }
        }

        if (function_exists('wc_add_notice')) {
            wc_add_notice(__('Profile updated successfully.', 'artpulse'), 'success');
        }

        wp_safe_redirect(add_query_arg('ap_updated', '1', wp_get_referer()));
        exit;
    }
}
