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
        wp_enqueue_script('ap-auth-js');
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
                    <input class="ap-input" id="ap_login_username" type="text" name="username" required />
                </p>
                <p>
                    <label class="ap-form-label" for="ap_login_password"><?php esc_html_e('Password', 'artpulse-management'); ?></label>
                    <input class="ap-input" id="ap_login_password" type="password" name="password" required />
                </p>
                <p>
                    <button class="ap-form-button nectar-button" type="submit"><?php esc_html_e('Login', 'artpulse-management'); ?></button>
                </p>
            </form>

            <?php echo \ArtPulse\Integration\OAuthManager::render_buttons(); ?>

            <hr />

            <?php
            $template_path = plugin_dir_path(__FILE__) . '../../templates/partials/registration-form.php';
            if (file_exists($template_path)) {
                include $template_path;
            }
            ?>
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

        $opts = get_option('artpulse_settings', []);
        if (!empty($opts['enforce_two_factor']) && !get_user_meta($user->ID, 'two_factor_enabled', true)) {
            wp_clear_auth_cookie();
            wp_send_json_error(['message' => __('Two-factor authentication is required.', 'artpulse-management')]);
        }

        $roles  = (array) $user->roles;
        $target = \ArtPulse\Core\Plugin::get_user_dashboard_url();
        if (in_array('organization', $roles, true)) {
            $target = \ArtPulse\Core\Plugin::get_org_dashboard_url();
        } elseif (in_array('artist', $roles, true)) {
            $target = \ArtPulse\Core\Plugin::get_artist_dashboard_url();
        }

        wp_send_json_success([
            'message'       => __('Login successful', 'artpulse-management'),
            'dashboardUrl'  => $target,
        ]);
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
        $role          = sanitize_key($_POST['role'] ?? 'member');
        $allowed_roles = ['member', 'artist', 'organization'];
        if (!in_array($role, $allowed_roles, true)) {
            $role = 'member';
        }
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

        // Assign the selected role
        wp_update_user([
            'ID'   => $result,
            'role' => $role,
        ]);

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
            'message' => __('Registration successful. Redirecting to your dashboard…', 'artpulse-management'),
        ]);
    }
}
