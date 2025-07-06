<?php
/**
 * Dashboard widget helpers and AJAX handlers.
 */

if (!defined('ABSPATH')) {
    exit;
}

function ap_get_all_widget_definitions(): array
{
    $defs = [
        [ 'id' => 'membership',      'name' => __('Membership', 'artpulse'),      'icon' => 'users',      'description' => __('Subscription status and badges.', 'artpulse') ],
        [ 'id' => 'upgrade',         'name' => __('Upgrade', 'artpulse'),         'icon' => 'star',       'description' => __('Upgrade options for the account.', 'artpulse') ],
        [ 'id' => 'local-events',    'name' => __('Local Events', 'artpulse'),    'icon' => 'map-pin',    'description' => __('Shows events near the user.', 'artpulse') ],
        [ 'id' => 'favorites',       'name' => __('Favorites', 'artpulse'),       'icon' => 'heart',      'description' => __('Favorited content lists.', 'artpulse') ],
        [ 'id' => 'rsvps',           'name' => __('RSVPs', 'artpulse'),           'icon' => 'calendar',   'description' => __('User RSVP history.', 'artpulse') ],
        [ 'id' => 'my-events',       'name' => __('My Events', 'artpulse'),       'icon' => 'clock',      'description' => __('Events created by the user.', 'artpulse') ],
        [ 'id' => 'events',          'name' => __('Upcoming Events', 'artpulse'), 'icon' => 'calendar',   'description' => __('Global upcoming events.', 'artpulse') ],
        [ 'id' => 'messages',        'name' => __('Messages', 'artpulse'),        'icon' => 'mail',       'description' => __('Private messages inbox.', 'artpulse') ],
        [ 'id' => 'account-tools',   'name' => __('Account Tools', 'artpulse'),   'icon' => 'settings',   'description' => __('Export and deletion options.', 'artpulse') ],
        [ 'id' => 'support-history', 'name' => __('Support History', 'artpulse'), 'icon' => 'life-buoy',  'description' => __('Previous support tickets.', 'artpulse') ],
        [ 'id' => 'notifications',   'name' => __('Notifications', 'artpulse'),   'icon' => 'bell',       'description' => __('Recent notifications.', 'artpulse') ],
        [ 'id' => 'next-payment',    'name' => __('Next Payment', 'artpulse'),    'icon' => 'credit-card','description' => __('Upcoming payouts.', 'artpulse') ],
        [ 'id' => 'transactions',    'name' => __('Transactions', 'artpulse'),    'icon' => 'list',       'description' => __('Recent transactions list.', 'artpulse') ],
        [ 'id' => 'content',         'name' => __('Your Content', 'artpulse'),    'icon' => 'image',      'description' => __('User generated content.', 'artpulse') ],
        [ 'id' => 'webhooks',        'name' => __('Webhooks', 'artpulse'),        'icon' => 'link',       'description' => __('Webhook management.', 'artpulse') ],
    ];

    return apply_filters('ap_dashboard_widget_definitions', $defs);
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
