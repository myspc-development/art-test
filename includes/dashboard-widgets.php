<?php
/**
 * Dashboard widget helpers and AJAX handlers.
 */

if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetManager;

function ap_render_widget(string $widget_id, int $user_id = null): void
{
    $user_id = $user_id ?: get_current_user_id();
    $role    = DashboardController::get_role($user_id);
    $widgets = DashboardWidgetRegistry::get_all();

    if (!isset($widgets[$widget_id])) {
        error_log("\xF0\x9F\x9A\xAB Widget '{$widget_id}' not found in registry.");
        return;
    }

    if (!in_array($role, $widgets[$widget_id]['roles'] ?? [], true)) {
        error_log("\xF0\x9F\x9A\xAB Widget '{$widget_id}' not allowed for role '{$role}'.");
        return;
    }

    if (has_action("ap_render_dashboard_widget_{$widget_id}")) {
        do_action("ap_render_dashboard_widget_{$widget_id}", $user_id);
        return;
    }

    $cb = $widgets[$widget_id]['callback'] ?? null;
    if (is_callable($cb)) {
        call_user_func($cb);
    } else {
        error_log("\xF0\x9F\x9A\xAB Invalid callback for widget '{$widget_id}'.");
    }
}

function register_ap_widget(string $id, array $args): void
{
    if (isset($args["component"]) && !isset($args["callback"])) {
        $args["callback"] = $args["component"];
    }
    if (isset($args["title"]) && !isset($args["label"])) {
        $args["label"] = $args["title"];
    }
    \ArtPulse\Core\DashboardWidgetRegistry::register_widget($id, $args);
}


function ap_get_all_widget_definitions(bool $include_schema = false): array
{
    return DashboardWidgetManager::getWidgetDefinitions($include_schema);
}

/**
 * Wrapper used by front-end scripts to load widget definitions with icons.
 */
function artpulse_get_dashboard_widgets(bool $include_schema = true): array
{
    return DashboardWidgetManager::getWidgetDefinitions($include_schema);
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

    DashboardWidgetManager::saveRoleLayout($role, $layout);
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Saved role layout for ' . $role . ': ' . wp_json_encode($layout));
    }
    wp_send_json_success(['saved' => true]);
}

function ap_save_user_layout(): void
{
    // Verify request nonce
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
        $input = json_decode(file_get_contents('php://input'), true);
        if (is_array($input) && isset($input['layout'])) {
            $layout = $input['layout'];
        }
    }

    $user_id = get_current_user_id();

    if ($user_id && is_array($layout)) {
        DashboardWidgetManager::saveUserLayout($user_id, $layout);
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
    return ap_load_dashboard_template('widgets/my-favorites.php', $vars);
}

function ap_widget_my_follows(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/my-follows.php', $vars);
}

function ap_widget_creator_tips(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/widget-creator-tips.php', $vars);
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

function ap_widget_cat_fact(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/cat-fact.php', $vars);
}

function ap_widget_spotlights(array $vars = []): string
{
    $vars['role'] = DashboardController::get_role(get_current_user_id());
    return ap_load_dashboard_template('widgets/widget-spotlights.php', $vars);
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

function ap_widget_upcoming_events_location(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/upcoming-events-location.php', $vars);
}

function ap_widget_followed_artists_activity(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/followed-artists-activity.php', $vars);
}

function ap_widget_artist_inbox_preview(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/artist-inbox-preview.php', $vars);
}

function ap_widget_artist_revenue_summary(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/artist-revenue-summary.php', $vars);
}

function ap_widget_artist_spotlight(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/artist-spotlight-widget.php', $vars);
}

function ap_widget_artist_artwork_manager(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/artist-artwork-manager.php', $vars);
}

function ap_widget_artist_audience_insights(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/artist-audience-insights.php', $vars);
}

function ap_widget_artist_earnings_summary(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/artist-earnings.php', $vars);
}

function ap_widget_artist_feed_publisher(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/artist-feed-publisher.php', $vars);
}

function ap_widget_collab_requests(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/collab-requests.php', $vars);
}

function ap_widget_onboarding_tracker(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/onboarding-tracker.php', $vars);
}

function ap_widget_my_rsvps(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/my-rsvps.php', $vars);
}

function ap_widget_my_shared_events_activity(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/my-shared-events-activity.php', $vars);
}

function ap_widget_recommended_for_you_member(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/recommended-for-you.php', $vars);
}

function ap_widget_dashboard_feedback(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/dashboard-feedback.php', $vars);
}

/**
 * Register core dashboard widgets.
 */
function ap_register_core_dashboard_widgets(): void
{
    // visible to all dashboards
    DashboardWidgetRegistry::register(
        'membership',
        __('Membership', 'artpulse'),
        'users',
        __('Subscription status and badges.', 'artpulse'),
        'ap_widget_membership',
        [
            'category' => 'engagement',
            'roles'    => ['member', 'artist', 'organization'],
            'visibility' => 'public',
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

    // visible to all dashboards
    DashboardWidgetRegistry::register(
        'upgrade',
        __('Upgrade', 'artpulse'),
        'star',
        __('Upgrade options for the account.', 'artpulse'),
        'ap_widget_upgrade',
        [
            'category' => 'account',
            'roles'    => ['member', 'artist', 'organization'],
            'visibility' => 'public',
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

    // visible to all dashboards
    DashboardWidgetRegistry::register(
        'local-events',
        __('Local Events', 'artpulse'),
        'map-pin',
        __('Shows events near the user.', 'artpulse'),
        'ap_widget_local_events',
        [
            'category'   => 'community',
            'roles'      => ['member'],
            'visibility' => 'public',
            'settings'   => [
                [
                    'key'     => 'limit',
                    'label'   => __('Number of Events', 'artpulse'),
                    'type'    => 'number',
                    'default' => 5,
                ],
            ],
        ]
    );

    // visible to all dashboards
    DashboardWidgetRegistry::register(
        'favorites',
        __('Favorites', 'artpulse'),
        'heart',
        __('Favorited content lists.', 'artpulse'),
        'ap_widget_favorites',
        [
            'category'   => 'engagement',
            'roles'      => ['member'],
            'visibility' => 'public',
            'settings'   => [
                [
                    'key'     => 'limit',
                    'label'   => __('Items to Show', 'artpulse'),
                    'type'    => 'number',
                    'default' => 5,
                ],
            ],
        ]
    );

    // visible to all dashboards
    DashboardWidgetRegistry::register(
        'my-follows',
        __('My Follows', 'artpulse'),
        'visibility',
        __('Artists and events you follow.', 'artpulse'),
        'ap_widget_my_follows',
        [
            'category'   => 'engagement',
            'roles'      => ['member'],
            'visibility' => 'public',
        ]
    );

    DashboardWidgetRegistry::register(
        'creator-tips',
        __('Creator Tips', 'artpulse'),
        'lightbulb',
        __('Contextual suggestions for creators.', 'artpulse'),
        'ap_widget_creator_tips',
        [
            'category' => 'engagement',
            'roles'    => ['member', 'artist', 'organization'],
            'visibility' => 'public',
        ]
    );

    // visible to all dashboards
    DashboardWidgetRegistry::register(
        'rsvps',
        __('RSVPs', 'artpulse'),
        'calendar',
        __('User RSVP history.', 'artpulse'),
        'ap_widget_rsvps',
        [
            'category' => 'events',
            'roles'    => ['member', 'artist', 'organization'],
            'visibility' => 'public',
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

    // visible to organization and member dashboards
    DashboardWidgetRegistry::register(
        'rsvp_stats',
        __('RSVP Stats', 'artpulse'),
        'calendar',
        __('RSVP summary for your events.', 'artpulse'),
        'ap_widget_rsvp_stats',
        [
            'category' => 'events',
            'roles'    => ['organization', 'member'],
            'visibility' => 'public',
        ]
    );

    // visible to all dashboards
    DashboardWidgetRegistry::register(
        'my-events',
        __('My Events', 'artpulse'),
        'clock',
        __('Events created by the user.', 'artpulse'),
        'ap_widget_my_events',
        [
            'category' => 'events',
            'roles'    => ['member', 'artist', 'organization'],
            'visibility' => 'public',
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

    // visible to all dashboards
    DashboardWidgetRegistry::register(
        'events',
        __('Upcoming Events (Global)', 'artpulse'),
        'calendar',
        __('Global upcoming events.', 'artpulse'),
        'ap_widget_events',
        [
            'category' => 'events',
            'roles'    => ['member', 'artist', 'organization'],
            'visibility' => 'public',
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

    // visible to all dashboards
    DashboardWidgetRegistry::register(
        'messages',
        __('Messages', 'artpulse'),
        'mail',
        __('Private messages inbox.', 'artpulse'),
        'ap_widget_messages',
        [
            'category'   => 'engagement',
            'roles'      => ['member'],
            'visibility' => 'public',
            'settings'   => [
                [
                    'key'     => 'limit',
                    'label'   => __('Items to Show', 'artpulse'),
                    'type'    => 'number',
                    'default' => 5,
                ],
            ],
        ]
    );

    // visible to all dashboards
    DashboardWidgetRegistry::register(
        'account-tools',
        __('Account Tools', 'artpulse'),
        'settings',
        __('Export and deletion options.', 'artpulse'),
        'ap_widget_account_tools',
        [
            'category' => 'account',
            'roles'    => ['member', 'artist', 'organization'],
            'visibility' => 'public',
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

    // visible to all dashboards
    DashboardWidgetRegistry::register(
        'support-history',
        __('Support History', 'artpulse'),
        'life-buoy',
        __('Previous support tickets.', 'artpulse'),
        'ap_widget_support_history',
        [
            'category' => 'support',
            'roles'    => ['member', 'artist', 'organization'],
            'visibility' => 'public',
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

    // visible to all dashboards
    DashboardWidgetRegistry::register(
        'notifications',
        __('Notifications', 'artpulse'),
        'bell',
        __('Recent notifications.', 'artpulse'),
        'ap_widget_notifications',
        [
            'category'   => 'engagement',
            'roles'      => ['member'],
            'visibility' => 'public',
            'settings'   => [
                [
                    'key'     => 'limit',
                    'label'   => __('Items to Show', 'artpulse'),
                    'type'    => 'number',
                    'default' => 5,
                ],
            ],
        ]
    );

    // visible to member, artist and organization dashboards
    DashboardWidgetRegistry::register(
        'role-spotlight',
        __('Featured Spotlight', 'artpulse'),
        'star-filled',
        __('Role based spotlights.', 'artpulse'),
        'ap_widget_role_spotlight',
        [
            'roles' => ['member', 'artist', 'organization'],
            'visibility' => 'public',
        ]
    );

    // visible to member, artist and organization dashboards
    DashboardWidgetRegistry::register(
        'widget_for_you_all',
        __('For You (All)', 'artpulse'),
        'sparkles',
        __('Personalized recommendations.', 'artpulse'),
        'ap_widget_for_you',
        [
            'roles'    => ['member', 'artist', 'organization'],
            'visibility' => 'public',
            'category' => 'recommended',
        ]
    );

    // visible to member and artist dashboards
    DashboardWidgetRegistry::register(
        'widget_followed_artists',
        __('Followed Artists', 'artpulse'),
        'groups',
        __('Artists the user follows.', 'artpulse'),
        'ap_widget_followed_artists',
        [
            'roles' => ['member', 'artist'],
            'visibility' => 'public',
        ]
    );

    DashboardWidgetRegistry::register(
        'upcoming_events_by_location',
        __('Upcoming Events Near You', 'artpulse'),
        'location',
        __('Lists events based on your location or saved city.', 'artpulse'),
        'ap_widget_upcoming_events_location',
        [
            'roles'    => ['member'],
            'visibility' => 'public',
            'category' => 'events',
        ]
    );

    DashboardWidgetRegistry::register(
        'followed_artists_activity',
        __('Followed Artists Activity', 'artpulse'),
        'activity',
        __('Recent uploads or events from artists you follow.', 'artpulse'),
        'ap_widget_followed_artists_activity',
        [
            'roles'    => ['member'],
            'visibility' => 'public',
            'category' => 'engagement',
        ]
    );

    DashboardWidgetRegistry::register(
        'artist_inbox_preview',
        __('Artist Inbox Preview', 'artpulse'),
        'inbox',
        __('Recent unread messages from artists.', 'artpulse'),
        'ap_widget_artist_inbox_preview',
        [
            // Available to both members and artists so creators can monitor
            // follower messages while regular users preview artist inboxes.
            'roles'    => ['member', 'artist'],
            'visibility' => 'public',
            'category' => 'engagement',
        ]
    );

    DashboardWidgetRegistry::register(
        'my_rsvps',
        __('My RSVPs', 'artpulse'),
        'calendar',
        __('Events you have RSVP\'d to.', 'artpulse'),
        'ap_widget_my_rsvps',
        [
            'roles'      => ['member'],
            'category'   => 'events',
            'visibility' => 'public',
        ]
    );

    DashboardWidgetRegistry::register(
        'my_shared_events_activity',
        __('My Shared Events', 'artpulse'),
        'share',
        __('Events you\'ve shared and engagement.', 'artpulse'),
        'ap_widget_my_shared_events_activity',
        [
            'roles'    => ['member'],
            'visibility' => 'public',
            'category' => 'engagement',
        ]
    );

    DashboardWidgetRegistry::register(
        'recommended_for_you',
        __('Recommended For You', 'artpulse'),
        'thumbs-up',
        __('Suggestions based on your interests.', 'artpulse'),
        'ap_widget_recommended_for_you_member',
        [
            'roles'      => ['member'],
            'category'   => 'recommended',
            'visibility' => 'public',
        ]
    );

    // visible to all dashboards
    DashboardWidgetRegistry::register(
        'cat_fact',
        __('Cat Fact', 'artpulse'),
        'smiley',
        __('Random cat facts from catfact.ninja.', 'artpulse'),
        'ap_widget_cat_fact',
        [
            'category'   => 'fun',
            'roles'      => ['member'],
            'visibility' => 'public',
        ]
    );

    DashboardWidgetRegistry::register(
        'dashboard_feedback',
        __('Dashboard Feedback', 'artpulse'),
        'message-circle',
        __('Send feedback about your dashboard.', 'artpulse'),
        'ap_widget_dashboard_feedback',
        [
            'roles'      => ['member'],
            'category'   => 'engagement',
            'visibility' => 'public',
        ]
    );

    // visible to member and artist dashboards
    DashboardWidgetRegistry::register(
        'instagram_widget',
        __('Instagram Feed', 'artpulse'),
        'instagram',
        __('Recent Instagram posts.', 'artpulse'),
        'ap_widget_instagram',
        [
            'category' => 'social',
            'roles'    => ['member', 'artist'],
            'visibility' => 'public',
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

    // visible to member and organization dashboards
    DashboardWidgetRegistry::register(
        'widget_spotlight_events',
        __('Event Spotlights', 'artpulse'),
        'calendar',
        __('Event related highlights.', 'artpulse'),
        'ap_widget_spotlight_events',
        [
            'roles' => ['member', 'organization'],
            'visibility' => 'public',
        ]
    );

    // visible to member, artist and organization dashboards
    DashboardWidgetRegistry::register(
        'widget_spotlight_calls',
        __('Call Spotlights', 'artpulse'),
        'phone',
        __('Calls to artists or members.', 'artpulse'),
        'ap_widget_spotlight_calls',
        [
            'roles' => ['member', 'artist', 'organization'],
            'visibility' => 'public',
        ]
    );

    // visible to member, artist and organization dashboards
    DashboardWidgetRegistry::register(
        'widget_spotlight_features',
        __('Featured Spotlights', 'artpulse'),
        'star-filled',
        __('General featured items.', 'artpulse'),
        'ap_widget_spotlight_features',
        [
            'roles' => ['member', 'artist', 'organization'],
            'visibility' => 'public',
        ]
    );

    DashboardWidgetRegistry::register(
        'artist_revenue_summary',
        __('Artist Revenue Summary', 'artpulse'),
        'dollar-sign',
        __('Revenue totals from tickets and donations.', 'artpulse'),
        'ap_widget_artist_revenue_summary',
        [
            'roles' => ['artist'],
            'visibility' => 'public',
            'category' => 'commerce',
        ]
    );

    DashboardWidgetRegistry::register(
        'artist_spotlight',
        __('Artist Spotlight', 'artpulse'),
        'star',
        __('Recent mentions and highlights.', 'artpulse'),
        'ap_widget_artist_spotlight',
        [
            'roles' => ['artist'],
            'visibility' => 'public',
            'category' => 'community',
        ]
    );

    DashboardWidgetRegistry::register(
        'artist_artwork_manager',
        __('Artwork Manager', 'artpulse'),
        'image',
        __('Upload and manage artworks.', 'artpulse'),
        'ap_widget_artist_artwork_manager',
        [
            'roles' => ['artist'],
            'visibility' => 'public',
            'default' => true
        ]
    );

    DashboardWidgetRegistry::register(
        'artist_audience_insights',
        __('Audience Insights', 'artpulse'),
        'users',
        __('Follower analytics and engagement.', 'artpulse'),
        'ap_widget_artist_audience_insights',
        [
            'roles' => ['artist'],
            'visibility' => 'public',
        ]
    );

    DashboardWidgetRegistry::register(
        'artist_earnings_summary',
        __('Earnings Summary', 'artpulse'),
        'dollar-sign',
        __('Revenue breakdown and payouts.', 'artpulse'),
        'ap_widget_artist_earnings_summary',
        [
            'roles' => ['artist'],
            'visibility' => 'public',
        ]
    );

    DashboardWidgetRegistry::register(
        'artist_feed_publisher',
        __('Post & Engage', 'artpulse'),
        'edit',
        __('Publish updates to your feed.', 'artpulse'),
        'ap_widget_artist_feed_publisher',
        [
            'roles' => ['artist'],
            'visibility' => 'public',
        ]
    );

    DashboardWidgetRegistry::register(
        'collab_requests',
        __('Collab Requests', 'artpulse'),
        'handshake',
        __('Collaboration invites from others.', 'artpulse'),
        'ap_widget_collab_requests',
        [
            'roles' => ['artist'],
            'visibility' => 'public',
        ]
    );

    DashboardWidgetRegistry::register(
        'onboarding_tracker',
        __('Onboarding Checklist', 'artpulse'),
        'check',
        __('Steps to get started.', 'artpulse'),
        'ap_widget_onboarding_tracker',
        [
            'roles' => ['artist'],
            'visibility' => 'public',
        ]
    );

    DashboardWidgetRegistry::register(
        'widget_spotlights',
        __('Spotlights', 'artpulse'),
        'star',
        __('Curated spotlights for artists.', 'artpulse'),
        'ap_widget_spotlights',
        [
            'roles' => ['artist'],
            'visibility' => 'public',
        ]
    );
}

add_action('artpulse_register_dashboard_widget', 'ap_register_core_dashboard_widgets');
