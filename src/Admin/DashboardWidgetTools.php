<?php
namespace ArtPulse\Admin;

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetManager;
use ArtPulse\Admin\UserLayoutManager;

class DashboardWidgetTools
{
    public static function get_role_widgets(): array
    {
        return [
            'artist' => [
                [
                    'id'       => 'favorites',
                    'callback' => [\ArtPulse\Core\DashboardWidgetRegistry::class, 'render_widget_favorites'],
                ],
                [
                    'id'   => 'messages',
                    'rest' => 'artpulse/v1/dashboard/messages',
                ],
            ],
            'organization' => [],
            'member' => [],
        ];
    }

    public static function get_role_widgets_for_current_user(): array
    {
        $user  = wp_get_current_user();
        $role  = $user->roles[0] ?? 'member';
        $all   = self::get_role_widgets();
        return $all[$role] ?? [];
    }

    public static function register_default_widgets_for_role(string $role): void
    {
        $defaults = [
            'member' => [
                ['id' => 'welcome_box', 'label' => 'Welcome', 'callback' => [\ArtPulse\Widgets::class, 'render_welcome_box']],
            ],
            'artist' => [
                ['id' => 'portfolio_preview', 'label' => 'Portfolio', 'callback' => [\ArtPulse\Widgets::class, 'render_portfolio_box']],
            ],
            'organization' => [
                ['id' => 'org_insights', 'label' => 'Insights', 'callback' => [\ArtPulse\Widgets::class, 'render_org_insights_box']],
            ],
        ];

        foreach ($defaults[$role] ?? [] as $widget) {
            update_user_meta(get_current_user_id(), "ap_widget_{$role}_{$widget['id']}", $widget);
        }
    }
    public static function register(): void
    {
        add_action('artpulse_register_dashboard_widget', function () {
            DashboardWidgetRegistry::register(
                'artpulse_dashboard_widget',
                __('ArtPulse Dashboard', 'artpulse'),
                'layout',
                __('Manage dashboard layouts.', 'artpulse'),
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
        add_action('artpulse_render_settings_tab_widgets', function () {
            include ARTPULSE_PLUGIN_DIR . 'templates/admin/settings-tab-widgets.php';
        });
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
            wp_die(
                __('Invalid role.', 'artpulse'),
                '',
                ['response' => 403]
            );
        }

        if (!ap_user_can_edit_layout($selected)) {
            wp_die(
                __('Access denied.', 'artpulse'),
                '',
                ['response' => 403]
            );
        }

        if (isset($_GET['dw_import_success'])) {
            echo '<div class="notice notice-success"><p>' . esc_html__('Widget configuration imported.', 'artpulse') . '</p></div>';
        } elseif (isset($_GET['dw_import_error'])) {
            $err  = sanitize_text_field($_GET['dw_import_error']);
            switch ($err) {
                case 'no_file':
                    $msg = __('No file was uploaded.', 'artpulse');
                    break;
                case 'file_size':
                    $msg = __('Uploaded file exceeds the maximum size of 1 MB.', 'artpulse');
                    break;
                case 'invalid_mime':
                    $msg = __('Invalid file type. Please upload a JSON file.', 'artpulse');
                    break;
                case 'invalid_json':
                default:
                    $msg = __('Uploaded file contains invalid JSON.', 'artpulse');
                    break;
            }
            echo '<div class="notice notice-error"><p>' . esc_html($msg) . '</p></div>';
        }

        if (defined('ARTPULSE_DEBUG') && ARTPULSE_DEBUG) {
            error_log('Widget Manager accessed by user: ' . get_current_user_id());
            error_log('User roles: ' . wp_json_encode(wp_get_current_user()->roles));
        }

        if (isset($_POST['layout'])) {
            check_admin_referer('ap_save_role_layout');
            $selected = sanitize_key($_POST['ap_dashboard_role'] ?? $selected);
            $layout   = json_decode(wp_unslash($_POST['layout']), true) ?: [];
            if (is_array($layout)) {
                DashboardWidgetManager::saveRoleLayout($selected, $layout);
                echo '<div class="notice notice-success"><p>' . esc_html__('Layout saved for role.', 'artpulse') . '</p></div>';
            }
        }

        if (isset($_POST['reset_layout'])) {
            check_admin_referer('ap_save_role_layout');
            $selected = sanitize_key($_POST['ap_dashboard_role'] ?? $selected);
            DashboardWidgetManager::saveRoleLayout($selected, []);
            echo '<div class="notice notice-success"><p>' . esc_html__('Layout reset for role.', 'artpulse') . '</p></div>';
        }

        if (isset($_POST['import_role_layout']) && current_user_can('manage_options')) {
            $import_result = DashboardWidgetManager::importRoleLayout($selected, stripslashes($_POST['import_json'] ?? ''));
            echo '<div class="notice ' . ($import_result ? 'notice-success' : 'notice-error') . '"><p>'
                . ($import_result ? 'Layout imported successfully.' : 'Invalid layout JSON.') . '</p></div>';
        }

        $current  = DashboardWidgetManager::getRoleLayout($selected);
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
        echo '<form method="post" id="widget-layout-form">';
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
        echo '<label for="import-layout" class="button">' . esc_html__('Import', 'artpulse') . '</label>';
        echo '<input type="file" id="import-layout" />';
        echo '<input type="hidden" id="layout_input" name="layout">';
        echo '<p><button type="submit" id="save-layout-btn" class="button button-primary">💾 Save Layout</button></p>';
        if (current_user_can('manage_options')) {
            echo '<br><textarea name="import_json" rows="6" cols="60" placeholder="Paste layout JSON..."></textarea><br>';
            echo '<button name="import_role_layout" type="submit" class="button">Import Layout</button> ';
            echo '<button type="button" class="button" onclick="copyExportedLayout()">Copy Export</button>';
            echo '<textarea id="export_json" rows="6" cols="60" readonly>' . esc_textarea(DashboardWidgetManager::exportRoleLayout($selected)) . '</textarea>';
        }
        echo '</form>';

        echo '<div id="ap-widget-list">';
        foreach ($current as $item) {
            $id = $item['id'];
            $visible = $item['visible'] ?? true;
            $cb = DashboardWidgetRegistry::get_widget_callback($id);
            if (is_callable($cb)) {
                $icon  = $defs_by_id[$id]['icon'] ?? '';
                $title = esc_html($defs_by_id[$id]['name'] ?? $id);
                echo '<div class="ap-widget-card' . ($visible ? '' : ' is-hidden') . '" role="group" aria-label="Widget: ' . $title . '" data-widget-id="' . esc_attr($id) . '" data-id="' . esc_attr($id) . '" data-visible="' . ($visible ? '1' : '0') . '">';
                echo '<div class="ap-widget-header">';
                echo '<span class="drag-handle" title="Drag to reorder" role="button" tabindex="0" aria-label="Drag to reorder">&#9776;</span>';
                echo '<span class="ap-widget-icon">' . artpulse_dashicon($icon) . '</span>';
                echo '<span class="ap-widget-title">' . $title . '</span>';
                echo '<div class="ap-widget-controls">';
                echo '<label class="toggle-switch" title="Toggle Widget"><input type="checkbox" class="widget-toggle" aria-label="Toggle Widget"' . checked($visible, true, false) . ' /><span class="slider"></span></label>';
                echo '<button type="button" class="widget-remove" title="Remove Widget" aria-label="Remove Widget">&#x2716;</button>';
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

        echo '<div class="ap-add-widget">';
        echo '<h3>' . esc_html__('Add Widget', 'artpulse') . '</h3>';
        echo '<input type="text" id="ap-widget-search" placeholder="Search widgets..." oninput="apSearchWidgets(this.value)" class="regular-text">';
        $categories = array_unique(array_filter(array_column($defs, 'category')));
        if ($categories) {
            echo '<select id="ap-widget-category-filter" onchange="apFilterWidgetsByCategory(this.value)">';
            echo '<option value="">' . esc_html__('All Categories', 'artpulse') . '</option>';
            foreach ($categories as $cat) {
                echo '<option value="' . esc_attr($cat) . '">' . esc_html(ucfirst($cat)) . '</option>';
            }
            echo '</select>';
        }
        $role_keys = array_keys($roles);
        echo '<select id="ap-widget-role-filter"><option value="">' . esc_html__('All Roles', 'artpulse') . '</option>';
        foreach ($role_keys as $role_key) {
            echo '<option value="' . esc_attr($role_key) . '">' . esc_html(ucfirst($role_key)) . '</option>';
        }
        echo '</select>';
        echo '<ul id="add-widget-panel">';
        foreach ($defs as $def) {
            if (in_array($def['id'], $unused, true)) {
                if (!empty($def['roles']) && !in_array($selected, (array) $def['roles'], true)) {
                    continue;
                }
                $id        = esc_attr($def['id']);
                $preview   = self::render_widget_preview($def['id']);
                $icon      = esc_html($def['icon'] ?? '');
                $category  = esc_attr($def['category'] ?? '');
                $name_attr = esc_attr($def['name'] ?? $def['id']);
                $desc_attr = esc_attr($def['description'] ?? '');
                $roles_attr = esc_attr(implode(',', $def['roles'] ?? []));
                $roles_text = '';
                if (!empty($def['roles'])) {
                    $roles_text = sprintf(__('Only visible to: %s', 'artpulse'), implode(', ', array_map('ucfirst', (array) $def['roles'])));
                }
                echo '<li class="widget-card" data-category="' . $category . '" data-name="' . $name_attr . '" data-desc="' . $desc_attr . '" data-roles="' . $roles_attr . '"><label><input type="checkbox" class="add-widget-check" value="' . $id . '"> ';
                echo '<span class="widget-icon">' . $icon . '</span> <strong>' . esc_html($def['name']) . '</strong>';
                echo '<div class="widget-preview-box">' . $preview . '</div>';
                echo '<small>' . esc_html($def['description']) . '</small>';
                if ($roles_text) {
                    echo '<br><small class="widget-roles">' . esc_html($roles_text) . '</small>';
                }
                echo '</label></li>';
            }
        }
        echo '</ul>';
        echo '</div>';
        echo '</div>';

        echo '<button type="button" id="toggle-preview" class="button">Toggle Preview</button>';
        echo '<h3>Preview Dashboard for ' . esc_html(ucfirst($selected)) . '</h3>';
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
            wp_die(
                __('Insufficient permissions', 'artpulse'),
                '',
                ['response' => 403]
            );
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
     * Validates file size and MIME type before processing.
     */
    public static function handle_import(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(
                __('Insufficient permissions', 'artpulse'),
                '',
                ['response' => 403]
            );
        }

        check_admin_referer('ap_import_widget_config');

        if (!isset($_FILES['ap_widget_file']) || empty($_FILES['ap_widget_file']['tmp_name'])) {
            error_log('[DashboardWidgetTools] Import failed: missing file');
            if (function_exists('ap_add_admin_notice')) {
                \ap_add_admin_notice(__('No file was uploaded.', 'artpulse'), 'error');
            }
            wp_safe_redirect(add_query_arg('dw_import_error', 'no_file', wp_get_referer() ?: admin_url('admin.php?page=artpulse-widget-editor')));
            exit;
        }

        $file       = $_FILES['ap_widget_file'];
        $max_size   = 1024 * 1024; // 1 MB
        $file_size  = $file['size'] ?? 0;
        $file_mime  = mime_content_type($file['tmp_name']);
        if ($file_size > $max_size) {
            error_log('[DashboardWidgetTools] Import failed: file too large');
            if (function_exists('ap_add_admin_notice')) {
                \ap_add_admin_notice(__('Uploaded file exceeds the maximum size of 1 MB.', 'artpulse'), 'error');
            }
            wp_safe_redirect(add_query_arg('dw_import_error', 'file_size', wp_get_referer() ?: admin_url('admin.php?page=artpulse-widget-editor')));
            exit;
        }

        if (!in_array($file_mime, ['application/json', 'text/plain'], true)) {
            error_log('[DashboardWidgetTools] Import failed: invalid mime ' . $file_mime);
            if (function_exists('ap_add_admin_notice')) {
                \ap_add_admin_notice(__('Invalid file type. Please upload a JSON file.', 'artpulse'), 'error');
            }
            wp_safe_redirect(add_query_arg('dw_import_error', 'invalid_mime', wp_get_referer() ?: admin_url('admin.php?page=artpulse-widget-editor')));
            exit;
        }

        $json = file_get_contents($_FILES['ap_widget_file']['tmp_name']);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            error_log('[DashboardWidgetTools] Invalid JSON import: ' . json_last_error_msg());
            if (function_exists('ap_add_admin_notice')) {
                \ap_add_admin_notice(__('Uploaded file contains invalid JSON.', 'artpulse'), 'error');
            }
            wp_safe_redirect(add_query_arg('dw_import_error', 'invalid_json', wp_get_referer() ?: admin_url('admin.php?page=artpulse-widget-editor')));
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

        wp_safe_redirect(add_query_arg('dw_import_success', '1', admin_url('admin.php?page=artpulse-widget-editor')));
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
            $label       = isset($def['label']) ? $def['label'] : 'Untitled';
            $description = $def['description'] ?? '';
            $icon        = $def['icon'] ?? '';
            $category    = $def['category'] ?? '';

            echo '<div class="widget-card" ' .
                'data-id="' . esc_attr($id) . '" ' .
                'data-name="' . esc_attr($label) . '" ' .
                'data-desc="' . esc_attr($description) . '" ' .
                'data-category="' . esc_attr($category) . '">';

            echo '<span class="widget-icon">' . esc_html($icon) . '</span>';
            echo '<strong class="widget-label">' . esc_html($label) . '</strong>';
            echo '<p class="widget-description">' . esc_html($description) . '</p>';
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
            $cb = [DashboardWidgetRegistry::class, 'render_widget_fallback'];
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

        $layout  = DashboardWidgetManager::getRoleLayout($role);

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
        $defs = array_filter(
            $defs,
            fn($def) => $def['id'] !== 'artpulse_dashboard_widget'
        );

        return array_map(
            fn($def) => ['id' => $def['id'], 'visible' => true],
            $defs
        );
    }

    /**
     * Iterate over a widget layout and output each widget using the provided renderer.
     *
     * @param array         $layout   Array of widget definitions in the saved order.
     * @param callable      $renderer Callback invoked with the widget definition, id and visibility flag.
     */
    private static function render_layout_widgets(array $layout, callable $renderer): void
    {
        $registry = DashboardWidgetRegistry::get_all();

        foreach ($layout as $widget) {
            $id = is_array($widget) ? $widget['id'] : $widget;
            if (!isset($registry[$id])) {
                continue;
            }

            $visible = is_array($widget) ? ($widget['visible'] ?? true) : true;
            $renderer($registry[$id], $id, $visible);
        }
    }

    /**
     * Output dashboard widgets.
     * If a role is provided, that role's layout will be used.
     * Otherwise the current user's layout is loaded.
     */
    public static function render_dashboard_widgets(string $role = ''): void
    {
        if ($role !== '') {
            $layout = DashboardWidgetManager::getRoleLayout($role);
        } else {
            $user_id = get_current_user_id();
            $layout  = DashboardWidgetManager::getUserLayout($user_id);
        }
        self::render_layout_widgets(
            $layout,
            function (array $def, string $id, bool $visible): void {
                echo '<div class="dashboard-widget">';
                if (isset($def['callback']) && is_callable($def['callback'])) {
                    call_user_func($def['callback']);
                } else {
                    echo '<div class="notice notice-error"><p>Invalid or missing callback for dashboard widget.</p></div>';
                }
                echo '</div>';
            }
        );
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

        self::render_layout_widgets(
            $layout,
            function (array $def, string $id, bool $visible): void {
                $label = isset($def['label']) ? $def['label'] : 'Untitled';
                echo '<div class="ap-widget-card" role="group" aria-label="Widget: ' . esc_attr($label) . '" data-widget-id="' . esc_attr($id) . '" data-id="' . esc_attr($id) . '" data-visible="' . ($visible ? '1' : '0') . '">';
                $icon = $def['icon'] ?? '';
                echo '<div class="ap-widget-header drag-handle" role="button" tabindex="0" aria-label="Drag to reorder">';
                echo '<span class="widget-title">' . artpulse_dashicon($icon, ['style' => 'margin-right:6px;']) . esc_html($label) . '</span>';
                echo '</div>';
                echo '<div class="inside">';
                if (isset($def['callback']) && is_callable($def['callback'])) {
                    call_user_func($def['callback']);
                } else {
                    echo '<div class="notice notice-error"><p>Invalid or missing callback for dashboard widget.</p></div>';
                }
                echo '</div></div>';
            }
        );
    }

    /**
     * Render the dashboard layout for a specific role without
     * requiring a user account switch.
     */
    public static function render_role_dashboard_preview(string $role): void
    {
        $style = UserLayoutManager::get_role_style($role);
        if ($style) {
            echo '<style id="ap-preview-style">';
            echo '.ap-widget-card{';
            if (!empty($style['background_color'])) {
                echo 'background:' . esc_attr($style['background_color']) . ';';
            }
            if (!empty($style['border'])) {
                echo 'border:' . esc_attr($style['border']) . ';';
            }
            if (!empty($style['padding'])) {
                $pad = strtolower($style['padding']);
                if ($pad === 's') { $pad = '4px'; }
                elseif ($pad === 'm') { $pad = '8px'; }
                elseif ($pad === 'l') { $pad = '16px'; }
                echo 'padding:' . esc_attr($pad) . ';';
            }
            echo '}';
            if (!empty($style['title_font_size'])) {
                echo '.ap-widget-card .widget-title{font-size:' . esc_attr($style['title_font_size']) . ';}';
            }
            echo '</style>';
        }
        $registry = \ArtPulse\Core\DashboardWidgetRegistry::get_all();
        $layout   = DashboardWidgetManager::getRoleLayout($role);

        foreach ($layout as $widget) {
            $id      = is_array($widget) ? $widget['id'] : $widget;
            $visible = is_array($widget) ? ($widget['visible'] ?? true) : true;

            if (!$visible || !isset($registry[$id])) {
                continue;
            }

            if ($id === 'artpulse_dashboard_widget') {
                continue; // exclude manager from preview
            }

            $w     = $registry[$id];
            $label = isset($w['label']) ? $w['label'] : 'Untitled';

            echo '<div class="ap-widget-card" role="group" aria-label="Widget: ' . esc_attr($label) . '" data-widget-id="' . esc_attr($id) . '" data-id="' . esc_attr($id) . '" data-visible="' . ($visible ? '1' : '0') . '">';
            $icon = $w['icon'] ?? '';
            echo '<div class="ap-widget-header drag-handle" role="button" tabindex="0" aria-label="Drag to reorder">';
            echo '<span class="widget-title">' . artpulse_dashicon($icon, ['style' => 'margin-right:6px;']) . esc_html($label) . '</span>';
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
