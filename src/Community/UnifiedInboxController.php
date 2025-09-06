<?php
namespace ArtPulse\Community;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * REST controller providing a combined inbox of messages, notifications
 * and RSVP updates for the current user.
 */
class UnifiedInboxController {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/inbox' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/inbox',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'list' ),
					'permission_callback' => fn() => is_user_logged_in(),
					'args'                => array(
						'limit' => array(
							'type'    => 'integer',
							'default' => 20,
						),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/inbox/read' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/inbox/read',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'mark_read' ),
					'permission_callback' => fn() => is_user_logged_in(),
					'args'                => array(
						'type' => array(
							'type'     => 'string',
							'required' => true,
						),
						'id'   => array(
							'type'     => 'integer',
							'required' => false,
						),
						'ids'  => array(
							'type'     => 'array',
							'required' => false,
						),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/inbox/unread' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/inbox/unread',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'mark_unread' ),
					'permission_callback' => fn() => is_user_logged_in(),
					'args'                => array(
						'type' => array(
							'type'     => 'string',
							'required' => true,
						),
						'id'   => array(
							'type'     => 'integer',
							'required' => false,
						),
						'ids'  => array(
							'type'     => 'array',
							'required' => false,
						),
					),
				)
			);
		}
	}

	public static function list( WP_REST_Request $request ): WP_REST_Response {
		global $wpdb;
		$user_id = get_current_user_id();
		$limit   = absint( $request->get_param( 'limit' ) ) ?: 20;

		$items = array();

		$notes = NotificationManager::get( $user_id, $limit );
		foreach ( $notes as $n ) {
			$items[] = array(
				'id'        => (int) $n->id,
				'type'      => 'notification',
				'content'   => $n->content,
				'timestamp' => $n->created_at,
				'read'      => $n->status === 'read',
			);
		}

				$table   = $wpdb->prefix . 'ap_messages';
				$columns = 'id, sender_id, recipient_id, content, is_read, created_at';
				$rows    = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT $columns FROM $table WHERE sender_id = %d OR recipient_id = %d ORDER BY created_at DESC LIMIT %d",
						$user_id,
						$user_id,
						$limit
					)
				);
		foreach ( $rows as $r ) {
			$items[] = array(
				'id'        => (int) $r->id,
				'type'      => 'message',
				'content'   => $r->content,
				'timestamp' => $r->created_at,
				'read'      => $r->recipient_id == $user_id ? (bool) $r->is_read : true,
				'other_id'  => $r->sender_id == $user_id ? (int) $r->recipient_id : (int) $r->sender_id,
			);
		}

		$log_table   = $wpdb->prefix . 'ap_user_engagement_log';
		$posts_table = $wpdb->posts;
		$rsvp_rows   = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT l.id, l.event_id, l.user_id, l.logged_at FROM {$log_table} l JOIN {$posts_table} p ON l.event_id = p.ID WHERE p.post_author = %d AND l.type = %s ORDER BY l.logged_at DESC LIMIT %d",
				$user_id,
				'rsvp',
				$limit
			)
		);
		$last_read   = get_user_meta( $user_id, 'ap_last_rsvp_read', true ) ?: '0000-00-00 00:00:00';
		foreach ( $rsvp_rows as $row ) {
			$event   = get_post( $row->event_id );
			$items[] = array(
				'id'        => (int) $row->id,
				'type'      => 'rsvp',
				'content'   => $event ? sprintf( esc_html__( 'New RSVP for "%1$s"', 'artpulse' ), esc_html( $event->post_title ) ) : esc_html__( 'New RSVP', 'artpulse' ),
				'timestamp' => $row->logged_at,
				'read'      => $row->logged_at <= $last_read,
				'event_id'  => (int) $row->event_id,
				'user_id'   => (int) $row->user_id,
			);
		}

		usort( $items, static fn( $a, $b ) => strcmp( $b['timestamp'], $a['timestamp'] ) );

		return \rest_ensure_response( $items );
	}

	public static function mark_read( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		$type = sanitize_key( $req->get_param( 'type' ) );
		$ids  = $req->get_param( 'ids' );
		if ( ! is_array( $ids ) ) {
			$id  = $req->get_param( 'id' );
			$ids = $id ? array( $id ) : array();
		}
		$ids = array_map( 'intval', $ids );
		$uid = get_current_user_id();

		if ( $type === 'message' ) {
			DirectMessages::mark_read_ids( $ids, $uid );
		} elseif ( $type === 'notification' ) {
			foreach ( $ids as $id ) {
				NotificationManager::mark_read( $id, $uid );
			}
		} elseif ( $type === 'rsvp' ) {
			update_user_meta( $uid, 'ap_last_rsvp_read', current_time( 'mysql' ) );
		} else {
			return new WP_Error( 'invalid_type', 'Unknown type', array( 'status' => 400 ) );
		}

		return \rest_ensure_response( array( 'updated' => count( $ids ) ) );
	}

	public static function mark_unread( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		$type = sanitize_key( $req->get_param( 'type' ) );
		$ids  = $req->get_param( 'ids' );
		if ( ! is_array( $ids ) ) {
			$id  = $req->get_param( 'id' );
			$ids = $id ? array( $id ) : array();
		}
		$ids = array_map( 'intval', $ids );
		$uid = get_current_user_id();

		if ( $type === 'message' ) {
			DirectMessages::mark_unread_ids( $ids, $uid );
		} elseif ( $type === 'notification' ) {
			foreach ( $ids as $id ) {
				NotificationManager::mark_unread( $id, $uid );
			}
		} elseif ( $type === 'rsvp' ) {
			update_user_meta( $uid, 'ap_last_rsvp_read', '0000-00-00 00:00:00' );
		} else {
			return new WP_Error( 'invalid_type', 'Unknown type', array( 'status' => 400 ) );
		}

		return \rest_ensure_response( array( 'updated' => count( $ids ) ) );
	}
}
