<?php
namespace ArtPulse\Frontend;

class DashboardRoleRewrite
{
    public static function register(): void
    {
        add_action('init', [self::class, 'add_rules']);
        add_filter('query_vars', [self::class, 'register_vars']);
        add_action('template_redirect', [self::class, 'maybe_render']);
    }

    public static function add_rules(): void
    {
        add_rewrite_rule(
            '^dashboard-role(?:\\.php)?/?$',
            'index.php?ap_dashboard_role=1',
            'top'
        );
    }

    public static function register_vars(array $vars): array
    {
        $vars[] = 'ap_dashboard_role';
        return $vars;
    }

    public static function maybe_render(): void
    {
        if (get_query_var('ap_dashboard_role')) {
            include plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'templates/dashboard-role.php';
            exit;
        }
    }
}
