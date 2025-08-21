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
                    'status'   => ['type' => 'string', 'required' => false],
                    'from'     => ['type' => 'string'],
                    'to'       => ['type' => 'string'],
                    'page'     => ['type' => 'integer', 'default' => 1],
                    'per_page' => ['type' => 'integer', 'default' => 25],
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

        register_rest_route($this->namespace, '/rsvps/bulk-update', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'bulk_update'],
            'permission_callback' => [$this, 'can_manage'],
            'args'                => [
                'event_id' => ['type' => 'integer', 'required' => true],
                'ids'      => ['type' => 'array',   'required' => true],
                'status'   => ['type' => 'string',  'required' => true],
            ],
        ]);
    }

    protected function table(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'ap_rsvps';
    }

    public function can_submit(WP_REST_Request $request): bool|WP_Error
    {
        if ( ! is_user_logged_in() && empty( $request['email'] ) ) {
            return new WP_Error( 'rest_not_logged_in', __( 'You are not currently logged in.', 'artpulse' ), [ 'status' => 401 ] );
        }
        return true;
    }

    public function can_manage(WP_REST_Request $request): bool|WP_Error
    {
        $event_id = intval( $request['event_id'] ?? $request['id'] );
        if ( ! is_user_logged_in() ) {
            return new WP_Error( 'rest_not_logged_in', __( 'You are not currently logged in.', 'artpulse' ), [ 'status' => 401 ] );
        }
        if ( ! $event_id || ! current_user_can( 'edit_post', $event_id ) ) {
            return new WP_Error( 'rest_forbidden', __( "You don't have permission to modify this resource.", 'artpulse' ), [ 'status' => 403 ] );
        }
        return true;
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
        do_action('ap_rsvp_changed', $event_id);
        return rest_ensure_response($data);
    }

    public function list_rsvps(WP_REST_Request $request): WP_REST_Response
    {
        global $wpdb;
        $event_id = intval($request['event_id']);
        $status   = sanitize_text_field($request['status']);
        $from     = $request['from'] ? sanitize_text_field($request['from']) : null;
        $to       = $request['to'] ? sanitize_text_field($request['to']) : null;
        $page     = max(1, intval($request['page']));
        $per_page = max(1, min(100, intval($request['per_page'])));

        $where  = $wpdb->prepare('event_id = %d', $event_id);
        $args   = [];
        if ($status) {
            $where .= $wpdb->prepare(' AND status = %s', $status);
        }
        if ($from) {
            $where .= $wpdb->prepare(' AND DATE(created_at) >= %s', $from);
        }
        if ($to) {
            $where .= $wpdb->prepare(' AND DATE(created_at) <= %s', $to);
        }

        $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table()} WHERE $where");
        $offset = ($page - 1) * $per_page;

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, name, email, status, created_at FROM {$this->table()} WHERE $where ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $per_page,
                $offset
            ),
            ARRAY_A
        );

        return rest_ensure_response([
            'total'    => $total,
            'page'     => $page,
            'per_page' => $per_page,
            'rows'     => $rows,
        ]);
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
        $event_id = (int) $wpdb->get_var($wpdb->prepare("SELECT event_id FROM {$this->table()} WHERE id = %d", $id));
        do_action('ap_rsvp_changed', $event_id);
        return rest_ensure_response(['id' => $id, 'status' => $status]);
    }

    public function export_csv(WP_REST_Request $request)
    {
        $request->set_param('page', 1);
        $request->set_param('per_page', 10000);
        $payload = $this->list_rsvps($request)->get_data();
        $rows    = $payload['rows'] ?? [];
        $out  = "id,name,email,status,created_at\n";
        foreach ($rows as $row) {
            $cells = [];
            foreach (['id','name','email','status','created_at'] as $col) {
                $val = (string) ($row[$col] ?? '');
                if (preg_match('/^[=+\-@]/', $val)) {
                    $val = "'" . $val;
                }
                $cells[] = $val;
            }
            $out .= implode(',', $cells) . "\n";
        }
        $filename = 'rsvps-' . intval($request['event_id']) . '-' . date('Ymd') . '.csv';
        return new WP_REST_Response($out, 200, [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function bulk_update(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $event_id = intval($request['event_id']);
        $ids      = array_map('intval', (array) $request['ids']);
        $status   = sanitize_text_field($request['status']);
        $allowed  = ['going', 'waitlist', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            return new WP_Error('invalid_status', 'Invalid status.', ['status' => 400]);
        }
        if (!$ids) {
            return new WP_Error('invalid_ids', 'No IDs supplied.', ['status' => 400]);
        }
        global $wpdb;
        $in = implode(',', array_fill(0, count($ids), '%d'));
        $wpdb->query($wpdb->prepare("UPDATE {$this->table()} SET status = %s WHERE event_id = %d AND id IN ($in)", array_merge([$status, $event_id], $ids)));
        do_action('ap_rsvp_changed', $event_id);
        return rest_ensure_response(['updated' => count($ids)]);
    }
}
