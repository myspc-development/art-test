<?php
namespace ArtPulse\Frontend;

class RegistrationShortcode
{
    public static function register(): void
    {
        add_shortcode('ap_register', [self::class, 'render']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_scripts']);
    }

    public static function enqueue_scripts(): void
    {
        if (is_user_logged_in()) {
            return;
        }
        wp_enqueue_script('ap-register-js');
    }

    public static function render(): string
    {
        if (is_user_logged_in()) {
            return '<p>' . esc_html__('You are already logged in.', 'artpulse-management') . '</p>';
        }

        ob_start();
        ?>
        <div class="ap-login-forms">
            <div id="ap-register-message" class="ap-form-messages" role="status" aria-live="polite"></div>
            <form id="ap-register-form" class="ap-form-container">
                <p>
                    <label class="ap-form-label" for="ap_reg_username"><?php esc_html_e('Username', 'artpulse-management'); ?></label>
                    <input class="ap-input" id="ap_reg_username" type="text" name="username" required />
                </p>
                <p>
                    <label class="ap-form-label" for="ap_reg_email"><?php esc_html_e('Email', 'artpulse-management'); ?></label>
                    <input class="ap-input" id="ap_reg_email" type="email" name="email" required />
                </p>
                <p>
                    <label class="ap-form-label" for="ap_reg_password"><?php esc_html_e('Password', 'artpulse-management'); ?></label>
                    <input class="ap-input" id="ap_reg_password" type="password" name="password" required />
                </p>
                <p>
                    <label class="ap-form-label" for="ap_reg_display_name"><?php esc_html_e('Display Name', 'artpulse-management'); ?></label>
                    <input class="ap-input" id="ap_reg_display_name" type="text" name="display_name" />
                </p>
                <p>
                    <label class="ap-form-label" for="ap_reg_bio"><?php esc_html_e('Bio', 'artpulse-management'); ?></label>
                    <textarea class="ap-input" id="ap_reg_bio" name="description" rows="4"></textarea>
                </p>
                <p>
                    <label class="ap-form-label" for="ap_country"><?php esc_html_e('Country', 'artpulse-management'); ?></label>
                    <input class="ap-input ap-address-country ap-address-input" id="ap_country" type="text" name="ap_country" />
                </p>
                <p>
                    <label class="ap-form-label" for="ap_state"><?php esc_html_e('State/Province', 'artpulse-management'); ?></label>
                    <input class="ap-input ap-address-state ap-address-input" id="ap_state" type="text" name="ap_state" />
                </p>
                <p>
                    <label class="ap-form-label" for="ap_city"><?php esc_html_e('City', 'artpulse-management'); ?></label>
                    <input class="ap-input ap-address-city ap-address-input" id="ap_city" type="text" name="ap_city" />
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
}
