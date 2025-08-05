<?php
namespace ArtPulse\Rest;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

class EventManagementController extends WP_REST_Controller
{
    /**
     * REST API namespace used by this controller.
     *
     * @var string
     */
    protected $namespace = 'artpulse/v1';

    public static function register(): void
    {
        $controller = new self();
        add_action('rest_api_init', [$controller, 'register_routes']);
    }

    public function register_routes(): void
    {
        register_rest_route($this->namespace, '/event/(?P<id>\d+)/dates', [
            'methods'  => WP_REST_Server::EDITABLE,
            'callback' => [$this, 'update_dates'],
            'permission_callback' => [$this, 'permissions'],
            'args'     => [
                'start' => ['required' => true],
                'end'   => ['required' => false],
            ],
        ]);
    }

    public function permissions(WP_REST_Request $request): bool
    {
        $id = intval($request['id']);
        return current_user_can('edit_post', $id);
    }

    public function update_dates(WP_REST_Request $request): WP_REST_Response
    {
        $id = intval($request['id']);
        $start = sanitize_text_field($request->get_param('start'));
        $end = sanitize_text_field($request->get_param('end'));
        update_post_meta($id, '_ap_event_date', $start);
        if ($end) {
            update_post_meta($id, 'event_end_date', $end);
        }
        return rest_ensure_response(['success' => true]);
    }
}
