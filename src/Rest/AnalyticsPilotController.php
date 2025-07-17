<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Core\EmailService;

class AnalyticsPilotController
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/analytics/pilot/invite', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'invite'],
            'permission_callback' => fn() => current_user_can('manage_options'),
            'args'                => [
                'email' => [ 'type' => 'string', 'required' => true ],
            ],
        ]);
    }

    public static function invite(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $email = sanitize_email($request['email']);
        if (!is_email($email)) {
            return new WP_Error('invalid_email', __('Invalid email', 'artpulse'), ['status' => 400]);
        }
        $user = get_user_by('email', $email);
        if (!$user) {
            $user_id = wp_create_user($email, wp_generate_password(), $email);
            if (is_wp_error($user_id)) {
                return new WP_Error('cannot_create', __('User creation failed', 'artpulse'), ['status' => 500]);
            }
            $user = get_user_by('id', $user_id);
        }
        if (!$user) {
            return new WP_Error('user_error', __('User lookup failed', 'artpulse'), ['status' => 500]);
        }
        $user->add_cap('ap_premium_member');
        EmailService::send($user->user_email, 'Analytics Pilot Access', 'You have been invited to the analytics pilot.');
        return rest_ensure_response(['invited' => $user->user_email]);
    }
}
