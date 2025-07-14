<?php
if (!defined('ABSPATH')) { exit; }

function ap_render_settings_page() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_admin_referer('ap_save_settings');
        update_option('ap_github_repo_url', sanitize_text_field($_POST['ap_github_repo_url'] ?? ''));
        update_option('ap_github_token', sanitize_text_field($_POST['ap_github_token'] ?? ''));
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved.', 'artpulse') . '</p></div>';
    }
    $repo  = get_option('ap_github_repo_url');
    $token = get_option('ap_github_token');
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('ArtPulse Settings', 'artpulse'); ?></h1>
        <h2 class="nav-tab-wrapper" id="ap-settings-nav">
            <a href="#advanced" class="nav-tab nav-tab-active" data-tab="advanced">Advanced</a>
        </h2>
        <section class="ap-settings-section" data-tab="advanced">
            <form method="post">
                <?php wp_nonce_field('ap_save_settings'); ?>
                <table class="form-table">
                    <tr>
                      <th scope="row"><label for="ap_github_repo_url">GitHub Repo URL</label></th>
                      <td><input type="text" name="ap_github_repo_url" value="<?php echo esc_attr($repo); ?>" size="50" />
                      <p class="description">e.g., https://github.com/your-org/artpulse-plugin</p></td>
                    </tr>
                    <tr>
                      <th scope="row"><label for="ap_github_token">GitHub Access Token</label></th>
                      <td><input type="password" name="ap_github_token" value="<?php echo esc_attr($token); ?>" size="50" />
                      <p class="description">Optional – to avoid GitHub rate limits</p></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </section>
    </div>
    <?php
}
