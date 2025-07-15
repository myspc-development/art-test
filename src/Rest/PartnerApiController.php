<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class PartnerApiController
{
    public static function register(): void
    {
        add_action('init', [self::class, 'maybe_install_table']);
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function maybe_install_table(): void
    {
        global $wpdb;
        $table  = $wpdb->prefix . 'ap_api_keys';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            self::install_table();
        }
    }

    public static function install_table(): void
    {
        global $wpdb;
        $table   = $wpdb->prefix . 'ap_api_keys';
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            org_id BIGINT NULL,
            api_key VARCHAR(64) NOT NULL,
            scopes VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL,
            usage INT NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            UNIQUE KEY api_key (api_key)
        ) $charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function register_routes(): void
    {
        register_rest_route('api/v1', '/events', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_events'],
            'permission_callback' => [self::class, 'auth_events'],
            'args'                => [ 'region' => [ 'type' => 'string', 'required' => false ] ],
        ]);
        register_rest_route('api/v1', '/analytics/events', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_event_stats'],
            'permission_callback' => [self::class, 'auth_stats'],
        ]);
        register_rest_route('api/v1', '/featured', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_featured'],
            'permission_callback' => [self::class, 'auth_featured'],
            'args'                => [ 'count' => [ 'type' => 'integer', 'default' => 5 ] ],
        ]);
    }

    private static function authenticate(WP_REST_Request $request, string $scope)
    {
        $header = $request->get_header('authorization');
        if (!$header || !str_starts_with(strtolower($header), 'bearer ')) {
            return new WP_Error('unauthorized', 'Missing token', ['status' => 401]);
        }
        $key = trim(substr($header, 7));
        global $wpdb;
        $table = $wpdb->prefix . 'ap_api_keys';
        $row   = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE api_key = %s", $key));
        if (!$row) {
            return new WP_Error('unauthorized', 'Invalid token', ['status' => 401]);
        }
        $scopes = array_map('trim', explode(',', $row->scopes));
        if (!in_array($scope, $scopes, true)) {
            return new WP_Error('unauthorized', 'Insufficient scope', ['status' => 403]);
        }
        $dayCount = (int) get_transient('ap_api_day_' . $key);
        if ($dayCount >= 1000) {
            return new WP_Error('rate_limit', 'Daily limit exceeded', ['status' => 429]);
        }
        $secCount = (int) get_transient('ap_api_sec_' . $key);
        if ($secCount >= 10) {
            return new WP_Error('rate_limit', 'Rate limit exceeded', ['status' => 429]);
        }
        set_transient('ap_api_day_' . $key, $dayCount + 1, DAY_IN_SECONDS);
        set_transient('ap_api_sec_' . $key, $secCount + 1, 1);
        return $row;
    }

    public static function auth_events(WP_REST_Request $request)
    {
        $auth = self::authenticate($request, 'read:events');
        return !is_wp_error($auth) ? true : $auth;
    }

    public static function auth_stats(WP_REST_Request $request)
    {
        $auth = self::authenticate($request, 'read:stats');
        return !is_wp_error($auth) ? true : $auth;
    }

    public static function auth_featured(WP_REST_Request $request)
    {
        $auth = self::authenticate($request, 'read:featured');
        return !is_wp_error($auth) ? true : $auth;
    }

    public static function get_events(WP_REST_Request $request): WP_REST_Response
    {
        $args = [
            'post_type'      => 'artpulse_event',
            'post_status'    => 'publish',
            'posts_per_page' => 20,
        ];
        if ($request['region']) {
            $args['meta_key'] = 'ap_region';
            $args['meta_value'] = sanitize_text_field($request['region']);
        }
        $posts = get_posts($args);
        $items = [];
        foreach ($posts as $post) {
            $items[] = [
                'id'    => $post->ID,
                'title' => $post->post_title,
                'link'  => get_permalink($post->ID),
            ];
        }
        return rest_ensure_response($items);
    }

    public static function get_event_stats(WP_REST_Request $request): WP_REST_Response
    {
        $event_id = (int) $request['event_id'];
        $views = \ArtPulse\Core\EventMetrics::get_counts($event_id, 'view', 30);
        return rest_ensure_response($views);
    }

    public static function get_featured(WP_REST_Request $request): WP_REST_Response
    {
        $count = max(1, min(10, (int) $request['count']));
        global $wpdb;
        $table = $wpdb->prefix . 'ap_event_rankings';
        $rows  = $wpdb->get_results($wpdb->prepare("SELECT event_id, score FROM $table ORDER BY score DESC LIMIT %d", $count));
        $items = [];
        foreach ($rows as $row) {
            $items[] = [
                'id'    => (int) $row->event_id,
                'score' => (float) $row->score,
                'title' => get_the_title($row->event_id),
                'link'  => get_permalink($row->event_id),
            ];
        }
        return rest_ensure_response($items);
    }
}
