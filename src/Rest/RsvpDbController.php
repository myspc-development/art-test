<?php
namespace ArtPulse\Rest;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Simple RSVP management backed by the ap_rsvps table.
 */
class RsvpDbController extends WP_REST_Controller
{
    protected $namespace = ARTPULSE_API_NAMESPACE;

    public static function register(): void
    {
        $controller = new self();
        add_action('rest_api_init', [$controller, 'register_routes']);
    }

    public function register_routes(): void
    {
        register_rest_route($this->namespace, '/rsvps', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'create_rsvp'],
                'permission_callback' => [$this, 'can_submit'],
                'args'                => [
                    'event_id' => ['type' => 'integer', 'required' => true],
                    'name'     => ['type' => 'string',  'required' => true],
                    'email'    => ['type' => 'string',  'required' => true],
                ],
            ],
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'list_rsvps'],
                'permission_callback' => [$this, 'can_manage'],
                'args'                => [
                    'event_id' => ['type' => 'integer', 'required' => true],
                ],
            ],
        ]);

        register_rest_route($this->namespace, '/rsvps/(?P<id>\d+)', [
            'methods'             => WP_REST_Server::EDITABLE,
            'callback'            => [$this, 'update_rsvp'],
            'permission_callback' => [$this, 'can_manage'],
            'args'                => [
                'status' => ['type' => 'string', 'required' => true],
            ],
        ]);

        register_rest_route($this->namespace, '/rsvps/export.csv', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'export_csv'],
            'permission_callback' => [$this, 'can_manage'],
            'args'                => [
                'event_id' => ['type' => 'integer', 'required' => true],
            ],
        ]);
    }

    protected function table(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'ap_rsvps';
    }

    public function can_submit(WP_REST_Request $request): bool
    {
        return is_user_logged_in() || !empty($request['email']);
    }

    public function can_manage(WP_REST_Request $request): bool
    {
        $event_id = intval($request['event_id'] ?? $request['id']);
        return $event_id && current_user_can('edit_post', $event_id);
    }

    public function create_rsvp(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $event_id = intval($request['event_id']);
        if (!$event_id || get_post_type($event_id) !== 'artpulse_event') {
            return new WP_Error('invalid_event', 'Invalid event.', ['status' => 400]);
        }

        global $wpdb;
        $data = [
            'event_id' => $event_id,
            'user_id'  => get_current_user_id() ?: null,
            'name'     => sanitize_text_field($request['name']),
            'email'    => sanitize_email($request['email']),
            'status'   => 'going',
        ];
        $wpdb->insert($this->table(), $data, ['%d','%d','%s','%s','%s']);
        $data['id'] = $wpdb->insert_id;
        $data['created_at'] = current_time('mysql');
        return rest_ensure_response($data);
    }

    public function list_rsvps(WP_REST_Request $request): WP_REST_Response
    {
        global $wpdb;
        $event_id = intval($request['event_id']);
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, event_id, user_id, name, email, status, created_at FROM {$this->table()} WHERE event_id = %d",
                $event_id
            ),
            ARRAY_A
        );
        return rest_ensure_response($rows);
    }

    public function update_rsvp(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $id     = intval($request['id']);
        $status = sanitize_text_field($request['status']);
        $allowed = ['going', 'waitlist', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            return new WP_Error('invalid_status', 'Invalid status.', ['status' => 400]);
        }
        global $wpdb;
        $wpdb->update($this->table(), ['status' => $status], ['id' => $id], ['%s'], ['%d']);
        return rest_ensure_response(['id' => $id, 'status' => $status]);
    }

    public function export_csv(WP_REST_Request $request)
    {
        $data = $this->list_rsvps($request)->get_data();
        $out  = "id,event_id,user_id,name,email,status,created_at\n";
        foreach ($data as $row) {
            $out .= sprintf(
                "%d,%d,%s,%s,%s,%s,%s\n",
                $row['id'],
                $row['event_id'],
                $row['user_id'] ?: '',
                $row['name'],
                $row['email'],
                $row['status'],
                $row['created_at']
            );
        }
        return new WP_REST_Response($out, 200, ['Content-Type' => 'text/csv']);
    }
}
