<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;
use function ArtPulse\Rest\Util\require_login_and_cap;

class AnalyticsPilotController {
    public static function register(): void {
        $c = new self();
        add_action('rest_api_init', [$c, 'register_routes']);
    }

    public function register_routes(): void {
        register_rest_route(ARTPULSE_API_NAMESPACE, '/analytics/pilot/invite', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'invite'],
            'permission_callback' => require_login_and_cap(static fn() => current_user_can('manage_options')),
            'args'                => [
                'user_id' => ['type' => 'integer', 'required' => true],
            ],
        ]);
    }

    public function invite(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $user_id = (int) $request['user_id'];
        $user    = get_user_by('id', $user_id);
        if (!$user) {
            return new WP_Error('invalid_user', __('User not found.'), ['status' => 404]);
        }
        $user->add_cap('ap_analytics_pilot');
        return rest_ensure_response(['ok' => true]);
    }
}
