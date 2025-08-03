<?php
namespace ArtPulse\Admin;

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Widgets\Member\WelcomeBoxWidget;
use ArtPulse\Core\DashboardController;
use ArtPulse\Admin\UserLayoutManager;
use ArtPulse\DashboardBuilder\DashboardManager as BuilderDashboardManager;

class DashboardWidgetTools
{
    public static function get_role_widgets(): array
    {
        $member_defs = DashboardWidgetRegistry::get_widgets_by_role('member');
        $member = [];
        foreach ($member_defs as $id => $def) {
            $item = ['id' => $id];
            if (!empty($def['callback'])) {
                $item['callback'] = $def['callback'];
            }
            if (!empty($def['rest'])) {
                $item['rest'] = $def['rest'];
            }
            $member[] = $item;
        }

        $org_defs = DashboardWidgetRegistry::get_widgets_by_role('organization');
        $organization = [];
        foreach ($org_defs as $id => $def) {
            $item = ['id' => $id];
            if (!empty($def['callback'])) {
                $item['callback'] = $def['callback'];
            }
            if (!empty($def['rest'])) {
                $item['rest'] = $def['rest'];
            }
            $organization[] = $item;
        }

        $artist_defs = DashboardWidgetRegistry::get_widgets_by_role('artist');
        $artist = [];
        foreach ($artist_defs as $id => $def) {
            $item = ['id' => $id];
            if (!empty($def['callback'])) {
                $item['callback'] = $def['callback'];
            }
            if (!empty($def['rest'])) {
                $item['rest'] = $def['rest'];
            }
            $artist[] = $item;
        }

        return [
            'artist' => $artist,
            'organization' => $organization,
            'member' => $member,
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
                ['id' => 'welcome_box', 'label' => 'Welcome', 'callback' => [WelcomeBoxWidget::class, 'render']],
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
                [self::class, 'render'],
                [ 'roles' => ['administrator'] ]
            );
        });
        add_action('admin_menu', function () {
            add_submenu_page(
                'index.php',
                __('Dashboard Layout Help', 'artpulse'),
                __('Layout Help', 'artpulse'),
                'manage_options',
                'dashboard-layout-help',
                [self::class, 'render_help_page']
            );
        });
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
        if (!current_user_can('manage_options')) {
            wp_die(__('Access denied.', 'artpulse'), '', ['response' => 403]);
        }

        BuilderDashboardManager::enqueue_assets();
        BuilderDashboardManager::render_builder(false);
    }

    public static function render_add_widget_modal(array $available_widgets): void
    {
        foreach ($available_widgets as $id => $def) {
            $label       = isset($def['label']) ? $def['label'] : __('Untitled', 'artpulse');
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

        if (!defined('IS_DASHBOARD_BUILDER_PREVIEW')) {
            define('IS_DASHBOARD_BUILDER_PREVIEW', true);
        }

        static $rendering = false;
        if ($rendering) {
            return '';
        }

        $rendering = true;
        ob_start();
        call_user_func($cb, 0);
        $html = ob_get_clean();
        $rendering = false;

        return $html;
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
                call_user_func($cb, 0);
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
            $layout = UserLayoutManager::get_role_layout($role);
        } else {
            $user_id = get_current_user_id();
            $layout  = UserLayoutManager::get_layout_for_user($user_id);
        }
        self::render_layout_widgets(
            $layout,
            function (array $def, string $id, bool $visible): void {
                echo '<div class="dashboard-widget">';
                if (isset($def['callback']) && is_callable($def['callback'])) {
                    ap_render_widget($id);
                } else {
                    echo '<div class="notice notice-error"><p>' . esc_html__('Invalid or missing callback for dashboard widget.', 'artpulse') . '</p></div>';
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
            function (array $def, string $id, bool $visible) use ($user_id): void {
                $label = isset($def['label']) ? $def['label'] : __('Untitled', 'artpulse');
                echo '<div class="ap-widget-card" role="group" aria-label="Widget: ' . esc_attr($label) . '" data-widget-id="' . esc_attr($id) . '" data-id="' . esc_attr($id) . '" data-visible="' . ($visible ? '1' : '0') . '">';
                $icon = $def['icon'] ?? '';
                $drag = esc_attr__('Drag to reorder', 'artpulse');
                echo '<div class="ap-widget-header drag-handle" role="button" tabindex="0" aria-label="' . $drag . '">';
                echo '<span class="widget-title">' . artpulse_dashicon($icon, ['style' => 'margin-right:6px;']) . esc_html($label) . '</span>';
                echo '</div>';
                echo '<div class="inside">';
                if (isset($def['callback']) && is_callable($def['callback'])) {
                    ap_render_widget($id, $user_id);
                } else {
                    echo '<div class="notice notice-error"><p>' . esc_html__('Invalid or missing callback for dashboard widget.', 'artpulse') . '</p></div>';
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
        if (!defined('IS_DASHBOARD_BUILDER_PREVIEW')) {
            define('IS_DASHBOARD_BUILDER_PREVIEW', true);
        }

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
        $layout   = UserLayoutManager::get_role_layout($role);

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
            $label = isset($w['label']) ? $w['label'] : __('Untitled', 'artpulse');

            echo '<div class="ap-widget-card" role="group" aria-label="Widget: ' . esc_attr($label) . '" data-widget-id="' . esc_attr($id) . '" data-id="' . esc_attr($id) . '" data-visible="' . ($visible ? '1' : '0') . '">';
            $icon = $w['icon'] ?? '';
            $drag = esc_attr__('Drag to reorder', 'artpulse');
            echo '<div class="ap-widget-header drag-handle" role="button" tabindex="0" aria-label="' . $drag . '">';
            echo '<span class="widget-title">' . artpulse_dashicon($icon, ['style' => 'margin-right:6px;']) . esc_html($label) . '</span>';
            echo '</div>';
            echo '<div class="inside">';
            if (isset($w['callback']) && is_callable($w['callback'])) {
                call_user_func($w['callback'], 0);
            } else {
                echo '<div class="notice notice-error"><p>' . esc_html__('Invalid or missing callback for dashboard widget.', 'artpulse') . '</p></div>';
            }
            echo '</div></div>';
        }
    }
}
