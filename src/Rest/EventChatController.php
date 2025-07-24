<?php
namespace ArtPulse\Rest;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class EventChatController extends WP_REST_Controller
{
    protected $namespace = 'artpulse/v1';

    public static function register(): void
    {
        $controller = new self();
        add_action('rest_api_init', [$controller, 'register_routes']);
    }

    public function register_routes(): void
    {
        register_rest_route($this->namespace, '/event/(?P<id>\\d+)/chat', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'get_messages'],
            'permission_callback' => [$this, 'permissions'],
            'args'                => [ 'id' => [ 'validate_callback' => 'is_numeric' ] ],
        ]);
    }

    public function permissions(WP_REST_Request $request)
    {
        $nonce = $request->get_header('X-WP-Nonce');
        if (!is_user_logged_in() || !current_user_can('read') || !wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_Error('rest_forbidden', __('Unauthorized.', 'artpulse'), ['status' => 401]);
        }
        return true;
    }

    public function get_messages(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $event_id = absint($request['id']);
        if (!$event_id || get_post_type($event_id) !== 'artpulse_event') {
            return new WP_Error('invalid_event', 'Invalid event.', ['status' => 404]);
        }

        \ArtPulse\DB\Chat\maybe_install_tables();
        $rows = \ArtPulse\DB\Chat\get_messages($event_id);

        $messages = array_map(static function ($row) {
            $user   = get_userdata((int) $row['user_id']);
            $avatar = get_avatar_url((int) $row['user_id'], ['size' => 48]);
            if ($avatar) {
                $avatar = set_url_scheme($avatar, 'https');
            }
            return [
                'id'         => (int) $row['id'],
                'user_id'    => (int) $row['user_id'],
                'author'     => $user ? $user->display_name : '',
                'avatar'     => $avatar ?: '',
                'content'    => $row['content'],
                'created_at' => $row['created_at'],
                'flagged'    => (int) $row['flagged'],
                'reactions'  => $row['reactions'],
            ];
        }, $rows);

        return rest_ensure_response($messages);
    }
}
