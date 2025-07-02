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
        wp_enqueue_script('ap-auth-js');
    }

    public static function render(): string
    {
        if (is_user_logged_in()) {
            return '<p>' . esc_html__('You are already logged in.', 'artpulse') . '</p>';
        }

        ob_start();
        ?>
        <div class="ap-login-forms">
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
}
