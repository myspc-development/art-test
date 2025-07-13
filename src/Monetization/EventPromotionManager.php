<?php
namespace ArtPulse\Monetization;

use WP_REST_Request;

/**
 * Simple event promotion actions.
 */
class EventPromotionManager
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/event/(?P<id>\d+)/feature', [
            'methods'  => 'POST',
            'callback' => [self::class, 'feature_event'],
            'permission_callback' => [self::class, 'can_edit'],
            'args' => ['id' => ['validate_callback' => 'absint']],
        ]);
    }

    public static function can_edit(WP_REST_Request $req): bool
    {
        $id = absint($req->get_param('id'));
        return current_user_can('edit_post', $id);
    }

    public static function feature_event(WP_REST_Request $req)
    {
        $id = absint($req->get_param('id'));
        if (!$id) {
            return new \WP_Error('invalid_event', 'Invalid event.', ['status' => 400]);
        }
        update_post_meta($id, 'ap_featured', 1);
        return rest_ensure_response(['featured' => true]);
    }
}
