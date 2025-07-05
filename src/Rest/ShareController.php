<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class ShareController
{
    public static function register(): void
    {
        register_rest_route('artpulse/v1', '/share', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'log_share'],
            'permission_callback' => '__return_true',
            'args'                => [
                'object_id' => [ 'type' => 'integer', 'required' => true ],
                'object_type' => [ 'type' => 'string', 'required' => true ],
                'network'    => [ 'type' => 'string', 'required' => false ],
            ],
        ]);
    }

    public static function log_share(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $id   = absint($request['object_id']);
        $type = sanitize_key($request['object_type']);
        $net  = sanitize_key($request->get_param('network'));

        if (!$id || !$type) {
            return new WP_Error('invalid_params', 'Invalid parameters.', ['status' => 400]);
        }

        if ($type === 'artpulse_event') {
            do_action('ap_event_shared', $id, $net);
        } elseif ($type === 'user') {
            do_action('ap_profile_shared', $id, $net);
        }

        return rest_ensure_response(['success' => true]);
    }
}
