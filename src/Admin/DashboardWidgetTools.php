<?php
namespace ArtPulse\Admin;

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Admin\UserLayoutManager;

class DashboardWidgetTools
{
    public static function register(): void
    {
        add_action('wp_dashboard_setup', function () {
            if (!current_user_can('manage_options')) {
                return;
            }

            wp_add_dashboard_widget(
                'artpulse_dashboard_widget',
                __('ArtPulse Dashboard', 'artpulse'),
                [self::class, 'render']
            );
        });
        add_action('admin_post_ap_export_widget_config', [self::class, 'handle_export']);
        add_action('admin_post_ap_import_widget_config', [self::class, 'handle_import']);
    }

    public static function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'artpulse'));
        }

        $roles    = wp_roles()->roles;
        $selected = isset($_POST['ap_dashboard_role']) ? sanitize_key($_POST['ap_dashboard_role']) : (isset($_GET['ap_dashboard_role']) ? sanitize_key($_GET['ap_dashboard_role']) : (array_key_first($roles) ?: ''));

        if (isset($_POST['layout'])) {
            check_admin_referer('ap_save_role_layout');
            $selected = sanitize_key($_POST['ap_dashboard_role'] ?? $selected);
            $layout   = json_decode(wp_unslash($_POST['layout']), true) ?: [];
            if (is_array($layout)) {
                UserLayoutManager::save_role_layout($selected, $layout);
                echo '<div class="notice notice-success"><p>' . esc_html__('Layout saved for role.', 'artpulse') . '</p></div>';
            }
        }

        if (isset($_POST['import_role_layout']) && current_user_can('manage_options')) {
            $import_result = UserLayoutManager::import_layout($selected, stripslashes($_POST['import_json'] ?? ''));
            echo '<div class="notice ' . ($import_result ? 'notice-success' : 'notice-error') . '"><p>'
                . ($import_result ? 'Layout imported successfully.' : 'Invalid layout JSON.') . '</p></div>';
        }

        $current  = UserLayoutManager::get_role_layout($selected);
        $defs     = DashboardWidgetRegistry::get_definitions();
        $defs_by_id = [];
        foreach ($defs as $def) {
            $defs_by_id[$def['id']] = $def;
        }
        $all_ids  = array_column($defs, 'id');
        $current_ids = array_column($current, 'id');
        $unused   = array_diff($all_ids, $current_ids);

        echo '<div class="wrap">';
        echo '<h3>' . esc_html__('Dashboard Widget Manager', 'artpulse') . '</h3>';
        echo '<form method="post" id="widget-layout-form" style="margin-bottom:10px">';
        wp_nonce_field('ap_save_role_layout');
        echo '<select name="ap_dashboard_role" onchange="this.form.submit()">';
        foreach ($roles as $key => $role) {
            $sel   = selected($selected, $key, false);
            $label = $role['name'] ?? $key;
            echo "<option value='" . esc_attr($key) . "' $sel>" . esc_html($label) . "</option>";
        }
        echo '</select> ';
        echo '<button type="submit" name="reset_layout" class="button">' . esc_html__('Reset Layout', 'artpulse') . '</button> ';
        echo '<button type="button" id="export-layout" class="button">' . esc_html__('Export', 'artpulse') . '</button> ';
        echo '<label for="import-layout" class="button" style="margin-right:5px;">' . esc_html__('Import', 'artpulse') . '</label>';
        echo '<input type="file" id="import-layout" style="display:none" />';
        echo '<input type="hidden" id="layout_input" name="layout">';
        if (current_user_can('manage_options')) {
            echo '<br><textarea name="import_json" rows="6" cols="60" placeholder="Paste layout JSON..."></textarea><br>';
            echo '<button name="import_role_layout" type="submit" class="button">Import Layout</button> ';
            echo '<button type="button" class="button" onclick="copyExportedLayout()">Copy Export</button>';
            echo '<textarea id="export_json" rows="6" cols="60" readonly>' . esc_textarea(UserLayoutManager::export_layout($selected)) . '</textarea>';
        }
        echo '</form>';

        echo '<div id="custom-widgets">';
        foreach ($current as $item) {
            $id = $item['id'];
            $visible = $item['visible'] ?? true;
            $cb = DashboardWidgetRegistry::get_widget_callback($id);
            if (is_callable($cb)) {
                echo '<div id="' . esc_attr($id) . '" class="ap-widget salient-widget-card" data-id="' . esc_attr($id) . '" data-visible="' . ($visible ? '1' : '0') . '">';
                echo '<div class="widget-handle widget-title">' . esc_html($defs_by_id[$id]['name'] ?? $id);
                echo ' <button type="button" class="widget-toggle button small">' . ($visible ? '🙈 Hide' : '👁 Show') . '</button>';
                echo '</div>';
                if ($visible) {
                    echo '<div class="widget-content">';
                    echo call_user_func($cb);
                    echo '</div>';
                }
                echo '</div>';
            }
        }
        echo '</div>';

        echo '<div class="ap-add-widget" style="margin-top:15px;">';
        echo '<h3>' . esc_html__('Add Widget', 'artpulse') . '</h3>';
        echo '<input type="text" id="widget-search" placeholder="Search widgets..." class="regular-text" style="margin-bottom: 10px;">';
        echo '<ul id="add-widget-panel">';
        foreach ($defs as $def) {
            if (in_array($def['id'], $unused, true)) {
                $id      = esc_attr($def['id']);
                $preview = self::render_widget_preview($def['id']);
                echo '<li><label><input type="checkbox" class="add-widget-check" value="' . $id . '"> <strong>' . esc_html($def['name']) . '</strong>';
                echo '<div class="preview-box">' . $preview . '</div>';
                echo '<small>' . esc_html($def['description']) . '</small></label></li>';
            }
        }
        echo '</ul>';
        echo '</div>';
        echo '</div>';

        echo '<button type="button" id="toggle-preview" class="button">Toggle Preview</button>';
        echo '<h3 style="margin-top: 40px;">Preview Dashboard for ' . esc_html(ucfirst($selected)) . '</h3>';
        echo '<div id="ap-widget-preview-area" class="ap-widget-preview-wrap">';
        $definitions = DashboardWidgetRegistry::get_definitions();
        foreach ($current as $widget) {
            $id = is_array($widget) ? $widget['id'] : $widget;
            $visible = is_array($widget) ? ($widget['visible'] ?? true) : true;

            if (!$visible || !isset($definitions[$id])) {
                continue;
            }
            if (!isset($definitions[$id]['callback']) || !is_callable($definitions[$id]['callback'])) {
                continue;
            }

            echo '<div class="ap-widget-preview-card">';
            echo '<h4>' . esc_html($definitions[$id]['name']) . '</h4>';
            echo '<div class="widget-preview-box">';
            call_user_func($definitions[$id]['callback']);
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';

        echo '</div>';
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

            foreach ($widgets as $item) {
                if (is_array($item) && isset($item['id'])) {
                    $id  = sanitize_key($item['id']);
                    $vis = isset($item['visible']) ? filter_var($item['visible'], FILTER_VALIDATE_BOOLEAN) : true;
                } else {
                    $id  = sanitize_key($item);
                    $vis = true;
                }
                if (in_array($id, $valid_ids, true)) {
                    $ordered[] = ['id' => $id, 'visible' => $vis];
                }
            }

            $sanitized[$role_key] = $ordered;
        }

        update_option('ap_dashboard_widget_config', $sanitized);

        wp_safe_redirect(add_query_arg('dw_import_success', '1', admin_url('admin.php?page=artpulse-dashboard-widgets')));
        exit;
    }

    /**
     * Render the preview HTML for a widget by ID.
     */
    public static function render_widget_preview(string $id): string
    {
        $cb = DashboardWidgetRegistry::get_widget_callback($id);
        if (!is_callable($cb)) {
            return '';
        }

        ob_start();
        call_user_func($cb);
        return ob_get_clean();
    }

    /**
     * Retrieve the default widget layout for a role.
     */
    public static function get_default_layout(string $role): array
    {
        $config = get_option('ap_dashboard_widget_config', []);
        if (isset($config[$role]) && is_array($config[$role])) {
            return $config[$role];
        }

        $defs = DashboardWidgetRegistry::get_definitions();
        return array_map(
            fn($def) => ['id' => $def['id'], 'visible' => true],
            $defs
        );
    }

    /**
     * Output dashboard widgets for a specific role.
     * Layouts are loaded via UserLayoutManager based on
     * the provided role.
     */
    public static function render_dashboard_widgets(string $role): void
    {
        $layout = UserLayoutManager::get_role_layout($role);

        foreach ($layout as $widget) {
            $id = is_array($widget) ? $widget['id'] : $widget;
            $visible = is_array($widget) ? ($widget['visible'] ?? true) : true;
            if (!$visible) {
                continue;
            }

            $cb = DashboardWidgetRegistry::get_widget_callback($id);
            if (is_callable($cb)) {
                echo '<div class="ap-widget">';
                echo call_user_func($cb);
                echo '</div>';
            }
        }
    }
}
