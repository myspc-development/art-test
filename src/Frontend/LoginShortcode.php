<?php
namespace ArtPulse\Frontend;

class LoginShortcode
{
    public static function register(): void
    {
        add_shortcode('ap_login', [self::class, 'render']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_scripts']);
        add_action('wp_ajax_ap_do_login', [self::class, 'ajax_login']);
        add_action('wp_ajax_nopriv_ap_do_login', [self::class, 'ajax_login']);
        add_action('wp_ajax_ap_do_register', [self::class, 'ajax_register']);
        add_action('wp_ajax_nopriv_ap_do_register', [self::class, 'ajax_register']);
    }

    public static function enqueue_scripts(): void
    {
        if (is_user_logged_in()) {
            return;
        }
        wp_enqueue_script('ap-login-js');
    }

    public static function render(): string
    {
        if (is_user_logged_in()) {
            return '<p>' . esc_html__('You are already logged in.', 'artpulse-management') . '</p>';
        }

        ob_start();
        ?>
        <div class="ap-login-forms">
            <div id="ap-login-message" class="ap-form-messages" role="status" aria-live="polite"></div>
            <form id="ap-login-form" class="ap-form-container">
                <p>
                    <label class="ap-form-label" for="ap_login_username"><?php esc_html_e('Username or Email', 'artpulse-management'); ?></label>
                    <input class="ap-form-input" id="ap_login_username" type="text" name="username" required />
                </p>
                <p>
                    <label class="ap-form-label" for="ap_login_password"><?php esc_html_e('Password', 'artpulse-management'); ?></label>
                    <input class="ap-form-input" id="ap_login_password" type="password" name="password" required />
                </p>
                <p>
                    <button class="ap-form-button" type="submit"><?php esc_html_e('Login', 'artpulse-management'); ?></button>
                </p>
            </form>

            <hr />

            <div id="ap-register-message" class="ap-form-messages" role="status" aria-live="polite"></div>
            <form id="ap-register-form" class="ap-form-container">
                <p>
                    <label class="ap-form-label" for="ap_reg_username"><?php esc_html_e('Username', 'artpulse-management'); ?></label>
                    <input class="ap-form-input" id="ap_reg_username" type="text" name="username" required />
                </p>
                <p>
                    <label class="ap-form-label" for="ap_reg_email"><?php esc_html_e('Email', 'artpulse-management'); ?></label>
                    <input class="ap-form-input" id="ap_reg_email" type="email" name="email" required />
                </p>
                <p>
                    <label class="ap-form-label" for="ap_reg_password"><?php esc_html_e('Password', 'artpulse-management'); ?></label>
                    <input class="ap-form-input" id="ap_reg_password" type="password" name="password" required />
                </p>
                <p>
                    <label class="ap-form-label" for="ap_reg_display_name"><?php esc_html_e('Display Name', 'artpulse-management'); ?></label>
                    <input class="ap-form-input" id="ap_reg_display_name" type="text" name="display_name" />
                </p>
                <p>
                    <label class="ap-form-label" for="ap_reg_bio"><?php esc_html_e('Bio', 'artpulse-management'); ?></label>
                    <textarea class="ap-form-textarea" id="ap_reg_bio" name="description" rows="4"></textarea>
                </p>
                <p>
                    <label class="ap-form-label" for="ap_country"><?php esc_html_e('Country', 'artpulse-management'); ?></label>
                    <input class="ap-form-input ap-address-country ap-address-input" id="ap_country" type="text" name="ap_country" />
                </p>
                <p>
                    <label class="ap-form-label" for="ap_state"><?php esc_html_e('State/Province', 'artpulse-management'); ?></label>
                    <input class="ap-form-input ap-address-state ap-address-input" id="ap_state" type="text" name="ap_state" />
                </p>
                <p>
                    <label class="ap-form-label" for="ap_city"><?php esc_html_e('City', 'artpulse-management'); ?></label>
                    <input class="ap-form-input ap-address-city ap-address-input" id="ap_city" type="text" name="ap_city" />
                </p>
                <input type="hidden" name="address_components" id="ap_address_components" />
                <p>
                    <button class="ap-form-button" type="submit"><?php esc_html_e('Register', 'artpulse-management'); ?></button>
                </p>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function ajax_login(): void
    {
        check_ajax_referer('ap_login_nonce', 'nonce');

        $creds = [
            'user_login'    => sanitize_user($_POST['username'] ?? ''),
            'user_password' => $_POST['password'] ?? '',
            'remember'      => true,
        ];

        $user = wp_signon($creds, false);

        if (is_wp_error($user)) {
            wp_send_json_error(['message' => $user->get_error_message()]);
        }

        wp_send_json_success(['message' => __('Login successful', 'artpulse-management')]);
    }

    public static function ajax_register(): void
    {
        check_ajax_referer('ap_login_nonce', 'nonce');

        if (!apply_filters('ap_registration_allowed', true)) {
            wp_send_json_error(['message' => __('Registration is currently disabled.', 'artpulse-management')]);
        }

        $username      = sanitize_user($_POST['username'] ?? '');
        $email         = sanitize_email($_POST['email'] ?? '');
        $password      = $_POST['password'] ?? '';
        $display_name  = sanitize_text_field($_POST['display_name'] ?? '');
        $bio           = sanitize_textarea_field($_POST['description'] ?? '');
        $components    = [];
        if (!empty($_POST['address_components'])) {
            $decoded = json_decode(stripslashes($_POST['address_components']), true);
            if (is_array($decoded)) {
                $components = $decoded;
            }
        }
        $country = isset($components['country']) ? sanitize_text_field($components['country']) : '';
        $state   = isset($components['state']) ? sanitize_text_field($components['state']) : '';
        $city    = isset($components['city']) ? sanitize_text_field($components['city']) : '';

        $min_length = (int) apply_filters('ap_min_password_length', 8);
        if (
            strlen($password) < $min_length ||
            !preg_match('/[A-Za-z]/', $password) ||
            !preg_match('/\d/', $password)
        ) {
            wp_send_json_error([
                'message' => sprintf(
                    /* translators: %d: minimum password length */
                    __('Password must be at least %d characters long and include both letters and numbers.', 'artpulse-management'),
                    $min_length
                ),
            ]);
        }

        $result = wp_create_user($username, $password, $email);
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        // Auto login the new user
        wp_set_current_user($result);
        wp_set_auth_cookie($result);

        if ($display_name) {
            wp_update_user([
                'ID'           => $result,
                'display_name' => $display_name,
            ]);
        }
        if ($bio !== '') {
            update_user_meta($result, 'description', $bio);
        }
        if ($country !== '') {
            update_user_meta($result, 'ap_country', $country);
        }
        if ($state !== '') {
            update_user_meta($result, 'ap_state', $state);
        }
        if ($city !== '') {
            update_user_meta($result, 'ap_city', $city);
        }

        wp_send_json_success([
            'message' => __('Registration successful', 'artpulse-management'),
        ]);
    }
}
