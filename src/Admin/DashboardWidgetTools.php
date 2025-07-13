<?php
namespace ArtPulse\Admin;

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Admin\UserLayoutManager;
use ArtPulse\Core\DashboardController;

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
        add_action('admin_menu', function () {
            add_submenu_page(
                'index.php',
                'Dashboard Layout Help',
                'Layout Help',
                'manage_options',
                'dashboard-layout-help',
                [self::class, 'render_help_page']
            );
        });
        add_action('admin_post_ap_export_widget_config', [self::class, 'handle_export']);
        add_action('admin_post_ap_import_widget_config', [self::class, 'handle_import']);
    }

    public static function render_help_page(): void
    {
        echo '<div class="wrap"><h1>Dashboard Layout Help</h1>';
        include plugin_dir_path(__FILE__) . '/partials/help-guide.php';
        echo '</div>';
    }

    public static function render(): void
    {
        $roles       = get_editable_roles();
        $selected    = sanitize_text_field($_GET['ap_dashboard_role'] ?? '');

        if (isset($_POST['ap_dashboard_role'])) {
            $selected = sanitize_text_field($_POST['ap_dashboard_role']);
        }

        $editable = array_keys($roles);
        if (!in_array($selected, $editable, true) && !current_user_can('manage_options')) {
            wp_die('Invalid role');
        }

        if (!ap_user_can_edit_layout($selected)) {
            wp_die(__('Access denied.', 'artpulse'));
        }

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Widget Manager accessed by user: ' . get_current_user_id());
            error_log('User roles: ' . wp_json_encode(wp_get_current_user()->roles));
        }

        if (isset($_POST['layout'])) {
            check_admin_referer('ap_save_role_layout');
            $selected = sanitize_key($_POST['ap_dashboard_role'] ?? $selected);
            $layout   = json_decode(wp_unslash($_POST['layout']), true) ?: [];
            if (is_array($layout)) {
                UserLayoutManager::save_role_layout($selected, $layout);
                echo '<div class="notice notice-success"><p>' . esc_html__('Layout saved for role.', 'artpulse') . '</p></div>';
            }
        }

        if (isset($_POST['reset_layout'])) {
            check_admin_referer('ap_save_role_layout');
            $selected = sanitize_key($_POST['ap_dashboard_role'] ?? $selected);
            UserLayoutManager::reset_layout_for_role($selected);
            echo '<div class="notice notice-success"><p>' . esc_html__('Layout reset for role.', 'artpulse') . '</p></div>';
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
        echo '<p><a href="' . admin_url('index.php?page=dashboard-layout-help') . '" class="button">📘 View Help Guide</a></p>';
        echo '<h3>' . esc_html__('Dashboard Widget Manager', 'artpulse') . '</h3>';
        echo '<form method="post" id="widget-layout-form" style="margin-bottom:10px">';
        wp_nonce_field('ap_save_role_layout');
        echo '<select id="ap-role-selector" name="ap_dashboard_role">';
        foreach ($roles as $key => $role) {
            $sel   = selected($selected, $key, false);
            $label = $role['name'] ?? $key;
            echo "<option value='" . esc_attr($key) . "' $sel>" . esc_html($label) . "</option>";
        }
        echo '</select> ';
        echo '<span class="description">Drag to reorder. Toggle visibility. Click save to store layout.</span>';
        echo '<button type="submit" name="reset_layout" class="button">' . esc_html__('Reset Layout', 'artpulse') . '</button> ';
        echo '<button type="button" id="export-layout" class="button">' . esc_html__('Export', 'artpulse') . '</button> ';
        echo '<label for="import-layout" class="button" style="margin-right:5px;">' . esc_html__('Import', 'artpulse') . '</label>';
        echo '<input type="file" id="import-layout" style="display:none" />';
        echo '<input type="hidden" id="layout_input" name="layout">';
        echo '<p><button type="submit" id="save-layout-btn" class="button button-primary">💾 Save Layout</button></p>';
        if (current_user_can('manage_options')) {
            echo '<br><textarea name="import_json" rows="6" cols="60" placeholder="Paste layout JSON..."></textarea><br>';
            echo '<button name="import_role_layout" type="submit" class="button">Import Layout</button> ';
            echo '<button type="button" class="button" onclick="copyExportedLayout()">Copy Export</button>';
            echo '<textarea id="export_json" rows="6" cols="60" readonly>' . esc_textarea(UserLayoutManager::export_layout($selected)) . '</textarea>';
        }
        echo '</form>';

        echo '<div id="ap-widget-list">';
        foreach ($current as $item) {
            $id = $item['id'];
            $visible = $item['visible'] ?? true;
            $cb = DashboardWidgetRegistry::get_widget_callback($id);
            if (is_callable($cb)) {
                $icon = esc_html($defs_by_id[$id]['icon'] ?? '');
                $title = esc_html($defs_by_id[$id]['name'] ?? $id);
                echo '<div class="ap-widget-card' . ($visible ? '' : ' is-hidden') . '" role="group" aria-label="Widget: ' . $title . '" data-widget-id="' . esc_attr($id) . '" data-id="' . esc_attr($id) . '" data-visible="' . ($visible ? '1' : '0') . '">';
                echo '<div class="ap-widget-header">';
                echo '<span class="drag-handle" title="Drag to reorder">&#9776;</span>';
                echo '<span class="ap-widget-icon">' . $icon . '</span>';
                echo '<span class="ap-widget-title">' . $title . '</span>';
                echo '<div class="ap-widget-controls">';
                echo '<label class="toggle-switch" title="Toggle Widget"><input type="checkbox" class="widget-toggle" aria-label="Toggle Widget"' . checked($visible, true, false) . ' /><span class="slider"></span></label>';
                echo '<button type="button" class="widget-remove" title="Remove Widget">&#x2716;</button>';
                echo '</div></div>';
                if ($visible) {
                    echo '<div class="ap-widget-content">';
                    echo call_user_func($cb);
                    echo '</div>';
                }
                echo '</div>';
            }
        }
        echo '</div>';

        echo '<div class="ap-add-widget" style="margin-top:15px;">';
        echo '<h3>' . esc_html__('Add Widget', 'artpulse') . '</h3>';
        echo '<input type="text" id="ap-widget-search" placeholder="Search widgets..." oninput="apSearchWidgets(this.value)" class="regular-text" style="margin-bottom: 10px;">';
        $categories = array_unique(array_filter(array_column($defs, 'category')));
        if ($categories) {
            echo '<select id="ap-widget-category-filter" onchange="apFilterWidgetsByCategory(this.value)" style="margin-left:5px;">';
            echo '<option value="">' . esc_html__('All Categories', 'artpulse') . '</option>';
            foreach ($categories as $cat) {
                echo '<option value="' . esc_attr($cat) . '">' . esc_html(ucfirst($cat)) . '</option>';
            }
            echo '</select>';
        }
        echo '<ul id="add-widget-panel">';
        foreach ($defs as $def) {
            if (in_array($def['id'], $unused, true)) {
                if (!empty($def['roles']) && !in_array($selected, (array) $def['roles'], true)) {
                    continue;
                }
                $id       = esc_attr($def['id']);
                $preview  = self::render_widget_preview($def['id']);
                $icon     = esc_html($def['icon']);
                $category = esc_attr($def['category'] ?? '');
                $name_attr = esc_attr($def['name']);
                $desc_attr = esc_attr($def['description']);
                echo '<li class="widget-card" data-category="' . $category . '" data-name="' . $name_attr . '" data-desc="' . $desc_attr . '"><label><input type="checkbox" class="add-widget-check" value="' . $id . '"> ';
                echo '<span class="widget-icon">' . $icon . '</span> <strong>' . esc_html($def['name']) . '</strong>';
                echo '<div class="widget-preview-box">' . $preview . '</div>';
                echo '<small>' . esc_html($def['description']) . '</small></label></li>';
            }
        }
        echo '</ul>';
        echo '</div>';
        echo '</div>';

        echo '<button type="button" id="toggle-preview" class="button">Toggle Preview</button>';
        echo '<h3 style="margin-top: 40px;">Preview Dashboard for ' . esc_html(ucfirst($selected)) . '</h3>';
        echo '<div id="ap-widget-preview-area" class="ap-widget-preview-wrap">';
        self::render_preview_dashboard($selected);
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
     * Render widget selection cards for the Add Widget modal.
     *
     * @param array $available_widgets
     */
    public static function render_add_widget_modal(array $available_widgets): void
    {
        foreach ($available_widgets as $id => $def) {
            echo '<div class="widget-card" ' .
                'data-id="' . esc_attr($id) . '" ' .
                'data-name="' . esc_attr($def['label']) . '" ' .
                'data-desc="' . esc_attr($def['description']) . '" ' .
                'data-category="' . esc_attr($def['category'] ?? '') . '">';

            echo '<span class="widget-icon">' . esc_html($def['icon']) . '</span>';
            echo '<strong class="widget-label">' . esc_html($def['label']) . '</strong>';
            echo '<p class="widget-description">' . esc_html($def['description']) . '</p>';
            echo '</div>';
        }
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
     * Render a preview dashboard for a role.
     */
    public static function render_preview_dashboard(string $role): void
    {
        if (!ap_user_can_edit_layout($role)) {
            return;
        }

        $layout  = UserLayoutManager::get_role_layout($role);

        echo '<div class="ap-preview-dashboard">';
        $defs = DashboardWidgetRegistry::get_definitions();
        $has_visible = false;
        foreach ($layout as $widget) {
            $id = is_array($widget) ? $widget['id'] : $widget;
            $visible = is_array($widget) ? ($widget['visible'] ?? true) : true;

            if (!$visible || !isset($defs[$id])) {
                continue;
            }

            $has_visible = true;

            $cb = DashboardWidgetRegistry::get_widget_callback($id);
            if (!is_callable($cb)) {
                continue;
            }

            echo '<div class="ap-preview-widget">';
            echo '<h4>' . esc_html($defs[$id]['name']) . '</h4>';
            echo '<div class="ap-preview-content">';
            try {
                call_user_func($cb);
            } catch (\Throwable $e) {
                echo '<div class="notice notice-error"><p>Error rendering widget: ' . esc_html($e->getMessage()) . '</p></div>';
            }
            echo '</div></div>';
        }

        if (!$has_visible) {
            echo '<div class="ap-preview-empty notice notice-info"><p>No widgets are currently visible for this layout.</p></div>';
        }

        echo '</div>';
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
        $user_id = get_current_user_id();
        $layout  = UserLayoutManager::get_layout_for_user($user_id);

        foreach ($layout as $widget) {
            $id = is_array($widget) ? $widget['id'] : $widget;
            $visible = is_array($widget) ? ($widget['visible'] ?? true) : true;

            if (!$visible) {
                continue;
            }

            $definition = DashboardWidgetRegistry::get($id);
            if (!$definition) {
                error_log("Missing widget: " . $id);
                continue;
            }

            echo '<div class="dashboard-widget">';
            if (isset($definition['callback']) && is_callable($definition['callback'])) {
                call_user_func($definition['callback']);
            } else {
                echo '<div class="notice notice-error"><p>Invalid or missing callback for dashboard widget.</p></div>';
            }
            echo '</div>';
        }
    }

    /**
     * Render the dashboard for a user based on their saved layout.
     */
    public static function render_user_dashboard(int $user_id): void
    {
        $layout   = DashboardController::get_user_dashboard_layout($user_id);
        $registry = DashboardWidgetRegistry::get_all();

        $has_visible = false;
        foreach ($layout as $widget) {
            $id      = is_array($widget) ? $widget['id'] : $widget;
            if (!isset($registry[$id])) {
                continue;
            }
            $visible = is_array($widget) ? ($widget['visible'] ?? true) : true;
            if ($visible) {
                $has_visible = true;
            }
        }

        if (!$has_visible) {
            echo '<p class="ap-empty-state">' . __('No widgets found. Start by adding one.', 'artpulse') . '</p>';
            return;
        }

        foreach ($layout as $widget) {
            $id      = is_array($widget) ? $widget['id'] : $widget;
            if (!isset($registry[$id])) {
                continue;
            }
            $visible = is_array($widget) ? ($widget['visible'] ?? true) : true;
            $def     = $registry[$id];

            echo '<div class="ap-widget-card" role="group" aria-label="Widget: ' . esc_attr($def['label']) . '" data-widget-id="' . esc_attr($id) . '" data-id="' . esc_attr($id) . '" data-visible="' . ($visible ? '1' : '0') . '">';
            echo '<div class="ap-widget-header drag-handle">';
            echo '<span class="widget-title">' . esc_html($def['label']) . '</span>';
            echo '</div>';
            echo '<div class="inside">';
            if (isset($def['callback']) && is_callable($def['callback'])) {
                call_user_func($def['callback']);
            } else {
                echo '<div class="notice notice-error"><p>Invalid or missing callback for dashboard widget.</p></div>';
            }
            echo '</div></div>';
        }
    }

    /**
     * Render the dashboard layout for a specific role without
     * requiring a user account switch.
     */
    public static function render_role_dashboard_preview(string $role): void
    {
        $registry = \ArtPulse\Core\DashboardWidgetRegistry::get_all();
        $layout   = \ArtPulse\Admin\RoleLayoutManager::get_layout_for_role($role);

        foreach ($layout as $widget) {
            $id      = is_array($widget) ? $widget['id'] : $widget;
            $visible = is_array($widget) ? ($widget['visible'] ?? true) : true;

            if (!$visible || !isset($registry[$id])) {
                continue;
            }

            $w = $registry[$id];

            echo '<div class="ap-widget-card" role="group" aria-label="Widget: ' . esc_attr($w['label']) . '" data-widget-id="' . esc_attr($id) . '" data-id="' . esc_attr($id) . '" data-visible="' . ($visible ? '1' : '0') . '">';
            echo '<div class="ap-widget-header drag-handle">';
            echo '<span class="widget-title">' . esc_html($w['label']) . '</span>';
            echo '</div>';
            echo '<div class="inside">';
            if (isset($w['callback']) && is_callable($w['callback'])) {
                call_user_func($w['callback']);
            } else {
                echo '<div class="notice notice-error"><p>Invalid or missing callback for dashboard widget.</p></div>';
            }
            echo '</div></div>';
        }
    }
}
