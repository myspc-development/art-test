<?php
namespace ArtPulse\Rest;

use WP_REST_Response;
use WP_Error;
use ArtPulse\Rest\Util\Auth;
use ArtPulse\Rest\RestResponder;

class OrgRolesController {
        use RestResponder;

        public static function register_routes(): void {
                register_rest_route(
                        ARTPULSE_API_NAMESPACE,
                        '/org-roles',
                        array(
                                'methods'             => 'GET',
                                'callback'            => array( self::class, 'get_roles' ),
                                'permission_callback' => array( Auth::class, 'guard_read' ),
                        )
                );
        }

        public static function get_roles(): WP_REST_Response|WP_Error {
                return \rest_ensure_response(
                        array(
                                'roles' => array( 'owner', 'manager', 'editor', 'viewer' ),
                        )
                );
        }
}
