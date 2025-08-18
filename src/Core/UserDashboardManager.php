<?php
namespace ArtPulse\Core;

use WP_REST_Request;
use ArtPulse\Community\FavoritesManager;
use ArtPulse\Core\UserEngagementLogger;
use Stripe\StripeClient;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\DashboardWidgetManager;
use ArtPulse\Admin\LayoutSnapshotManager;
use ArtPulse\Core\DashboardController;
use ArtPulse\Core\LayoutUtils;

class UserDashboardManager
{
    /**
     * Generate a cache-busting asset version based on file modification time.
     */
    private static function get_asset_version(string $relative_path): string
    {
        $path = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . ltrim($relative_path, '/');
        return file_exists($path) ? (string) filemtime($path) : '1.0.0';
    }

    public static function register()
    {
        ShortcodeRegistry::register('ap_user_dashboard', 'Member Dashboard', [ self::class, 'renderDashboard' ]);
        add_action('wp_enqueue_scripts',   [ self::class, 'enqueueAssets' ]);
        add_action('rest_api_init',        [ self::class, 'register_routes' ]);
        // Dashboard widgets are registered via the `artpulse_register_dashboard_widget` hook.
    }

    // Aliased method for compatibility with provided code snippet
    public static function register_routes()
    {
        self::registerRestRoutes();
    }

    public static function enqueueAssets()
    {
        // Core dashboard script
        wp_enqueue_script(
            'ap-widget-lifecycle',
            plugins_url('assets/js/widget-lifecycle.js', ARTPULSE_PLUGIN_FILE),
            [],
            self::get_asset_version('assets/js/widget-lifecycle.js'),
            true
        );
        wp_enqueue_script(
            'ap-user-dashboard-js',
            plugins_url('assets/js/ap-user-dashboard.js', ARTPULSE_PLUGIN_FILE),
            ['wp-api-fetch', 'chart-js', 'ap-widget-lifecycle'],
            self::get_asset_version('assets/js/ap-user-dashboard.js'),
            true
        );

        // Analytics events
        wp_enqueue_script(
            'ap-analytics-js',
            plugins_url('assets/js/ap-analytics.js', ARTPULSE_PLUGIN_FILE),
            ['ap-user-dashboard-js'],
            self::get_asset_version('assets/js/ap-analytics.js'),
            true
        );

        wp_enqueue_script(
            'ap-event-analytics',
            plugins_url('assets/js/event-analytics.js', ARTPULSE_PLUGIN_FILE),
            ['ap-user-dashboard-js'],
            self::get_asset_version('assets/js/event-analytics.js'),
            true
        );

        // Localize dashboard REST endpoint
        $opts = get_option('artpulse_settings', []);
        wp_localize_script('ap-user-dashboard-js', 'ArtPulseDashboardApi', [
            'root'             => esc_url_raw(rest_url()),
            'nonce'            => wp_create_nonce('wp_rest'),
            'orgSubmissionUrl' => self::get_org_submission_url(),
            'artistSubmissionUrl' => self::get_artist_submission_url(),
            'artistEndpoint'   => esc_url_raw(rest_url('artpulse/v1/artist-upgrade')),
            'exportEndpoint'   => esc_url_raw(rest_url('artpulse/v1/user/export')),
            'deleteEndpoint'   => esc_url_raw(rest_url('artpulse/v1/user/delete')),
            'apiUrl'           => esc_url_raw($opts['external_api_base_url'] ?? ''),
            'apiToken'         => $opts['external_api_token'] ?? '',
        ]);

        wp_localize_script('ap-user-dashboard-js', 'apL10n', [
            'membership_level'  => __('Membership Level', 'artpulse'),
            'expires'           => __('Expires', 'artpulse'),
            'never'             => __('Never', 'artpulse'),
            'upgrade_artist'    => __('Request Artist Upgrade', 'artpulse'),
            'artist_pending'    => __('Artist upgrade request pending.', 'artpulse'),
            'request_submitted' => __('Request submitted', 'artpulse'),
            'submit_org'        => __('Submit Organization', 'artpulse'),
            'upgrade_org'       => __('Upgrade to Organization', 'artpulse'),
            'org_pending'       => __('Organization upgrade request pending.', 'artpulse'),
            'pause'             => __('Pause Membership', 'artpulse'),
            'resume'            => __('Resume Membership', 'artpulse'),
            'events'            => __('Events', 'artpulse'),
            'artists'           => __('Artists', 'artpulse'),
            'artworks'          => __('Artworks', 'artpulse'),
            'subscription_status' => __('Subscription Status', 'artpulse'),
            'next_payment'        => __('Next Payment', 'artpulse'),
            'recent_transactions' => __('Recent Transactions', 'artpulse'),
            'no_transactions'     => __('No transactions found.', 'artpulse'),
            'export_json'         => __('Export JSON', 'artpulse'),
            'export_csv'          => __('Export CSV', 'artpulse'),
            'delete_account'      => __('Delete Account', 'artpulse'),
            'reset_confirm'       => __('Reset dashboard layout?', 'artpulse'),
        ]);

        if (is_user_logged_in()) {
            $uid = get_current_user_id();
            [$months, $rsvp_counts, $favorite_counts] = self::get_trend_data($uid);
            wp_localize_script('ap-user-dashboard-js', 'APUserTrends', [
                'months'         => $months,
                'rsvpCounts'     => $rsvp_counts,
                'favoriteCounts' => $favorite_counts,
            ]);

            $stats = \ArtPulse\Core\UserEngagementLogger::get_stats($uid);
            wp_localize_script('ap-user-dashboard-js', 'APUserStats', $stats);

            wp_localize_script('ap-user-dashboard-js', 'APProfileMetrics', [
                'endpoint'   => esc_url_raw(rest_url('artpulse/v1/profile-metrics')),
                'profileId' => $uid,
                'nonce'     => wp_create_nonce('wp_rest'),
            ]);

            wp_localize_script('ap-event-analytics', 'APEventAnalytics', [
                'endpoint' => esc_url_raw(rest_url('artpulse/v1/analytics')),
                'eventId'  => 0,
                'nonce'    => wp_create_nonce('wp_rest'),
            ]);
        }

        // Dashboard styles
        if (function_exists('ap_enqueue_global_styles')) {
            ap_enqueue_global_styles();
        }

        wp_enqueue_media();
        wp_enqueue_script(
            'ap-profile-modal',
            plugins_url('assets/js/ap-profile-modal.js', ARTPULSE_PLUGIN_FILE),
            [],
            self::get_asset_version('assets/js/ap-profile-modal.js'),
            true
        );
        wp_localize_script('ap-profile-modal', 'APProfileModal', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('ap_profile_edit_action'),
        ]);

        wp_enqueue_script(
            'ap-widget-settings',
            plugins_url('assets/js/ap-widget-settings.js', ARTPULSE_PLUGIN_FILE),
            [],
            self::get_asset_version('assets/js/ap-widget-settings.js'),
            true
        );
        wp_localize_script('ap-widget-settings', 'APWidgetSettings', [
            'root'  => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);

        // Enable drag-and-drop layout editing
        wp_enqueue_script(
            'sortablejs',
            plugins_url('assets/libs/sortablejs/Sortable.min.js', ARTPULSE_PLUGIN_FILE),
            [],
            self::get_asset_version('assets/libs/sortablejs/Sortable.min.js'),
            true
        );
        wp_enqueue_script(
            'user-dashboard-layout',
            plugins_url('assets/js/user-dashboard-layout.js', ARTPULSE_PLUGIN_FILE),
            ['sortablejs'],
            self::get_asset_version('assets/js/user-dashboard-layout.js'),
            true
        );
        wp_localize_script('user-dashboard-layout', 'APLayout', [
            'nonce'    => wp_create_nonce('ap_save_user_layout'),
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);

        wp_enqueue_script(
            'ap-dashboard-nav',
            plugins_url('assets/js/dashboard-nav.js', ARTPULSE_PLUGIN_FILE),
            [],
            self::get_asset_version('assets/js/dashboard-nav.js'),
            true
        );
        $user = wp_get_current_user();
        $menu = ap_merge_dashboard_menus($user->roles, true);
        if (empty($menu)) {
            $menu = [
                [
                    'id' => 'placeholder',
                    'section' => '#',
                    'label' => __('No menu items available', 'artpulse'),
                    'icon' => 'dashicons-info',
                    'capability' => 'read',
                ],
            ];
        }
        wp_localize_script('ap-dashboard-nav', 'APDashboardMenu', [
            'roles' => $user->roles,
            'menu'  => $menu,
            'debug' => defined('WP_DEBUG') && WP_DEBUG,
        ]);
    }

    public static function registerRestRoutes()
    {
        if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/user/dashboard')) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/user/dashboard', [
            'methods'             => 'GET',
            'callback'            => [ self::class, 'getDashboardData' ],
            'permission_callback' => function() {
                if (!is_user_logged_in() || !current_user_can('view_artpulse_dashboard')) {
                    return new \WP_Error('rest_forbidden', __('Unauthorized.', 'artpulse'), ['status' => 403]);
                }
                return true;
            },
        ]);
        }

        if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/user/profile')) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/user/profile', [
            'methods'             => 'POST',
            'callback'            => [ self::class, 'updateProfile' ],
            'permission_callback' => function() {
                if (!current_user_can('read')) {
                    return new \WP_Error('rest_forbidden', __('Unauthorized.', 'artpulse'), ['status' => 403]);
                }
                return true;
            },
        ]);
        }

        if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/user/engagement')) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/user/engagement', [
            'methods'             => 'GET',
            'callback'            => [ self::class, 'getEngagementFeed' ],
            'permission_callback' => function() {
                if (!is_user_logged_in() || !current_user_can('view_artpulse_dashboard')) {
                    return new \WP_Error('rest_forbidden', __('Unauthorized.', 'artpulse'), ['status' => 403]);
                }
                return true;
            },
            'args' => [
                'page' => [ 'type' => 'integer', 'default' => 1 ],
                'per_page' => [ 'type' => 'integer', 'default' => 10 ],
            ],
        ]);
        }

        if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/user/onboarding')) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/user/onboarding', [
            'methods'             => 'POST',
            'callback'            => [ self::class, 'saveOnboardingProgress' ],
            'permission_callback' => function() {
                if (!current_user_can('read')) {
                    return new \WP_Error('rest_forbidden', __('Unauthorized.', 'artpulse'), ['status' => 403]);
                }
                return true;
            },
            'args'                => [
                'step' => [ 'type' => 'string', 'required' => true ],
            ],
        ]);
        }

        if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/dashboard-tour')) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/dashboard-tour', [
            'methods'             => 'POST',
            'callback'            => [ self::class, 'completeDashboardTour' ],
            'permission_callback' => function() {
                if (!current_user_can('read')) {
                    return new \WP_Error('rest_forbidden', __('Unauthorized.', 'artpulse'), ['status' => 403]);
                }
                return true;
            },
        ]);
        }

        if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/ap/widgets/available')) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/ap/widgets/available', [
            'methods'             => 'GET',
            'callback'            => [ self::class, 'getAvailableWidgets' ],
            'permission_callback' => function() {
                return current_user_can('read');
            },
            'args' => [
                'role' => [ 'type' => 'string', 'required' => false ],
            ],
        ]);
        }

        if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/ap/layout')) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/ap/layout', [
            'methods'             => 'GET',
            'callback'            => [ self::class, 'getDashboardLayout' ],
            'permission_callback' => function() {
                return current_user_can('read');
            },
            'args' => [
                'role' => [ 'type' => 'string', 'required' => false ],
            ],
        ]);
        }

        if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/ap/layout/save')) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/ap/layout/save', [
            'methods'             => 'POST',
            'callback'            => [ self::class, 'saveDashboardLayout' ],
            'permission_callback' => function() {
                return current_user_can('read');
            },
            'args'                => [
                'layout'     => [ 'type' => 'array', 'required' => false ],
                'visibility' => [ 'type' => 'object', 'required' => false ],
            ],
        ]);
        }

        if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/ap/layout/reset')) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/ap/layout/reset', [
            'methods'             => 'POST',
            'callback'            => [ self::class, 'resetDashboardLayout' ],
            'permission_callback' => function() {
                return current_user_can('read');
            },
        ]);
        }

        if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/ap/layout/revert')) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/ap/layout/revert', [
            'methods'             => 'POST',
            'callback'            => [ self::class, 'revertDashboardLayout' ],
            'permission_callback' => function() {
                return current_user_can('read');
            },
        ]);
        }

        // Temporary aliases for backward compatibility
        if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/ap_dashboard_layout')) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/ap_dashboard_layout', [
                [
                    'methods'  => 'GET',
                    'callback' => [ self::class, 'getDashboardLayout' ],
                    'permission_callback' => function() { return current_user_can('read'); },
                    'args' => [
                        'role' => [ 'type' => 'string', 'required' => false ],
                    ],
                ],
                [
                    'methods'  => 'POST',
                    'callback' => [ self::class, 'saveDashboardLayout' ],
                    'permission_callback' => function() { return current_user_can('read'); },
                    'args'     => [
                        'layout'     => [ 'type' => 'array', 'required' => false ],
                        'visibility' => [ 'type' => 'object', 'required' => false ],
                    ],
                ],
            ]);
        }
    }

    public static function getDashboardData(WP_REST_Request $request)
    {
        if (!current_user_can('view_artpulse_dashboard')) {
            return new \WP_Error('forbidden', __('You do not have permission to view this dashboard.', 'artpulse'), ['status' => 403]);
        }
        $user_id = get_current_user_id();
        $data = [
            'membership_level'   => get_user_meta($user_id, 'ap_membership_level', true),
            'membership_expires' => get_user_meta($user_id, 'ap_membership_expires', true),
            'membership_paused'  => (bool) get_user_meta($user_id, 'ap_membership_paused', true),
            'dashboard_theme'   => get_user_meta($user_id, 'ap_dashboard_theme', true),
            'country'            => get_user_meta($user_id, 'ap_country', true),
            'state'              => get_user_meta($user_id, 'ap_state', true),
            'city'               => get_user_meta($user_id, 'ap_city', true),
            'artist_request_pending' => !empty(get_posts([
                'post_type'      => 'ap_artist_request',
                'post_status'    => 'pending',
                'author'         => $user_id,
                'posts_per_page' => 1,
                'fields'         => 'ids',
            ])),
            'org_request_pending' => (bool) get_user_meta($user_id, 'ap_pending_organization_id', true),
            'events'             => [],
            'artists'            => [],
            'artworks'           => [],
            'favorite_events'    => [],
            'favorite_artists'   => [],
            'favorite_orgs'      => [],
            'favorite_artworks'  => [],
            'rsvp_events'        => [],
            'support_history'    => [],
        ];

        foreach ( ['event','artist','artwork'] as $type ) {
            $posts = get_posts([
                'post_type'      => "artpulse_{$type}",
                'author'         => $user_id,
                'posts_per_page' => -1,
            ]);
            foreach ( $posts as $p ) {
                $data[$type . 's'][] = [
                    'id'    => $p->ID,
                    'title' => $p->post_title,
                    'link'  => get_permalink($p),
                ];
            }
        }

        // Fetch favorited events
        $favorite_events = FavoritesManager::get_user_favorites($user_id, 'artpulse_event');
        $favorite_ids = [];
        foreach ($favorite_events as $fav) {
            $post = get_post($fav->object_id);
            if (!$post) {
                continue;
            }
            $favorite_ids[] = (int) $post->ID;
            $data['favorite_events'][] = [
                'id'    => $post->ID,
                'title' => $post->post_title,
                'link'  => get_permalink($post),
                'date'  => get_post_meta($post->ID, '_ap_event_date', true),
            ];
        }

        // Fetch favorited artists, orgs and artworks
        $favorite_types = [
            'artpulse_artist'   => 'favorite_artists',
            'artpulse_org'      => 'favorite_orgs',
            'artpulse_artwork'  => 'favorite_artworks',
        ];

        foreach ($favorite_types as $post_type => $key) {
            $favorites = FavoritesManager::get_user_favorites($user_id, $post_type);
            foreach ($favorites as $fav) {
                $post = get_post($fav->object_id);
                if (!$post) {
                    continue;
                }
                $data[$key][] = [
                    'id'    => $post->ID,
                    'title' => $post->post_title,
                    'link'  => get_permalink($post),
                ];
            }
        }

        $rsvp_ids = get_user_meta($user_id, 'ap_rsvp_events', true);
        if (!is_array($rsvp_ids)) {
            $rsvp_ids = [];
        }
        foreach ($rsvp_ids as $eid) {
            $post = get_post($eid);
            if (!$post) {
                continue;
            }
            $date = get_post_meta($eid, '_ap_event_date', true);
            if ($date && strtotime($date) < time()) {
                continue;
            }
            $data['rsvp_events'][] = [
                'id'    => $post->ID,
                'title' => $post->post_title,
                'link'  => get_permalink($post),
                'date'  => $date,
            ];
        }

        $data['favorite_count'] = count($data['favorite_events'])
            + count($data['favorite_artists'])
            + count($data['favorite_orgs'])
            + count($data['favorite_artworks']);
        $data['rsvp_count']     = count($data['rsvp_events']);

        // Consolidated my events list
        $event_ids = array_unique(array_merge($favorite_ids, $rsvp_ids));
        $my_events = [];
        foreach ($event_ids as $eid) {
            $post = get_post($eid);
            if (!$post) {
                continue;
            }
            $date = get_post_meta($eid, '_ap_event_date', true);
            $my_events[] = [
                'id'        => $post->ID,
                'title'     => $post->post_title,
                'link'      => get_permalink($post),
                'date'      => $date,
                'rsvped'    => in_array($eid, $rsvp_ids, true),
                'favorited' => in_array($eid, $favorite_ids, true),
            ];
        }

        usort($my_events, function ($a, $b) {
            return strcmp($a['date'] ?? '', $b['date'] ?? '');
        });
        $data['my_events'] = $my_events;
        $data['my_event_count'] = count($my_events);

        // Determine next upcoming RSVP event
        $next_event = null;
        $soonest = PHP_INT_MAX;
        foreach ($rsvp_ids as $eid) {
            $date = get_post_meta($eid, '_ap_event_date', true);
            if (!$date) {
                continue;
            }
            $ts = strtotime($date);
            if ($ts < time() || $ts >= $soonest) {
                continue;
            }
            $post = get_post($eid);
            if ($post) {
                $soonest = $ts;
                $next_event = [
                    'id'        => $post->ID,
                    'title'     => $post->post_title,
                    'link'      => get_permalink($post),
                    'date'      => $date,
                    'rsvped'    => true,
                    'favorited' => in_array($eid, $favorite_ids, true),
                ];
            }
        }
        $data['next_event'] = $next_event;

        // Previous support requests or tickets
        $history_ids = get_user_meta($user_id, 'ap_support_history', true);
        if (is_array($history_ids)) {
            foreach ($history_ids as $sid) {
                $p = get_post($sid);
                if (!$p) {
                    continue;
                }
                $data['support_history'][] = [
                    'id'    => $p->ID,
                    'title' => $p->post_title,
                    'link'  => get_permalink($p),
                ];
            }
        }


        // Upcoming payment/renewal date
        $next_payment = intval(get_user_meta($user_id, 'ap_membership_expires', true));
        if (!$next_payment && class_exists(StripeClient::class)) {
            $customer_id = get_user_meta($user_id, 'stripe_customer_id', true);
            $settings    = get_option('artpulse_settings', []);
            $secret      = $settings['stripe_secret'] ?? '';

            if ($customer_id && $secret) {
                try {
                    $stripe = new StripeClient($secret);
                    $subs   = $stripe->subscriptions->all([
                        'customer' => $customer_id,
                        'status'   => 'active',
                        'limit'    => 1,
                    ]);

                    if (!empty($subs->data)) {
                        $next_payment = (int) $subs->data[0]->current_period_end;
                    }
                } catch (\Exception $e) {
                    error_log('Stripe error: ' . $e->getMessage());
                }
            }
        }

        $data['next_payment'] = $next_payment ?: null;
        // Recent transactions via WooCommerce or stored Stripe charge IDs
        $data['transactions'] = [];
        if (function_exists('wc_get_orders')) {
            $orders = wc_get_orders([
                'customer_id' => $user_id,
                'limit'       => 5,
                'orderby'     => 'date',
                'order'       => 'DESC',
            ]);
            foreach ($orders as $order) {
                $data['transactions'][] = [
                    'id'     => $order->get_id(),
                    'total'  => $order->get_total(),
                    'date'   => $order->get_date_created() ? $order->get_date_created()->getTimestamp() : null,
                    'status' => $order->get_status(),
                ];
            }
        } else {
            $charges = get_user_meta($user_id, 'stripe_payment_ids', true);
            if (is_array($charges)) {
                $charges = array_slice(array_reverse($charges), 0, 5);
                foreach ($charges as $cid) {
                    $data['transactions'][] = [ 'id' => $cid ];
                }
            }
        }

        $data['user_badges'] = self::getBadges($user_id);

        return rest_ensure_response($data);
    }

    public static function updateProfile(WP_REST_Request $request)
    {
        $user_id = get_current_user_id();
        $params  = $request->get_json_params();
        if ( isset($params['display_name']) ) {
            wp_update_user([
                'ID'           => $user_id,
                'display_name' => sanitize_text_field($params['display_name']),
            ]);
        }
        if ( isset($params['ap_country']) ) {
            update_user_meta($user_id, 'ap_country', sanitize_text_field($params['ap_country']));
        }
        if ( isset($params['ap_state']) ) {
            update_user_meta($user_id, 'ap_state', sanitize_text_field($params['ap_state']));
        }
        if ( isset($params['ap_city']) ) {
            update_user_meta($user_id, 'ap_city', sanitize_text_field($params['ap_city']));
        }
        if ( isset($params['ap_profile_public']) ) {
            update_user_meta($user_id, 'ap_profile_public', $params['ap_profile_public'] ? 1 : 0);
        }
        return rest_ensure_response([ 'success' => true ]);
    }

    public static function getEngagementFeed(WP_REST_Request $request)
    {
        if (!current_user_can('view_artpulse_dashboard')) {
            return new \WP_Error('forbidden', __('You do not have permission to view this dashboard.', 'artpulse'), ['status' => 403]);
        }

        $user_id  = get_current_user_id();
        $page     = max(1, (int) $request->get_param('page'));
        $per_page = max(1, (int) $request->get_param('per_page'));
        $offset   = ($page - 1) * $per_page;

        $items = UserEngagementLogger::get_feed($user_id, $per_page, $offset);
        return rest_ensure_response($items);
    }

    private static function get_trend_data(int $user_id): array
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[] = date('Y-m', strtotime("-$i month"));
        }

        $rsvp_counts = array_fill(0, 6, 0);
        $ids = get_user_meta($user_id, 'ap_rsvp_events', true);
        if (is_array($ids)) {
            foreach ($ids as $eid) {
                $date = get_post_meta($eid, '_ap_event_date', true);
                if (!$date) {
                    continue;
                }
                $month = substr($date, 0, 7);
                $index = array_search($month, $months, true);
                if ($index !== false) {
                    $rsvp_counts[$index]++;
                }
            }
        }

        $favorite_counts = array_fill(0, 6, 0);
        $favs = FavoritesManager::get_user_favorites($user_id, 'artpulse_event');
        foreach ($favs as $fav) {
            if (empty($fav->favorited_on)) {
                continue;
            }
            $month = substr($fav->favorited_on, 0, 7);
            $index = array_search($month, $months, true);
            if ($index !== false) {
                $favorite_counts[$index]++;
            }
        }

        return [$months, $rsvp_counts, $favorite_counts];
    }

    private static function get_org_submission_url(): string
    {
        $pages = get_posts([
            'post_type'   => 'page',
            'post_status' => 'publish',
            's'           => '[ap_submit_organization]',
            'numberposts' => 1,
        ]);

        if (!empty($pages)) {
            return get_permalink($pages[0]->ID);
        }

        return home_url('/');
    }

    private static function get_artist_submission_url(): string
    {
        $pages = get_posts([
            'post_type'   => 'page',
            'post_status' => 'publish',
            's'           => '[ap_submit_artist]',
            'numberposts' => 1,
        ]);

        if (!empty($pages)) {
            return get_permalink($pages[0]->ID);
        }

        return home_url('/');
    }

    private static function get_profile_edit_url(): string
    {
        // First look for a dedicated edit profile page
        $pages = get_posts([
            'post_type'   => 'page',
            'post_status' => 'publish',
            's'           => '[ap_profile_edit]',
            'numberposts' => 1,
        ]);

        if (!empty($pages)) {
            return get_permalink($pages[0]->ID);
        }

        // Fallback to the user profile page if the edit page is missing
        $profile_pages = get_posts([
            'post_type'   => 'page',
            'post_status' => 'publish',
            's'           => '[ap_user_profile]',
            'numberposts' => 1,
        ]);

        if (!empty($profile_pages)) {
            return get_permalink($profile_pages[0]->ID);
        }

        return home_url('/');
    }

    public static function saveOnboardingProgress(WP_REST_Request $request)
    {
        $user_id = get_current_user_id();
        $step    = sanitize_key($request->get_param('step'));

        if ($step === 'skip') {
            update_user_meta($user_id, 'ap_onboarding_completed', 1);
            return rest_ensure_response(['completed' => true]);
        }

        $steps = get_user_meta($user_id, 'ap_onboarding_steps', true);
        if (!is_array($steps)) {
            $steps = [];
        }
        if (!in_array($step, $steps, true)) {
            $steps[] = $step;
            update_user_meta($user_id, 'ap_onboarding_steps', $steps);
        }

        $all = apply_filters('artpulse_onboarding_steps', ['profile']);
        $completed = empty(array_diff($all, $steps));
        if ($completed) {
            update_user_meta($user_id, 'ap_onboarding_completed', 1);
        }

        return rest_ensure_response(['completed' => $completed]);
    }

    public static function getDashboardLayout(?\WP_REST_Request $request = null): \WP_REST_Response
    {
        $role = '';
        if ($request instanceof \WP_REST_Request) {
            $role = sanitize_key((string) $request->get_param('role'));
        }

        $uid         = get_current_user_id();
        $layout_meta = $role ? [] : get_user_meta($uid, 'ap_dashboard_layout', true);
        $layout      = [];
        $visibility  = [];
        if (is_array($layout_meta)) {
            $valid      = array_column(DashboardWidgetRegistry::get_definitions(), 'id');
            $normalized = LayoutUtils::normalize_layout($layout_meta, $valid);
            foreach ($normalized as $item) {
                if (!in_array($item['id'], $valid, true)) {
                    continue;
                }
                $layout[]         = $item['id'];
                $visibility[$item['id']] = $item['visible'];
            }
        }

        if (empty($layout)) {
            $roles  = $role ? [$role] : wp_get_current_user()->roles;
            $config = get_option('ap_dashboard_widget_config', []);
            foreach ($roles as $r) {
                if (!empty($config[$r]) && is_array($config[$r])) {
                    foreach ($config[$r] as $item) {
                        if (is_array($item)) {
                            $id  = sanitize_key($item['id'] ?? '');
                            $vis = isset($item['visible']) ? (bool) $item['visible'] : true;
                        } else {
                            $id  = sanitize_key($item);
                            $vis = true;
                        }
                        if (in_array($id, $layout, true)) {
                            continue;
                        }
                        $layout[]        = $id;
                        $visibility[$id] = $vis;
                    }
                }
                // Ensure all widgets from the registry are present
                $allowed = array_keys(DashboardWidgetRegistry::get_widgets($r, $uid));
                foreach ($allowed as $id) {
                    $id = sanitize_key($id);
                    if (in_array($id, $layout, true)) {
                        continue;
                    }
                    $layout[]        = $id;
                    if (!isset($visibility[$id])) {
                        $visibility[$id] = true;
                    }
                }
            }
        }
        if (!$role) {
            $vis_meta = get_user_meta($uid, 'ap_widget_visibility', true);
            if (is_array($vis_meta)) {
                foreach ($vis_meta as $id => $vis) {
                    $id = sanitize_key($id);
                    if (isset($visibility[$id])) {
                        $visibility[$id] = (bool) $vis;
                    }
                }
            }
        }

        return rest_ensure_response([
            'layout'     => $layout,
            'visibility' => $visibility,
        ]);
    }

    public static function resetDashboardLayout(): \WP_REST_Response
    {
        $uid = get_current_user_id();
        \ArtPulse\Core\DashboardWidgetManager::resetUserLayout($uid);
        return rest_ensure_response(['reset' => true]);
    }

    public static function revertDashboardLayout(): \WP_REST_Response
    {
        $uid = get_current_user_id();
        $ok = \ArtPulse\Admin\LayoutSnapshotManager::restore_last($uid);
        return rest_ensure_response(['reverted' => $ok]);
    }

    public static function getAvailableWidgets(WP_REST_Request $request): \WP_REST_Response
    {
        $role = sanitize_key($request->get_param('role') ?? 'member');
        $defs = DashboardWidgetRegistry::get_widgets_by_role($role);
        $formatted = array_map(
            fn($d) => [
                'id'          => $d['id'],
                'name'        => $d['label'],
                'icon'        => $d['icon'],
                'description' => $d['description'],
            ],
            $defs
        );
        return rest_ensure_response(array_values($formatted));
    }

    public static function saveDashboardLayout(WP_REST_Request $request): \WP_REST_Response
    {
        $uid = get_current_user_id();

        if ($request->has_param('layout')) {
            $layout_raw = $request->get_param('layout');
            if (is_string($layout_raw)) {
                $layout_raw = json_decode($layout_raw, true);
            }
            $valid_ids = array_column(DashboardWidgetRegistry::get_definitions(), 'id');
            $ordered    = LayoutUtils::normalize_layout((array) $layout_raw, $valid_ids);
            $ordered    = array_values(array_filter(
                $ordered,
                static fn($item) => in_array($item['id'], $valid_ids, true)
            ));
            update_user_meta($uid, 'ap_dashboard_layout', $ordered);
        } elseif ($request->has_param('visibility')) {
            $vis_raw = (array) $request->get_param('visibility');
            $current = get_user_meta($uid, 'ap_dashboard_layout', true);
            if (!is_array($current)) {
                $current = [];
            }
            $updated = [];
            foreach ($current as $item) {
                if (is_array($item) && isset($item['id'])) {
                    $id = sanitize_key($item['id']);
                    $vis = isset($vis_raw[$id]) ? (bool) $vis_raw[$id] : ($item['visible'] ?? true);
                    $updated[] = ['id' => $id, 'visible' => $vis];
                } elseif (is_string($item)) {
                    $id = sanitize_key($item);
                    $vis = isset($vis_raw[$id]) ? (bool) $vis_raw[$id] : true;
                    $updated[] = ['id' => $id, 'visible' => $vis];
                }
            }
            update_user_meta($uid, 'ap_dashboard_layout', $updated);
        }

        return rest_ensure_response(['saved' => true]);
    }

    public static function completeDashboardTour(WP_REST_Request $request): \WP_REST_Response
    {
        $uid = get_current_user_id();
        update_user_meta($uid, 'ap_dashboard_tour_complete', 1);
        return rest_ensure_response(['completed' => true]);
    }

    public static function addBadge(int $user_id, string $slug): void
    {
        $badges = get_user_meta($user_id, 'user_badges', true);
        if (!is_array($badges)) {
            $badges = [];
        }
        if (!in_array($slug, $badges, true)) {
            $badges[] = $slug;
            update_user_meta($user_id, 'user_badges', $badges);
        }
    }

    public static function getBadges(int $user_id): array
    {
        $badges = get_user_meta($user_id, 'user_badges', true);
        return is_array($badges) ? $badges : [];
    }

    private static function load_template(string $template, array $vars = []): string
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

    public static function renderDashboard($atts)
    {
        $uid      = get_current_user_id();
        $role     = DashboardController::get_role($uid);
        $can_view = current_user_can('view_artpulse_dashboard');

        if (defined('WP_DEBUG') && WP_DEBUG) {
            $u     = wp_get_current_user();
            $roles = $u ? implode(',', (array) $u->roles) : 'guest';
            error_log(
                sprintf(
                    'ap_user_dashboard uid=%d roles=%s can_view=%s resolved_role=%s',
                    $uid,
                    $roles,
                    $can_view ? 'yes' : 'no',
                    $role
                )
            );
        }

        if (!is_user_logged_in() || !$can_view) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                $u = wp_get_current_user();
                $roles = $u ? implode(',', (array) $u->roles) : 'guest';
                error_log(sprintf('ap_user_dashboard denied uid=%d role=%s roles=%s', $uid, $role, $roles));
            }
            return '<p>' . __('Please log in to view your dashboard.', 'artpulse') . '</p>';
        }

        wp_enqueue_style('ap-react-dashboard');
        wp_enqueue_script('ap-react-vendor');
        wp_enqueue_script('ap-react-dashboard');

        $atts = shortcode_atts([
            'show_forms' => false,
        ], $atts, 'ap_user_dashboard');

        $show_forms = filter_var($atts['show_forms'], FILTER_VALIDATE_BOOLEAN);

        $artist_form = $show_forms ? do_shortcode('[ap_submit_artist]') : '';
        $org_form    = $show_forms ? do_shortcode('[ap_submit_organization]') : '';

        $user             = wp_get_current_user();
        $profile_edit_url = self::get_profile_edit_url();
        $show_notifications =
            in_array('member', $user->roles, true) ||
            in_array('artist', $user->roles, true) ||
            in_array('organization', $user->roles, true) ||
            user_can($user, 'manage_options');
        $support_history   = get_user_meta(get_current_user_id(), 'ap_support_history', true);
        $show_support_history = is_array($support_history) && !empty($support_history);
        $badges = self::getBadges(get_current_user_id());

        $roles_list = [$role];
        $widgets = DashboardWidgetRegistry::get_widgets($role, $uid);
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('ap_user_dashboard roles: ' . implode(',', $roles_list));
            error_log('ap_user_dashboard widgets: ' . implode(',', array_keys($widgets)));
        }
        $layout_resp  = self::getDashboardLayout();
        $layout_data  = method_exists($layout_resp, 'get_data') ? $layout_resp->get_data() : (array) $layout_resp;
        $layout       = $layout_data['layout'] ?? [];
        $visibility   = $layout_data['visibility'] ?? [];
        if (!is_array($layout)) {
            $layout = [];
        }
        if (!is_array($visibility)) {
            $visibility = [];
        }
        $ordered = [];
        foreach ($layout as $id) {
            if (isset($widgets[$id])) {
                $ordered[$id] = $widgets[$id];
                unset($widgets[$id]);
            }
        }
        $widgets = $ordered + $widgets;

        $vars = [
            'artist_form'         => $artist_form,
            'org_form'            => $org_form,
            'show_forms'          => $show_forms,
            'profile_edit_url'    => $profile_edit_url,
            'show_notifications'  => $show_notifications,
            'show_support_history'=> $show_support_history,
            'badges'              => $badges,
            'widgets'             => $widgets,
            'visibility'          => $visibility,
        ];

        $onboarding_html = '';
        $completed = get_user_meta(get_current_user_id(), 'ap_onboarding_completed', true);
        $tour_done = get_user_meta(get_current_user_id(), 'ap_dashboard_tour_complete', true);
        if (!$completed && !$tour_done) {
            wp_enqueue_script(
                'intro-js',
                'https://unpkg.com/intro.js/minified/intro.min.js',
                [],
                '4.2.2',
                true
            );
            wp_enqueue_style(
                'intro-js',
                'https://unpkg.com/intro.js/minified/introjs.min.css',
                [],
                '4.2.2'
            );
            wp_enqueue_script(
                'ap-dashboard-tour',
                plugins_url('assets/js/ap-dashboard-tour.js', ARTPULSE_PLUGIN_FILE),
                ['intro-js', 'ap-user-dashboard-js'],
                '1.0.0',
                true
            );
            wp_localize_script('ap-dashboard-tour', 'APDashboardTour', [
                'endpoint' => esc_url_raw(rest_url('artpulse/v1/dashboard-tour')),
                'nonce'    => wp_create_nonce('wp_rest'),
            ]);
        }
        if (!$completed && !$tour_done) {
            if (in_array('artist', $user->roles, true) && isset($_GET['onboarding'])) {
                $onboarding_html = self::load_template('onboarding-artist.php');
            } else {
                $onboarding_html = '<div id="ap-onboarding-banner" class="ap-onboarding-banner">'
                    . '<span>' . esc_html__('Get started with a quick tour.', 'artpulse') . '</span>'
                    . '<div><button id="ap-start-tour" class="ap-form-button">' . esc_html__('Start', 'artpulse') . '</button>'
                    . '<button id="ap-dismiss-tour" class="ap-form-button">' . esc_html__('Dismiss', 'artpulse') . '</button></div></div>';
            }
        }

        $user_role = $role;

        $widget_defs = array_map(
            static fn($id) => ['id' => $id, 'restOnly' => true],
            array_keys($widgets)
        );
        wp_localize_script(
            'art-widgets',
            'RoleDashboardData',
            [
                'widgets' => array_values($widget_defs),
                'currentUser' => [
                    'role'  => $user_role,
                    'roles' => $roles_list,
                ],
            ]
        );

        ob_start();
        \ap_render_dashboard([$user_role]);
        $dashboard_html = ob_get_clean();

        return $onboarding_html . $dashboard_html;
    }
}
