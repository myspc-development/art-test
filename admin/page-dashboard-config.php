<?php
if (!defined('ABSPATH')) { exit; }

use ArtPulse\Support\OptionUtils;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Support\WidgetIds;

function ap_render_dashboard_config_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Insufficient permissions', 'artpulse'));
    }

    $visibility = OptionUtils::get_array_option('artpulse_widget_roles');
    $layout     = OptionUtils::get_array_option('artpulse_dashboard_layouts');
    if (!$layout) {
        $layout = [];
        foreach (DashboardWidgetRegistry::get_role_widget_map() as $role => $widgets) {
            $layout[$role] = array_map(
                static fn($w) => ['id' => sanitize_key(is_array($w) ? ($w['id'] ?? '') : $w)],
                $widgets
            );
        }
    }
    $locked     = get_option('artpulse_locked_widgets', []);

    if (isset($_POST['save_dashboard_config']) && check_admin_referer('ap_save_dashboard_config')) {
        $visibility = json_decode(stripslashes($_POST['roles_json'] ?? ''), true) ?: [];
        foreach ($visibility as $r => &$ids) {
            $ids = array_values(array_unique(array_map([WidgetIds::class, 'canonicalize'], (array) $ids)));
        }
        unset($ids);
        $layout = json_decode(stripslashes($_POST['layout_json'] ?? ''), true) ?: [];
        foreach ($layout as $r => &$ids) {
            $new = [];
            $seen = [];
            foreach ((array)$ids as $item) {
                $id = is_array($item) ? ($item['id'] ?? '') : $item;
                $id = WidgetIds::canonicalize($id);
                if (in_array($id, $seen, true)) { continue; }
                $seen[] = $id;
                $new[] = is_array($item) ? array_merge($item, ['id'=>$id]) : $id;
            }
            $ids = $new;
        }
        unset($ids);
        $locked = json_decode(stripslashes($_POST['locked_json'] ?? ''), true) ?: [];
        $locked = array_values(array_unique(array_map([WidgetIds::class, 'canonicalize'], (array) $locked)));
        update_option('artpulse_widget_roles', $visibility);
        update_option('artpulse_dashboard_layouts', $layout);
        update_option('artpulse_locked_widgets', $locked);
        echo '<div class="notice notice-success"><p>' . esc_html__('Configuration saved.', 'artpulse') . '</p></div>';
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Dashboard Configuration', 'artpulse'); ?></h1>
        <form method="post">
            <?php wp_nonce_field('ap_save_dashboard_config'); ?>
            <h2><?php esc_html_e('Widget Roles', 'artpulse'); ?></h2>
            <textarea name="roles_json" id="ap-widget-roles" rows="6" style="width:100%;"><?php echo esc_textarea(wp_json_encode($visibility, JSON_PRETTY_PRINT)); ?></textarea>
            <h2><?php esc_html_e('Default Layouts', 'artpulse'); ?></h2>
            <textarea name="layout_json" id="ap-default-layout" rows="6" style="width:100%;"><?php echo esc_textarea(wp_json_encode($layout, JSON_PRETTY_PRINT)); ?></textarea>
            <h2><?php esc_html_e('Locked Widgets', 'artpulse'); ?></h2>
            <textarea name="locked_json" id="ap-locked-widgets" rows="3" style="width:100%;"><?php echo esc_textarea(wp_json_encode($locked, JSON_PRETTY_PRINT)); ?></textarea>
            <?php submit_button(__('Save', 'artpulse'), 'primary', 'save_dashboard_config', false, ['id' => 'ap-dashboard-save']); ?>
        </form>
        <script>
        window.APDashboardConfig = {
            endpoint: '<?php echo esc_js(rest_url('artpulse/v1/dashboard-config')); ?>',
            nonce: '<?php echo esc_js(wp_create_nonce('wp_rest')); ?>'
        };
        </script>
        <?php
        wp_enqueue_script('ap-dashboard-admin', plugins_url('/assets/js/admin-dashboard.js', ARTPULSE_PLUGIN_FILE), [], '1.0', true);
        ?>
    </div>
    <?php
}

ap_render_dashboard_config_page();
