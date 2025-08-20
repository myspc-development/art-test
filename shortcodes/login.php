<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the front-end login form.
 */
function artpulse_login_form(): string
{
    if (is_user_logged_in()) {
        return '';
    }

    if (!empty($_POST['ap_login_nonce']) && wp_verify_nonce($_POST['ap_login_nonce'], 'ap_login') && empty($_POST['ap_hp'])) {
        $creds = [
            'user_login'    => sanitize_user($_POST['username'] ?? ''),
            'user_password' => $_POST['password'] ?? '',
            'remember'      => true,
        ];
        $user = wp_signon($creds, false);
        if (!is_wp_error($user)) {
            wp_safe_redirect(home_url('/dashboard'));
            exit;
        }
    }

    ob_start();
    ?>
    <form class="ap-login-form" method="post">
        <p>
            <label for="ap_login_username"><?php esc_html_e('Username or Email', 'artpulse'); ?></label>
            <input id="ap_login_username" type="text" name="username" required />
        </p>
        <p>
            <label for="ap_login_password"><?php esc_html_e('Password', 'artpulse'); ?></label>
            <input id="ap_login_password" type="password" name="password" required />
        </p>
        <p style="display:none">
            <label for="ap_hp">Leave this field empty</label>
            <input id="ap_hp" type="text" name="ap_hp" value="" />
        </p>
        <?php wp_nonce_field('ap_login', 'ap_login_nonce'); ?>
        <p><button type="submit"><?php esc_html_e('Log In', 'artpulse'); ?></button></p>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('ap_login', 'artpulse_login_form');
