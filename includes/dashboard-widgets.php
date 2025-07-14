<?php
/**
 * Dashboard widget helpers and AJAX handlers.
 */

if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\DashboardController;

function ap_get_all_widget_definitions(bool $include_schema = false): array
{
    return DashboardWidgetRegistry::get_definitions($include_schema);
}


add_action('wp_ajax_ap_save_dashboard_widget_config', 'ap_save_dashboard_widget_config');

add_action('wp_ajax_ap_save_widget_layout', 'ap_save_widget_layout');
add_action('wp_ajax_ap_save_role_layout', 'ap_save_role_layout');
add_action('wp_ajax_ap_save_user_layout', 'ap_save_user_layout');
add_action('wp_ajax_save_widget_order', 'ap_save_widget_order');
add_action('wp_ajax_ap_save_dashboard_order', 'ap_save_dashboard_order_callback');
add_action('wp_ajax_save_dashboard_layout', function () {
    check_admin_referer('save_dashboard_layout');
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Cheatin&#8217; uh?' ) );
    }
    check_ajax_referer('ap_widget_nonce', 'nonce');
    $layout = json_decode(stripslashes($_POST['layout'] ?? ''), true);
    if (!is_array($layout)) {
        wp_send_json_error('Invalid layout');
    }

    update_user_meta(get_current_user_id(), 'ap_dashboard_layout', $layout);
    wp_send_json_success();
});

function ap_save_dashboard_widget_config(): void
{
    check_admin_referer('ap_save_dashboard_widget_config');
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Cheatin&#8217; uh?' ) );
    }
    check_ajax_referer('ap_dashboard_widget_config', 'nonce');

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
    check_admin_referer('ap_save_widget_layout');
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Cheatin&#8217; uh?' ) );
    }
    $role = sanitize_key($_POST['role'] ?? '');
    if (!get_role($role) && !current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('Invalid role', 'artpulse')], 400);
        return;
    }
    check_ajax_referer('ap_save_widget_layout', 'nonce');
    if (!current_user_can($role) && !current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('Permission denied', 'artpulse')], 403);
        return;
    }
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Saving widget layout for user ' . get_current_user_id());
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
    check_admin_referer('ap_save_role_layout');
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Cheatin&#8217; uh?' ) );
    }
    check_ajax_referer('ap_save_role_layout', 'nonce');

    $role   = sanitize_key($_POST['role'] ?? '');
    if (!get_role($role)) {
        wp_send_json_error(['message' => __('Invalid role', 'artpulse')]);
        return;
    }
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
    check_admin_referer('ap_save_user_layout');
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Cheatin&#8217; uh?' ) );
    }
    check_ajax_referer('ap_save_user_layout', 'nonce');

    $layout = [];

    // Prefer POST parameter when the request is form encoded
    if (isset($_POST['layout'])) {
        $layout_raw = $_POST['layout'];
        if (is_string($layout_raw)) {
            $layout = json_decode(stripslashes($layout_raw), true);
        }
    } else {
        // Fallback to JSON body when sent via fetch()
        $input  = json_decode(file_get_contents('php://input'), true);
        if (is_array($input) && isset($input['layout'])) {
            $layout = $input['layout'];
        }
    }

    $user_id = get_current_user_id();

    if ($user_id && is_array($layout)) {
        \ArtPulse\Admin\UserLayoutManager::save_user_layout($user_id, $layout);
        wp_send_json_success(['message' => 'Layout saved']);
    }

    wp_send_json_error(['message' => 'Invalid data']);
}

function ap_save_widget_order(): void
{
    check_admin_referer('ap_save_widget_order');
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Cheatin&#8217; uh?' ) );
    }
    check_ajax_referer('ap_widget_order', 'nonce');
    $order = isset($_POST['order']) ? json_decode(stripslashes($_POST['order']), true) : [];
    update_user_meta(get_current_user_id(), 'ap_widget_order', $order);
    wp_send_json_success();
}

function ap_save_dashboard_order_callback(): void
{
    check_admin_referer('ap_save_dashboard_order_callback');
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Cheatin&#8217; uh?' ) );
    }
    check_ajax_referer('ap_dashboard_nonce', 'nonce');

    $order = json_decode(stripslashes($_POST['order'] ?? '[]'), true);
    if (!is_array($order)) {
        wp_send_json_error(['message' => 'Invalid format.']);
    }

    update_user_meta(get_current_user_id(), 'ap_dashboard_order', $order);

    wp_send_json_success(['message' => 'Dashboard order saved.']);
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

function ap_widget_my_follows(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/my-follows.php', $vars);
}

function ap_widget_rsvps(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/rsvps.php', $vars);
}

function ap_widget_rsvp_stats(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/rsvp-stats.php', $vars);
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

function ap_widget_for_you(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/widget-for-you.php', $vars);
}

function ap_widget_followed_artists(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/widget-followed-artists.php', $vars);
}

function ap_widget_account_tools(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/account-tools.php', $vars);
}

function ap_widget_webhooks(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/webhooks.php', $vars);
}

function ap_widget_instagram(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/instagram-widget.php', $vars);
}

function ap_widget_role_spotlight(array $vars = []): string
{
    $vars['role'] = DashboardController::get_role(get_current_user_id());
    return ap_load_dashboard_template('widgets/spotlight-dashboard.php', $vars);
}

function ap_widget_spotlight_calls(array $vars = []): string
{
    $vars['role']     = DashboardController::get_role(get_current_user_id());
    $vars['category'] = 'calls';
    return ap_load_dashboard_template('widgets/spotlight-dashboard.php', $vars);
}

function ap_widget_spotlight_events(array $vars = []): string
{
    $vars['role']     = DashboardController::get_role(get_current_user_id());
    $vars['category'] = 'events';
    return ap_load_dashboard_template('widgets/spotlight-dashboard.php', $vars);
}

function ap_widget_spotlight_features(array $vars = []): string
{
    $vars['role']     = DashboardController::get_role(get_current_user_id());
    $vars['category'] = 'featured';
    return ap_load_dashboard_template('widgets/spotlight-dashboard.php', $vars);
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
        'my-follows',
        __('My Follows', 'artpulse'),
        'visibility',
        __('Artists and events you follow.', 'artpulse'),
        'ap_widget_my_follows',
        [
            'category' => 'engagement',
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
        'rsvp_stats',
        __('RSVP Stats', 'artpulse'),
        'calendar',
        __('RSVP summary for your events.', 'artpulse'),
        'ap_widget_rsvp_stats',
        [
            'category' => 'events',
            'roles'    => ['organization', 'member'],
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

    DashboardWidgetRegistry::register(
        'role-spotlight',
        __('Featured Spotlight', 'artpulse'),
        'star-filled',
        __('Role based spotlights.', 'artpulse'),
        'ap_widget_role_spotlight',
        [
            'roles' => ['member', 'artist', 'organization'],
        ]
    );

    DashboardWidgetRegistry::register(
        'widget_for_you',
        __('For You', 'artpulse'),
        'sparkles',
        __('Personalized recommendations.', 'artpulse'),
        'ap_widget_for_you',
        [
            'roles'    => ['member', 'artist', 'organization'],
            'category' => 'recommended',
        ]
    );

    DashboardWidgetRegistry::register(
        'widget_followed_artists',
        __('Followed Artists', 'artpulse'),
        'groups',
        __('Artists the user follows.', 'artpulse'),
        'ap_widget_followed_artists',
        [
            'roles' => ['member', 'artist'],
        ]
    );

    DashboardWidgetRegistry::register(
        'instagram_widget',
        __('Instagram Feed', 'artpulse'),
        'instagram',
        __('Recent Instagram posts.', 'artpulse'),
        'ap_widget_instagram',
        [
            'category' => 'social',
            'roles'    => ['member', 'artist'],
            'settings' => [
                [
                    'key'   => 'access_token',
                    'label' => __('Access Token', 'artpulse'),
                    'type'  => 'text',
                ],
                [
                    'key'   => 'urls',
                    'label' => __('Post URLs', 'artpulse'),
                    'type'  => 'text',
                ],
                [
                    'key'     => 'count',
                    'label'   => __('Items to Show', 'artpulse'),
                    'type'    => 'number',
                    'default' => 3,
                ],
            ],
        ]
    );

    DashboardWidgetRegistry::register(
        'widget_spotlight_events',
        __('Event Spotlights', 'artpulse'),
        'calendar',
        __('Event related highlights.', 'artpulse'),
        'ap_widget_spotlight_events',
        [
            'roles' => ['member', 'organization'],
        ]
    );

    DashboardWidgetRegistry::register(
        'widget_spotlight_calls',
        __('Call Spotlights', 'artpulse'),
        'phone',
        __('Calls to artists or members.', 'artpulse'),
        'ap_widget_spotlight_calls',
        [
            'roles' => ['member', 'artist', 'organization'],
        ]
    );

    DashboardWidgetRegistry::register(
        'widget_spotlight_features',
        __('Featured Spotlights', 'artpulse'),
        'star-filled',
        __('General featured items.', 'artpulse'),
        'ap_widget_spotlight_features',
        [
            'roles' => ['member', 'artist', 'organization'],
        ]
    );
}

add_action('artpulse_register_dashboard_widget', 'ap_register_core_dashboard_widgets');
