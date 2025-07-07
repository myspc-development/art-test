<?php
namespace ArtPulse\Admin;

class DashboardWidgetTools
{
    /**
     * Return a registry of available dashboard widgets.
     */
    public static function get_available_widgets(): array
    {
        return [
            'admin_stats' => [
                'title'    => __('Admin Stats', 'artpulse'),
                'callback' => [self::class, 'render_admin_stats'],
            ],
            'artist_profile' => [
                'title'    => __('Artist Profile', 'artpulse'),
                'callback' => [self::class, 'render_artist_profile'],
            ],
            'quick_links' => [
                'title'    => __('Quick Links', 'artpulse'),
                'callback' => [self::class, 'render_quick_links'],
            ],
        ];
    }

    public static function register(): void
    {
        add_action('wp_dashboard_setup', [self::class, 'add_dashboard_widgets']);
        add_action('admin_post_ap_export_widget_config', [self::class, 'handle_export']);
        add_action('admin_post_ap_import_widget_config', [self::class, 'handle_import']);
    }

    public static function add_dashboard_widgets(): void
    {
        $registry  = self::get_available_widgets();
        $by_role   = get_option('artpulse_dashboard_widgets_by_role', []);
        $roles     = wp_get_current_user()->roles;
        $to_render = [];

        foreach ($roles as $role) {
            if (isset($by_role[$role]) && is_array($by_role[$role])) {
                foreach ($by_role[$role] as $id) {
                    if (isset($registry[$id]) && !in_array($id, $to_render, true)) {
                        $to_render[] = $id;
                    }
                }
            }
        }

        if (empty($to_render)) {
            $to_render = array_keys($registry);
        }

        foreach ($to_render as $id) {
            $widget = $registry[$id];
            wp_add_dashboard_widget(
                'artpulse_' . $id,
                $widget['title'],
                $widget['callback']
            );
        }
    }

    public static function render_admin_stats(): void
    {
        echo '<p>' . esc_html__('Admin statistics placeholder.', 'artpulse') . '</p>';
    }

    public static function render_artist_profile(): void
    {
        echo '<p>' . esc_html__('Artist profile overview placeholder.', 'artpulse') . '</p>';
    }

    public static function render_quick_links(): void
    {
        echo '<p>' . esc_html__('Quick links placeholder.', 'artpulse') . '</p>';
    }

    public static function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'artpulse'));
        }

        echo '<div class="wrap">';
        echo '<h3>' . esc_html__('Welcome to the ArtPulse Dashboard', 'artpulse') . '</h3>';
        echo '<p>' . esc_html__('This is your custom dashboard widget. You can add stats, charts, quick links, etc.', 'artpulse') . '</p>';
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
