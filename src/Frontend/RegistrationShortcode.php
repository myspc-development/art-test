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
                    <label class="ap-form-label" for="ap_continue_as"><?php esc_html_e('Continue as', 'artpulse-management'); ?></label>
                    <select class="ap-form-select" id="ap_continue_as" name="continue_as">
                        <option value="artist"><?php esc_html_e('Artist', 'artpulse-management'); ?></option>
                        <option value="organization"><?php esc_html_e('Organization', 'artpulse-management'); ?></option>
                    </select>
                </p>
                <p>
                    <button class="ap-form-button" type="submit"><?php esc_html_e('Register', 'artpulse-management'); ?></button>
                </p>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
}
