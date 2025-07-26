<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', 'ap_register_api_keys_page');

function ap_register_api_keys_page(): void
{
    add_submenu_page(
        'artpulse-settings',
        __('Partner API Keys', 'artpulse'),
        __('API Keys', 'artpulse'),
        'manage_options',
        'ap-api-keys',
        'ap_render_api_keys_page'
    );
}

function ap_render_api_keys_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('Insufficient permissions', 'artpulse'));
    }

    global $wpdb;
    $table = $wpdb->prefix . 'ap_api_keys';

    // Create new key
    if (isset($_POST['ap_create_api_key']) && check_admin_referer('ap_create_api_key')) {
        $scopes = sanitize_text_field($_POST['scopes'] ?? 'read:events');
        $key    = bin2hex(random_bytes(16));
        $hash   = hash('sha256', $key);
        $wpdb->insert($table, [
            'key_hash'   => $hash,
            'scopes'     => $scopes,
            'created_at' => current_time('mysql'),
        ]);
        $msg = sprintf(__('New API key generated: %s', 'artpulse'), esc_html($key));
        \ArtPulse\Dashboard\WidgetVisibilityManager::add_admin_notice($msg);
    }

    // Delete key
    if (
        isset($_POST['ap_delete_api_key']) &&
        isset($_POST['id']) &&
        check_admin_referer('ap_delete_api_key_' . $_POST['id'])
    ) {
        $wpdb->delete($table, ['id' => absint($_POST['id'])]);
        \ArtPulse\Dashboard\WidgetVisibilityManager::add_admin_notice(__('API key deleted.', 'artpulse'));
    }

    // View scopes
    $view_scopes = '';
    if (isset($_POST['ap_view_scopes']) && check_admin_referer('ap_view_scopes')) {
        $key = sanitize_text_field($_POST['key']);
        $hash = hash('sha256', $key);
        $sql  = $wpdb->prepare("SELECT scopes FROM $table WHERE key_hash = %s", $hash);
        $view_scopes = $wpdb->get_var($sql);
        if ($view_scopes === null) {
            \ArtPulse\Dashboard\WidgetVisibilityManager::add_admin_notice(__('Key not found.', 'artpulse'), 'error');
            $view_scopes = '';
        }
    }

    $keys = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Partner API Keys', 'artpulse'); ?></h1>

        <h2><?php esc_html_e('Generate Key', 'artpulse'); ?></h2>
        <form method="post">
            <?php wp_nonce_field('ap_create_api_key'); ?>
            <label>
                <?php esc_html_e('Scopes (comma separated)', 'artpulse'); ?>
                <input type="text" name="scopes" value="read:events" style="width:200px" />
            </label>
            <?php submit_button(__('Generate', 'artpulse'), 'primary', 'ap_create_api_key', false); ?>
        </form>

        <h2><?php esc_html_e('Existing Keys', 'artpulse'); ?></h2>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('ID', 'artpulse'); ?></th>
                    <th><?php esc_html_e('Key', 'artpulse'); ?></th>
                    <th><?php esc_html_e('Scopes', 'artpulse'); ?></th>
                    <th><?php esc_html_e('Created', 'artpulse'); ?></th>
                    <th><?php esc_html_e('Actions', 'artpulse'); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($keys as $k) : ?>
                <tr>
                    <td><?php echo esc_html($k->id); ?></td>
                    <td><code><?php echo esc_html(substr($k->key_hash, 0, 10)); ?>...</code></td>
                    <td><?php echo esc_html($k->scopes); ?></td>
                    <td><?php echo esc_html($k->created_at); ?></td>
                    <td>
                        <form method="post" style="display:inline">
                            <?php wp_nonce_field('ap_delete_api_key_' . $k->id); ?>
                            <input type="hidden" name="id" value="<?php echo esc_attr($k->id); ?>" />
                            <input type="submit" name="ap_delete_api_key" class="button"
                                   value="<?php esc_attr_e('Delete', 'artpulse'); ?>" />
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <h2><?php esc_html_e('Check Scopes', 'artpulse'); ?></h2>
        <form method="post">
            <?php wp_nonce_field('ap_view_scopes'); ?>
            <input type="text" name="key" placeholder="<?php esc_attr_e('API key', 'artpulse'); ?>"
                   style="width:250px" />
            <?php submit_button(__('View Scopes', 'artpulse'), 'secondary', 'ap_view_scopes', false); ?>
            <?php if ($view_scopes !== '') : ?>
                <p><?php esc_html_e('Scopes:', 'artpulse'); ?> <?php echo esc_html($view_scopes); ?></p>
            <?php endif; ?>
        </form>
    </div>
    <?php
}
