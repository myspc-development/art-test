<?php
namespace ArtPulse\Admin;

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Admin\UserLayoutManager;

class DashboardWidgetTools
{
    public static function register(): void
    {
        add_action('wp_dashboard_setup', [self::class, 'add_dashboard_widgets']);
        add_action('admin_post_ap_export_widget_config', [self::class, 'handle_export']);
        add_action('admin_post_ap_import_widget_config', [self::class, 'handle_import']);
    }

    public static function add_dashboard_widgets(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        wp_add_dashboard_widget(
            'artpulse_dashboard_widget',
            __('ArtPulse Dashboard', 'artpulse'),
            [self::class, 'render']
        );
    }

    public static function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'artpulse'));
        }

        $all_roles = wp_roles()->roles;
        $selected  = isset($_GET['ap_role']) ? sanitize_key($_GET['ap_role']) : (array_key_first($all_roles) ?: '');
        if (isset($_POST['ap_save_role_layout'])) {
            check_admin_referer('ap_save_role_layout');
            $selected = sanitize_key($_POST['ap_role']);
            $layout   = UserLayoutManager::get_layout(get_current_user_id());
            UserLayoutManager::save_role_layout($selected, $layout);
            echo '<div class="notice notice-success"><p>' . esc_html__('Layout saved for role.', 'artpulse') . '</p></div>';
        }

        echo '<div class="wrap">';
        echo '<h3>' . esc_html__('Welcome to the ArtPulse Dashboard', 'artpulse') . '</h3>';
        echo '<form method="post" style="margin-bottom:10px">';
        wp_nonce_field('ap_save_role_layout');
        echo '<select name="ap_role">';
        foreach ($all_roles as $key => $role) {
            $sel = selected($selected, $key, false);
            $label = $role['name'] ?? $key;
            echo "<option value='" . esc_attr($key) . "' $sel>" . esc_html($label) . "</option>";
        }
        echo '</select> ';
        echo '<button type="submit" name="ap_save_role_layout" class="button">' . esc_html__('Save Role Layout', 'artpulse') . '</button>';
        echo '</form>';

        self::render_dashboard_widgets($selected);
        echo '</div>';

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

    /**
     * Export the saved dashboard widget layout as JSON.
     */
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

    /**
     * Parse an uploaded JSON file and update the widget layout option.
     */
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

        $valid_ids = array_column(DashboardWidgetRegistry::get_definitions(), 'id');
        $sanitized = [];

        foreach ($data as $role => $widgets) {
            if (!is_array($widgets)) {
                continue;
            }

            $role_key = sanitize_key($role);
            $ordered  = [];

            foreach ($widgets as $w) {
                $key = sanitize_key($w);
                if (in_array($key, $valid_ids, true)) {
                    $ordered[] = $key;
                }
            }

            $sanitized[$role_key] = $ordered;
        }

        update_option('ap_dashboard_widget_config', $sanitized);

        wp_safe_redirect(add_query_arg('dw_import_success', '1', admin_url('admin.php?page=artpulse-dashboard-widgets')));
        exit;
    }

    /**
     * Retrieve the default widget layout for a role.
     */
    public static function get_default_layout(string $role): array
    {
        $config = get_option('ap_dashboard_widget_config', []);
        if (isset($config[$role]) && is_array($config[$role])) {
            return array_map('sanitize_key', $config[$role]);
        }

        $defs = DashboardWidgetRegistry::get_definitions();
        return array_column($defs, 'id');
    }

    /**
     * Output dashboard widgets for a specific role.
     * Layouts are loaded via UserLayoutManager based on
     * the provided role.
     */
    public static function render_dashboard_widgets(string $role): void
    {
        $layout = UserLayoutManager::get_role_layout($role);

        foreach ($layout as $id) {
            $cb = DashboardWidgetRegistry::get_widget_callback($id);
            if (is_callable($cb)) {
                echo '<div class="ap-widget">';
                echo call_user_func($cb);
                echo '</div>';
            }
        }
    }
}
