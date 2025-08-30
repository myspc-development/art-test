<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use ArtPulse\Rest\Util\Auth;
use ArtPulse\Core\ProfileMetrics;

final class ProfileMetricsController {
    public static function register(): void {
        add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
    }

    public static function register_routes(): void {
        $permission = function () {
            return Auth::require_cap( 'read' );
        };

        register_rest_route(
            ARTPULSE_API_NAMESPACE,
            '/profile/metrics',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( self::class, 'get_metrics' ),
                'permission_callback' => $permission,
                'args'                => array(
                    'metric' => array( 'type' => 'string', 'default' => 'view' ),
                    'days'   => array( 'type' => 'integer', 'default' => 30 ),
                    'id'     => array( 'type' => 'integer' ),
                ),
            )
        );

        // Legacy path for backward compatibility.
        register_rest_route(
            ARTPULSE_API_NAMESPACE,
            '/profile-metrics/(?P<id>\d+)',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( self::class, 'get_metrics' ),
                'permission_callback' => $permission,
                'args'                => array(
                    'metric' => array( 'type' => 'string', 'default' => 'view' ),
                    'days'   => array( 'type' => 'integer', 'default' => 30 ),
                ),
            )
        );
    }

    public static function get_metrics( WP_REST_Request $req ): WP_REST_Response {
        $metric = sanitize_key( $req->get_param( 'metric' ) );
        $days   = max( 1, absint( $req->get_param( 'days' ) ) );
        $uid    = absint( $req->get_param( 'id' ) );
        if ( ! $uid ) {
            $uid = get_current_user_id();
        }

        $data = ProfileMetrics::get_counts( $uid, $metric, $days );
        return rest_ensure_response( $data );
    }
}
