<?php
namespace ArtPulse\Rest;

use WP_REST_Server;
use ArtPulse\Rest\Util\Auth;

final class RestRoutes {
    public static function boot(): void {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void {
        // RSVP
        register_rest_route('ap/v1', '/rsvps', [
            'methods'  => WP_REST_Server::READABLE,
            'callback' => [RsvpDbController::class, 'list'],
            'permission_callback' => Auth::require_login_and_cap(fn()=> current_user_can('read')),
        ]);
        register_rest_route('ap/v1', '/rsvps/bulk-update', [
            'methods'  => WP_REST_Server::CREATABLE,
            'callback' => [RsvpDbController::class, 'bulk_update'],
            'permission_callback' => Auth::require_login_and_cap(fn()=> current_user_can('edit_posts')),
        ]);
        register_rest_route('ap/v1', '/rsvps/export.csv', [
            'methods'  => WP_REST_Server::READABLE,
            'callback' => [RsvpDbController::class, 'export_csv'],
            'permission_callback' => Auth::require_login_and_cap(fn()=> current_user_can('read')),
            'args' => ['event_id'=>['required'=>true,'type'=>'integer']],
        ]);

        // Analytics – must exist and return 200 for valid ranges
        register_rest_route('ap/v1', '/analytics/events/summary', [
            'methods'  => WP_REST_Server::READABLE,
            'callback' => [EventAnalyticsController::class, 'summary'],
            'permission_callback' => Auth::require_login_and_cap(fn()=> current_user_can('read')),
        ]);

        // Events (public GET by coords)
        register_rest_route('ap/v1', '/events', [
            'methods'  => WP_REST_Server::READABLE,
            'callback' => [CalendarFeedController::class, 'nearby'],
            'permission_callback' => '__return_true', // tests expect 200 unauth
        ]);

        // Dashboard layout + alias
        register_rest_route('ap/v1', '/dashboard/layout', [
            'methods'  => WP_REST_Server::READABLE,
            'callback' => [DashboardLayoutController::class, 'get'],
            'permission_callback' => Auth::require_login_and_cap(fn()=> current_user_can('read')),
        ]);
        register_rest_route('ap/v1', '/dashboard/layout', [
            'methods'  => WP_REST_Server::EDITABLE,
            'callback' => [DashboardLayoutController::class, 'save'],
            'permission_callback' => Auth::require_login_and_cap(fn()=> current_user_can('read')),
        ]);
        // Alias routes
        register_rest_route('ap/v1', '/dashboard/widgets', [
            'methods'  => WP_REST_Server::READABLE,
            'callback' => [DashboardLayoutController::class, 'get'],
            'permission_callback' => Auth::require_login_and_cap(fn()=> current_user_can('read')),
        ]);
        register_rest_route('ap/v1', '/dashboard/widgets', [
            'methods'  => WP_REST_Server::EDITABLE,
            'callback' => [DashboardLayoutController::class, 'save'],
            'permission_callback' => Auth::require_login_and_cap(fn()=> current_user_can('read')),
        ]);

        // System status (tests expect it)
        register_rest_route('ap/v1', '/system/status', [
            'methods'  => WP_REST_Server::READABLE,
            'callback' => [SystemStatusController::class, 'get'],
            'permission_callback' => '__return_true',
        ]);

        // Route audit (dev/test)
        if (defined('WP_DEBUG') && WP_DEBUG || defined('ARTPULSE_TEST_MODE')) {
            register_rest_route('ap/v1', '/_routes-audit', [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [RouteAudit::class, 'rest'],
                'permission_callback' => '__return_true',
            ]);
        }

        // Pilot invite must exist and be manage_options-gated
        register_rest_route('ap/v1', '/analytics/pilot/invite', [
            'methods'  => WP_REST_Server::CREATABLE,
            'callback' => [AnalyticsPilotController::class, 'invite'],
            'permission_callback' => Auth::require_login_and_cap(fn()=> current_user_can('manage_options')),
        ]);
    }
}
