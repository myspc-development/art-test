<?php
/**
 * Dashboard widget helpers and AJAX handlers.
 */

if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\Core\DashboardWidgetRegistry;

function ap_get_all_widget_definitions(): array
{
    return DashboardWidgetRegistry::get_definitions();
}


add_action('wp_ajax_ap_save_dashboard_widget_config', 'ap_save_dashboard_widget_config');

function ap_save_dashboard_widget_config(): void
{
    check_ajax_referer('ap_dashboard_widget_config', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('Permission denied', 'artpulse')]);
    }

    $raw = $_POST['config'] ?? [];
    $sanitized = [];
    foreach ($raw as $role => $widgets) {
        $role_key = sanitize_key($role);
        $ordered = [];
        foreach ((array) $widgets as $w) {
            $ordered[] = sanitize_key($w);
        }
        $sanitized[$role_key] = $ordered;
    }

    update_option('ap_dashboard_widget_config', $sanitized);
    wp_send_json_success(['saved' => true]);
}

function ap_load_dashboard_template(string $template, array $vars = []): string
{
    $path = locate_template($template);
    if (!$path) {
        $path = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'templates/' . $template;
    }
    if (!file_exists($path)) {
        return '';
    }
    ob_start();
    if ($vars) {
        extract($vars, EXTR_SKIP);
    }
    include $path;
    return ob_get_clean();
}

function ap_widget_membership(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/membership.php', $vars);
}

function ap_widget_next_payment(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/next-payment.php', $vars);
}

function ap_widget_transactions(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/transactions.php', $vars);
}

function ap_widget_upgrade(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/upgrade.php', $vars);
}

function ap_widget_content(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/content.php', $vars);
}

function ap_widget_local_events(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/local-events.php', $vars);
}

function ap_widget_favorites(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/favorites.php', $vars);
}

function ap_widget_rsvps(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/rsvps.php', $vars);
}

function ap_widget_my_events(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/my-events.php', $vars);
}

function ap_widget_events(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/events.php', $vars);
}

function ap_widget_support_history(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/support-history.php', $vars);
}

function ap_widget_notifications(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/notifications.php', $vars);
}

function ap_widget_messages(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/messages.php', $vars);
}

function ap_widget_account_tools(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/account-tools.php', $vars);
}

function ap_widget_webhooks(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/webhooks.php', $vars);
}
