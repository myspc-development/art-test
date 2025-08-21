<?php
namespace ArtPulse\Rest;

use function ArtPulse\Rest\Util\require_login_and_cap; // maybe not used but for completeness

class RestRoutes {
    /**
     * Boot the REST routes by hooking into rest_api_init.
     */
    public static function boot(): void {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    /**
     * Legacy compatibility.
     */
    public static function register(): void {
        self::boot();
    }

    /**
     * Register all core routes needed for tests.
     */
    public static function register_routes(): void {
        if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/orgs')) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/orgs', [
                'methods'             => 'GET',
                'callback'            => [self::class, 'get_orgs'],
                'permission_callback' => require_login_and_cap(static fn() => current_user_can('read')),
            ]);
        }

        // Controllers that need to be available during tests.
        (new RsvpDbController())->register_routes();
        (new EventAnalyticsController())->register_routes();
        (new PortfolioRestController())->register_routes();
        CalendarFeedController::register_routes();
        (new DashboardLayoutController())->register_routes();

        // Optional debug route to audit routes for conflicts.
        if (defined('ARTPULSE_TEST_MODE') || (defined('WP_DEBUG') && WP_DEBUG)) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/_routes-audit', [
                'methods'  => 'GET',
                'callback' => [RouteAudit::class, 'analyze'],
                'permission_callback' => '__return_true',
            ]);
        }
    }

    public static function get_orgs() {
        return self::get_posts_with_meta('artpulse_org', [
            'address' => 'ead_org_street_address',
            'website' => 'ead_org_website_url',
        ]);
    }

    private static function get_posts_with_meta($post_type, $meta_keys = [], array $query_args = []) {
        $transient_key = 'ap_rest_posts_' . $post_type;
        $use_cache     = empty($query_args);

        if ($use_cache) {
            $cached = get_transient($transient_key);
            if (false !== $cached) {
                return $cached;
            }
        }

        $posts  = get_posts(array_merge([
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ], $query_args));

        $output = [];
        foreach ($posts as $post_id) {
            $item = [
                'id'      => $post_id,
                'title'   => get_the_title($post_id),
                'content' => apply_filters('the_content', get_post_field('post_content', $post_id)),
                'link'    => get_permalink($post_id),
            ];
            foreach ($meta_keys as $field => $meta_key) {
                $item[$field] = get_post_meta($post_id, $meta_key, true);
            }
            $output[] = $item;
        }

        if ($use_cache) {
            set_transient($transient_key, $output, HOUR_IN_SECONDS);
        }

        return $output;
    }
}
