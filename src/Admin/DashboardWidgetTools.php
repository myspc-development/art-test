<?php
namespace ArtPulse\Admin;

class DashboardWidgetTools
{
    public static function register(): void
    {
        add_action('admin_post_ap_export_widget_config', [self::class, 'handle_export']);
        add_action('admin_post_ap_import_widget_config', [self::class, 'handle_import']);
    }

    public static function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'artpulse'));
        }

        if (isset($_GET['dw_import_success'])) {
            echo '<div class="notice notice-success"><p>' . esc_html__('Widget layouts imported.', 'artpulse') . '</p></div>';
        } elseif (isset($_GET['dw_import_error'])) {
            echo '<div class="notice notice-error"><p>' . esc_html__('Invalid layout file.', 'artpulse') . '</p></div>';
        }
        ?>
        <details>
            <summary><?php esc_html_e('Advanced: Import/Export JSON', 'artpulse'); ?></summary>
            <h2><?php esc_html_e('Export Layouts', 'artpulse'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('ap_export_widget_config'); ?>
                <input type="hidden" name="action" value="ap_export_widget_config" />
                <button type="submit" class="button"><?php esc_html_e('Download JSON', 'artpulse'); ?></button>
            </form>
            <hr/>
            <h2><?php esc_html_e('Import Layouts', 'artpulse'); ?></h2>
            <form method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('ap_import_widget_config'); ?>
                <input type="hidden" name="action" value="ap_import_widget_config" />
                <input type="file" name="ap_widget_file" accept=".json" required />
                <button type="submit" class="button button-primary" style="margin-top:10px;">
                    <?php esc_html_e('Upload', 'artpulse'); ?>
                </button>
            </form>
        </details>
        <?php
    }

    public static function handle_export(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'artpulse'));
        }
        check_admin_referer('ap_export_widget_config');

        $config = get_option('ap_dashboard_widget_config', []);
        $json   = wp_json_encode($config, JSON_PRETTY_PRINT);
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="ap-dashboard-widgets.json"');
        echo $json;
        exit;
    }

    public static function handle_import(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'artpulse'));
        }
        check_admin_referer('ap_import_widget_config');
        if (!isset($_FILES['ap_widget_file']) || empty($_FILES['ap_widget_file']['tmp_name'])) {
            wp_safe_redirect(add_query_arg('dw_import_error', '1', wp_get_referer() ?: admin_url('admin.php?page=artpulse-dashboard-widgets')));
            exit;
        }
        $json = file_get_contents($_FILES['ap_widget_file']['tmp_name']);
        $data = json_decode($json, true);
        if (!is_array($data)) {
            wp_safe_redirect(add_query_arg('dw_import_error', '1', wp_get_referer() ?: admin_url('admin.php?page=artpulse-dashboard-widgets')));
            exit;
        }
        $sanitized = [];
        foreach ($data as $role => $widgets) {
            if (!is_array($widgets)) {
                continue;
            }
            $role_key = sanitize_key($role);
            $ordered  = [];
            foreach ($widgets as $w) {
                $ordered[] = sanitize_key($w);
            }
            $sanitized[$role_key] = $ordered;
        }
        update_option('ap_dashboard_widget_config', $sanitized);
        wp_safe_redirect(add_query_arg('dw_import_success', '1', admin_url('admin.php?page=artpulse-dashboard-widgets')));
        exit;
    }
}
