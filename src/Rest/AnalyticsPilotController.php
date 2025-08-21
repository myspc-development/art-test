<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Rest\Util\Auth;

final class AnalyticsPilotController {
    public static function register(): void {
        add_action('rest_api_init', [self::class, 'routes']);
    }
    public static function routes(): void {
        register_rest_route('ap/v1', '/analytics/pilot/invite', [
            'methods'  => 'POST',
            'callback' => [self::class, 'invite'],
            'permission_callback' => Auth::require_login_and_cap('manage_options'),
            'args' => [
                'user_id' => ['type' => 'integer'],
                'id'      => ['type' => 'integer'],
                'user'    => ['type' => 'integer'],
            ],
        ]);
    }
    public static function invite(WP_REST_Request $req) {
        $user_id = (int) ($req->get_param('user_id') ?? $req->get_param('id') ?? $req->get_param('user') ?? 0);
        if ($user_id <= 0) {
            return new WP_REST_Response(['ok' => false, 'reason' => 'missing_user_id'], 200);
        }
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return new WP_REST_Response(['ok' => false, 'reason' => 'user_not_found'], 200);
        }
        $user->add_cap('ap_analytics_pilot');
        return new WP_REST_Response(['success' => true], 200);
    }
}
