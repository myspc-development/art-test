<?php
namespace ArtPulse\Core;

use WP_REST_Request;
use ArtPulse\Community\FavoritesManager;

class UserDashboardManager
{
    public static function register()
    {
        add_shortcode('ap_user_dashboard', [ self::class, 'renderDashboard' ]);
        add_action('wp_enqueue_scripts',   [ self::class, 'enqueueAssets' ]);
        add_action('rest_api_init',        [ self::class, 'register_routes' ]);
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
            'ap-user-dashboard-js',
            plugins_url('assets/js/ap-user-dashboard.js', ARTPULSE_PLUGIN_FILE),
            ['wp-api-fetch', 'chart-js'],
            '1.0.0',
            true
        );

        // Analytics events
        wp_enqueue_script(
            'ap-analytics-js',
            plugins_url('assets/js/ap-analytics.js', ARTPULSE_PLUGIN_FILE),
            ['ap-user-dashboard-js'],
            '1.0.0',
            true
        );

        // Localize dashboard REST endpoint
        wp_localize_script('ap-user-dashboard-js', 'ArtPulseDashboardApi', [
            'root'             => esc_url_raw(rest_url()),
            'nonce'            => wp_create_nonce('wp_rest'),
            'orgSubmissionUrl' => self::get_org_submission_url(),
            'artistSubmissionUrl' => self::get_artist_submission_url(),
            'artistEndpoint'   => esc_url_raw(rest_url('artpulse/v1/artist-upgrade')),
            'exportEndpoint'   => esc_url_raw(rest_url('artpulse/v1/user/export')),
            'deleteEndpoint'   => esc_url_raw(rest_url('artpulse/v1/user/delete')),
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
            '1.0.0',
            true
        );
        wp_localize_script('ap-profile-modal', 'APProfileModal', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('ap_profile_edit_action'),
        ]);
    }

    public static function registerRestRoutes()
    {
        register_rest_route('artpulse/v1', '/user/dashboard', [
            'methods'             => 'GET',
            'callback'            => [ self::class, 'getDashboardData' ],
            'permission_callback' => function() {
                return is_user_logged_in() && current_user_can('view_artpulse_dashboard');
            },
        ]);

        register_rest_route('artpulse/v1', '/user/profile', [
            'methods'             => 'POST',
            'callback'            => [ self::class, 'updateProfile' ],
            'permission_callback' => function() {
                return is_user_logged_in();
            },
        ]);
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
        foreach ( $favorite_events as $fav ) {
            $post = get_post($fav->object_id);
            if ( ! $post ) {
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

        $data['favorite_count'] = count($data['favorite_events']);
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
        $next_payment = get_user_meta($user_id, 'ap_membership_expires', true);
        if (!$next_payment && class_exists('Stripe\Stripe')) {
            // Placeholder: if using Stripe subscriptions you would fetch here
            $next_payment = null;
        }
        $data['next_payment'] = $next_payment;

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
        return rest_ensure_response([ 'success' => true ]);
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
        if ( ! is_user_logged_in() || ! current_user_can('view_artpulse_dashboard') ) {
            return '<p>' . __('Please log in to view your dashboard.', 'artpulse') . '</p>';
        }

        $atts = shortcode_atts([
            'show_forms' => false,
        ], $atts, 'ap_user_dashboard');

        $show_forms = filter_var($atts['show_forms'], FILTER_VALIDATE_BOOLEAN);

        $artist_form = $show_forms ? do_shortcode('[ap_submit_artist]') : '';
        $org_form    = $show_forms ? do_shortcode('[ap_submit_organization]') : '';

        $user             = wp_get_current_user();
        $roles            = (array) $user->roles;
        $profile_edit_url = self::get_profile_edit_url();
        $show_notifications = !empty(array_intersect(['member','artist','organization','administrator'], $roles));
        $support_history   = get_user_meta(get_current_user_id(), 'ap_support_history', true);
        $show_support_history = is_array($support_history) && !empty($support_history);
        $badges = self::getBadges(get_current_user_id());

        $vars = [
            'artist_form'         => $artist_form,
            'org_form'            => $org_form,
            'show_forms'          => $show_forms,
            'profile_edit_url'    => $profile_edit_url,
            'show_notifications'  => $show_notifications,
            'show_support_history'=> $show_support_history,
            'badges'              => $badges,
        ];

        if (in_array('organization', $roles, true) || in_array('administrator', $roles, true)) {
            return self::load_template('dashboard-organization.php', $vars);
        }

        if (in_array('artist', $roles, true)) {
            return self::load_template('dashboard-artist.php', $vars);
        }

        return self::load_template('dashboard-member.php', $vars);
    }
}
