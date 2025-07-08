<?php
/**
 * Dashboard widget helpers and AJAX handlers.
 */

if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\Core\DashboardWidgetRegistry;

function ap_get_all_widget_definitions(bool $include_schema = false): array
{
    return DashboardWidgetRegistry::get_definitions($include_schema);
}


add_action('wp_ajax_ap_save_dashboard_widget_config', 'ap_save_dashboard_widget_config');

add_action('wp_ajax_ap_save_widget_layout', 'ap_save_widget_layout');
add_action('wp_ajax_ap_save_role_layout', 'ap_save_role_layout');
add_action('wp_ajax_ap_save_user_layout', 'ap_save_user_layout');

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

function ap_save_widget_layout(): void
{
    check_ajax_referer('ap_dashboard_layout', 'nonce');
    if (!current_user_can('view_artpulse_dashboard') && !current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('Permission denied', 'artpulse')]);
        return;
    }

    $uid = get_current_user_id();

    if (isset($_POST['layout'])) {
        $layout_raw = $_POST['layout'];
        if (is_string($layout_raw)) {
            $layout_raw = json_decode($layout_raw, true);
        }
        $valid_ids = array_column(DashboardWidgetRegistry::get_definitions(), 'id');
        $ordered = [];
        foreach ((array) $layout_raw as $item) {
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
        update_user_meta($uid, 'ap_dashboard_layout', $ordered);
    }

    wp_send_json_success(['saved' => true]);
}

function ap_save_role_layout(): void
{
    check_ajax_referer('ap_save_role_layout', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('Permission denied', 'artpulse')]);
        return;
    }

    $role   = sanitize_key($_POST['role'] ?? '');
    $layout = $_POST['layout'] ?? [];
    if (is_string($layout)) {
        $layout = json_decode($layout, true);
    }
    if (!is_array($layout)) {
        $layout = [];
    }

    \ArtPulse\Admin\UserLayoutManager::save_role_layout($role, $layout);
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Saved role layout for ' . $role . ': ' . wp_json_encode($layout));
    }
    wp_send_json_success(['saved' => true]);
}

function ap_save_user_layout(): void
{
    check_ajax_referer('ap_save_user_layout', 'nonce');

    $input   = json_decode(file_get_contents('php://input'), true);
    $layout  = $input['layout'] ?? [];
    $user_id = get_current_user_id();

    if ($user_id && is_array($layout)) {
        \ArtPulse\Admin\UserLayoutManager::save_user_layout($user_id, $layout);
        wp_send_json_success(['message' => 'Layout saved']);
    }

    wp_send_json_error(['message' => 'Invalid data']);
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

/**
 * Register core dashboard widgets.
 */
function ap_register_core_dashboard_widgets(): void
{
    DashboardWidgetRegistry::register(
        'membership',
        __('Membership', 'artpulse'),
        'users',
        __('Subscription status and badges.', 'artpulse'),
        'ap_widget_membership',
        [
            'category' => 'engagement',
            'settings' => [
                [
                    'key'     => 'show_badges',
                    'label'   => __('Show Badges', 'artpulse'),
                    'type'    => 'checkbox',
                    'default' => true,
                ],
            ],
        ]
    );

    DashboardWidgetRegistry::register(
        'upgrade',
        __('Upgrade', 'artpulse'),
        'star',
        __('Upgrade options for the account.', 'artpulse'),
        'ap_widget_upgrade',
        [
            'category' => 'account',
            'settings' => [
                [
                    'key'     => 'show_forms',
                    'label'   => __('Show Forms', 'artpulse'),
                    'type'    => 'checkbox',
                    'default' => true,
                ],
            ],
        ]
    );

    DashboardWidgetRegistry::register(
        'local-events',
        __('Local Events', 'artpulse'),
        'map-pin',
        __('Shows events near the user.', 'artpulse'),
        'ap_widget_local_events',
        [
            'category' => 'community',
            'settings' => [
                [
                    'key'     => 'limit',
                    'label'   => __('Number of Events', 'artpulse'),
                    'type'    => 'number',
                    'default' => 5,
                ],
            ],
        ]
    );

    DashboardWidgetRegistry::register(
        'favorites',
        __('Favorites', 'artpulse'),
        'heart',
        __('Favorited content lists.', 'artpulse'),
        'ap_widget_favorites',
        [
            'category' => 'engagement',
            'settings' => [
                [
                    'key'     => 'limit',
                    'label'   => __('Items to Show', 'artpulse'),
                    'type'    => 'number',
                    'default' => 5,
                ],
            ],
        ]
    );

    DashboardWidgetRegistry::register(
        'rsvps',
        __('RSVPs', 'artpulse'),
        'calendar',
        __('User RSVP history.', 'artpulse'),
        'ap_widget_rsvps',
        [
            'category' => 'events',
            'settings' => [
                [
                    'key'     => 'limit',
                    'label'   => __('Items to Show', 'artpulse'),
                    'type'    => 'number',
                    'default' => 5,
                ],
            ],
        ]
    );

    DashboardWidgetRegistry::register(
        'my-events',
        __('My Events', 'artpulse'),
        'clock',
        __('Events created by the user.', 'artpulse'),
        'ap_widget_my_events',
        [
            'category' => 'events',
            'settings' => [
                [
                    'key'     => 'limit',
                    'label'   => __('Items to Show', 'artpulse'),
                    'type'    => 'number',
                    'default' => 5,
                ],
            ],
        ]
    );

    DashboardWidgetRegistry::register(
        'events',
        __('Upcoming Events', 'artpulse'),
        'calendar',
        __('Global upcoming events.', 'artpulse'),
        'ap_widget_events',
        [
            'category' => 'events',
            'settings' => [
                [
                    'key'     => 'limit',
                    'label'   => __('Items to Show', 'artpulse'),
                    'type'    => 'number',
                    'default' => 5,
                ],
            ],
        ]
    );

    DashboardWidgetRegistry::register(
        'messages',
        __('Messages', 'artpulse'),
        'mail',
        __('Private messages inbox.', 'artpulse'),
        'ap_widget_messages',
        [
            'category' => 'engagement',
            'settings' => [
                [
                    'key'     => 'limit',
                    'label'   => __('Items to Show', 'artpulse'),
                    'type'    => 'number',
                    'default' => 5,
                ],
            ],
        ]
    );

    DashboardWidgetRegistry::register(
        'account-tools',
        __('Account Tools', 'artpulse'),
        'settings',
        __('Export and deletion options.', 'artpulse'),
        'ap_widget_account_tools',
        [
            'category' => 'account',
            'settings' => [
                [
                    'key'     => 'show_export',
                    'label'   => __('Show Export Links', 'artpulse'),
                    'type'    => 'checkbox',
                    'default' => true,
                ],
            ],
        ]
    );

    DashboardWidgetRegistry::register(
        'support-history',
        __('Support History', 'artpulse'),
        'life-buoy',
        __('Previous support tickets.', 'artpulse'),
        'ap_widget_support_history',
        [
            'category' => 'support',
            'settings' => [
                [
                    'key'     => 'limit',
                    'label'   => __('Items to Show', 'artpulse'),
                    'type'    => 'number',
                    'default' => 5,
                ],
            ],
        ]
    );

    DashboardWidgetRegistry::register(
        'notifications',
        __('Notifications', 'artpulse'),
        'bell',
        __('Recent notifications.', 'artpulse'),
        'ap_widget_notifications',
        [
            'category' => 'engagement',
            'settings' => [
                [
                    'key'     => 'limit',
                    'label'   => __('Items to Show', 'artpulse'),
                    'type'    => 'number',
                    'default' => 5,
                ],
            ],
        ]
    );
}

add_action('artpulse_register_dashboard_widget', 'ap_register_core_dashboard_widgets');
