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
                'email'   => ['type' => 'string'],
            ],
        ]);
    }
    public static function invite(WP_REST_Request $req) {
        if (!wp_verify_nonce($req->get_header('X-WP-Nonce'), 'wp_rest')) {
            return new WP_Error('invalid_nonce', 'Invalid nonce', ['status' => 401]);
        }

        $user = null;
        $user_id = absint($req->get_param('user_id'));
        if ($user_id) {
            $user = get_user_by('id', $user_id);
        }
        if (!$user) {
            $email = sanitize_email($req->get_param('email'));
            if ($email) {
                $user = get_user_by('email', $email);
            }
        }
        if (!$user) {
            return new WP_Error('user_not_found', 'User not found', ['status' => 404]);
        }
        $user->add_cap('ap_analytics_pilot');
        return new WP_REST_Response(['granted' => true], 200);
    }
}
