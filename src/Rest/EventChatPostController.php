<?php
namespace ArtPulse\Rest;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class EventChatPostController extends WP_REST_Controller
{
    protected $namespace = 'artpulse/v1';

    public static function register(): void
    {
        $controller = new self();
        add_action('rest_api_init', [$controller, 'register_routes']);
    }

    public function register_routes(): void
    {
        register_rest_route($this->namespace, '/event/(?P<id>\d+)/chat', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'create_item'],
            'permission_callback' => [$this, 'permissions'],
            'args'                => [
                'id'      => ['validate_callback' => 'is_numeric'],
                'content' => ['type' => 'string', 'required' => true],
            ],
        ]);

        register_rest_route($this->namespace, '/chat/(?P<id>\d+)/reaction', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'add_reaction'],
            'permission_callback' => [$this, 'permissions'],
            'args'                => [
                'id'   => ['validate_callback' => 'is_numeric'],
                'emoji'=> ['type' => 'string', 'required' => true],
            ],
        ]);

        register_rest_route($this->namespace, '/chat/(?P<id>\d+)', [
            'methods'             => WP_REST_Server::DELETABLE,
            'callback'            => [$this, 'delete_item'],
            'permission_callback' => [$this, 'moderator_check'],
            'args'                => [ 'id' => ['validate_callback' => 'is_numeric'] ],
        ]);

        register_rest_route($this->namespace, '/chat/(?P<id>\d+)/flag', [
            'methods'             => WP_REST_Server::EDITABLE,
            'callback'            => [$this, 'flag_item'],
            'permission_callback' => [$this, 'moderator_check'],
            'args'                => [ 'id' => ['validate_callback' => 'is_numeric'] ],
        ]);
    }

    public function permissions(WP_REST_Request $request)
    {
        return is_user_logged_in();
    }

    public function moderator_check(WP_REST_Request $request)
    {
        return current_user_can('moderate_comments');
    }

    /** @param WP_REST_Request $request */
    public function create_item($request)
    {
        $event_id = absint($request['id']);
        if (!$event_id || get_post_type($event_id) !== 'artpulse_event') {
            return new WP_Error('invalid_event', 'Invalid event.', ['status' => 404]);
        }
        $content = sanitize_text_field($request['content']);
        if ($content === '') {
            return new WP_Error('empty_content', 'Message content required.', ['status' => 400]);
        }
        $user_id = get_current_user_id();
        \ArtPulse\DB\Chat\maybe_install_tables();
        $msg = \ArtPulse\DB\Chat\insert_message($event_id, $user_id, $content);
        return rest_ensure_response($msg);
    }

    public function add_reaction(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $msg_id = absint($request['id']);
        $emoji  = sanitize_text_field($request['emoji']);
        if (!$msg_id || $emoji === '') {
            return new WP_Error('invalid_request', 'Invalid reaction.', ['status' => 400]);
        }
        \ArtPulse\DB\Chat\add_reaction($msg_id, get_current_user_id(), $emoji);
        return rest_ensure_response(['status' => 'ok']);
    }

    /** @param WP_REST_Request $request */
    public function delete_item($request)
    {
        $id = absint($request['id']);
        \ArtPulse\DB\Chat\delete_message($id);
        return rest_ensure_response(['status' => 'deleted']);
    }

    public function flag_item(WP_REST_Request $request): WP_REST_Response
    {
        $id = absint($request['id']);
        \ArtPulse\DB\Chat\flag_message($id);
        return rest_ensure_response(['status' => 'flagged']);
    }
}
