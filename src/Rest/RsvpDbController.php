<?php
namespace ArtPulse\Rest;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use ArtPulse\Rest\Util\Auth;
use ArtPulse\Rest\RestResponder;

/**
 * Simple RSVP management backed by the ap_rsvps table.
 */
class RsvpDbController extends WP_REST_Controller {
	use RestResponder;

	protected $namespace = ARTPULSE_API_NAMESPACE;

	public static function register(): void {
		$controller = new self();
		add_action( 'rest_api_init', array( $controller, 'register_routes' ) );
	}

	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/rsvps',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_rsvp' ),
				'permission_callback' => Auth::require_login_and_cap( 'read' ),
				'args'                => array(
					'event_id' => array(
						'type'     => 'integer',
						'required' => true,
					),
					'name'     => array(
						'type'     => 'string',
						'required' => true,
					),
					'email'    => array(
						'type'     => 'string',
						'required' => true,
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/rsvps',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'list_rsvps' ),
				'permission_callback' => Auth::require_login_and_cap( 'read' ),
				'args'                => array(
					'event_id' => array(
						'type'     => 'integer',
						'required' => true,
					),
					'status'   => array(
						'type'     => 'string',
						'required' => false,
					),
					'from'     => array( 'type' => 'string' ),
					'to'       => array( 'type' => 'string' ),
					'page'     => array(
						'type'    => 'integer',
						'default' => 1,
					),
					'per_page' => array(
						'type'    => 'integer',
						'default' => 25,
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/rsvps/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_rsvp' ),
				'permission_callback' => Auth::require_login_and_cap( 'read' ),
				'args'                => array(
					'status' => array(
						'type'     => 'string',
						'required' => true,
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/rsvps/export.csv',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'export_csv' ),
				'permission_callback' => Auth::require_login_and_cap( 'read' ),
				'args'                => array(
					'event_id' => array(
						'type'     => 'integer',
						'required' => true,
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/rsvps/bulk',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'bulk_update' ),
				'permission_callback' => Auth::require_login_and_cap( 'read' ),
				'args'                => array(
					'event_id' => array(
						'type'     => 'integer',
						'required' => true,
					),
					'ids'      => array(
						'type'     => 'array',
						'required' => true,
					),
					'status'   => array(
						'type'     => 'string',
						'required' => true,
					),
				),
			)
		);
	}

        protected function table(): string {
                global $wpdb;
                return $wpdb->prefix . 'ap_rsvps';
        }

        private function ensure_table(): ?\WP_Error {
                global $wpdb;
                $table  = $this->table();
                $exists = (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
                return $exists ? null : $this->fail( 'ap_db_missing', 'Required table missing', 500 );
        }

	protected function csv_safe( string $value ): string {
		if ( preg_match( '/^[=+\-@]/', $value ) ) {
			return "'" . $value;
		}
		return $value;
	}

        public function create_rsvp( WP_REST_Request $request ): WP_REST_Response|WP_Error {
                $event_id = intval( $request['event_id'] );
                if ( ! $event_id || get_post_type( $event_id ) !== 'artpulse_event' ) {
                        return new WP_Error( 'invalid_event', 'Invalid event.', array( 'status' => 400 ) );
                }
                if ( $err = $this->ensure_table() ) {
                        return $err;
                }
                global $wpdb;
                $data = array(
                        'event_id' => $event_id,
                        'user_id'  => get_current_user_id() ?: null,
                        'name'     => sanitize_text_field( $request['name'] ),
                        'email'    => sanitize_email( $request['email'] ),
			'status'   => 'going',
		);
		$wpdb->insert( $this->table(), $data, array( '%d', '%d', '%s', '%s', '%s' ) );
		$data['id']         = $wpdb->insert_id;
		$data['created_at'] = current_time( 'mysql' );
		do_action( 'ap_rsvp_changed', $event_id );
		return \rest_ensure_response( $data );
	}

        public function list_rsvps( WP_REST_Request $request ): WP_REST_Response|WP_Error {
                if ( $err = $this->ensure_table() ) {
                        return $err;
                }
                global $wpdb;
                $event_id = intval( $request['event_id'] );
		$status   = sanitize_text_field( $request['status'] );
		$from     = $request['from'] ? sanitize_text_field( $request['from'] ) : null;
		$to       = $request['to'] ? sanitize_text_field( $request['to'] ) : null;
		$page     = max( 1, intval( $request['page'] ) );
		$per_page = max( 1, min( 100, intval( $request['per_page'] ) ) );

		$where = $wpdb->prepare( 'event_id = %d', $event_id );
		$args  = array();
		if ( $status ) {
			$where .= $wpdb->prepare( ' AND status = %s', $status );
		}
		if ( $from ) {
			$where .= $wpdb->prepare( ' AND DATE(created_at) >= %s', $from );
		}
		if ( $to ) {
			$where .= $wpdb->prepare( ' AND DATE(created_at) <= %s', $to );
		}

		$total  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table()} WHERE $where" );
		$offset = ( $page - 1 ) * $per_page;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, name, email, status, created_at FROM {$this->table()} WHERE $where ORDER BY created_at DESC LIMIT %d OFFSET %d",
				$per_page,
				$offset
			),
			ARRAY_A
		);

		return \rest_ensure_response(
			array(
				'total'    => $total,
				'page'     => $page,
				'per_page' => $per_page,
				'rows'     => $rows,
			)
		);
	}

        public function update_rsvp( WP_REST_Request $request ): WP_REST_Response|WP_Error {
                $id      = intval( $request['id'] );
                $status  = sanitize_text_field( $request['status'] );
                $allowed = array( 'going', 'waitlist', 'cancelled' );
                if ( ! in_array( $status, $allowed, true ) ) {
                        return new WP_Error( 'invalid_status', 'Invalid status.', array( 'status' => 400 ) );
                }
                if ( $err = $this->ensure_table() ) {
                        return $err;
                }
                global $wpdb;
                $wpdb->update( $this->table(), array( 'status' => $status ), array( 'id' => $id ), array( '%s' ), array( '%d' ) );
		$event_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT event_id FROM {$this->table()} WHERE id = %d", $id ) );
		do_action( 'ap_rsvp_changed', $event_id );
		return \rest_ensure_response(
			array(
				'id'     => $id,
				'status' => $status,
			)
		);
	}

        public function export_csv( WP_REST_Request $request ) {
                $request->set_param( 'page', 1 );
                $request->set_param( 'per_page', 10000 );
                $result = $this->list_rsvps( $request );
                if ( $result instanceof \WP_Error ) {
                        return $result;
                }
                $payload = $result->get_data();
                $rows    = $payload['rows'] ?? array();
		$cols    = array( 'id', 'name', 'email', 'status', 'created_at' );
		$header  = implode( ',', array_map( array( $this, 'csv_safe' ), $cols ) ) . "\n";
		$out     = $header;
		foreach ( $rows as $row ) {
			$cells = array();
			foreach ( $cols as $col ) {
				$cells[] = $this->csv_safe( (string) ( $row[ $col ] ?? '' ) );
			}
			$out .= implode( ',', $cells ) . "\n";
		}
		$filename = 'rsvps-' . intval( $request['event_id'] ) . '-' . date( 'Ymd' ) . '.csv';
		return new WP_REST_Response(
			$out,
			200,
			array(
				'Content-Type'        => 'text/csv; charset=utf-8',
				'Content-Disposition' => 'attachment; filename="' . $filename . '"',
			)
		);
	}

        public function bulk_update( WP_REST_Request $request ): WP_REST_Response|WP_Error {
                if ( $err = $this->ensure_table() ) {
                        return $err;
                }
                $event_id = intval( $request['event_id'] );
                $ids      = array_map( 'intval', (array) $request['ids'] );
		$status   = sanitize_text_field( $request['status'] );
		$allowed  = array( 'going', 'waitlist', 'cancelled' );
		if ( ! in_array( $status, $allowed, true ) ) {
			return new WP_Error( 'invalid_status', 'Invalid status.', array( 'status' => 400 ) );
		}
		if ( ! $ids ) {
			return new WP_Error( 'invalid_ids', 'No IDs supplied.', array( 'status' => 400 ) );
		}
		global $wpdb;
		$in = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$wpdb->query( $wpdb->prepare( "UPDATE {$this->table()} SET status = %s WHERE event_id = %d AND id IN ($in)", array_merge( array( $status, $event_id ), $ids ) ) );
		do_action( 'ap_rsvp_changed', $event_id );
		return \rest_ensure_response( array( 'updated' => count( $ids ) ) );
	}
}
