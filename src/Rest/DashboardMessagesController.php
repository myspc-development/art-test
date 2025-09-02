<?php
namespace ArtPulse\Rest;

use ArtPulse\Rest\Util\Auth;
use ArtPulse\Rest\RestResponder;

class DashboardMessagesController {
	use RestResponder;
	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/dashboard/messages' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/dashboard/messages',
				array(
                                        'methods'             => 'GET',
                                        'callback'            => array( self::class, 'get_messages' ),
                                        'permission_callback' => array( Auth::class, 'guard_read' ),
				)
			);
		}
	}

        public static function get_messages(): \WP_REST_Response|\WP_Error {
                $responder = new self();

                if ( ! current_user_can( 'read' ) ) {
                        return $responder->fail( 'unauthorized', 'Login required', 401 );
                }

                global $wpdb;
                $table  = $wpdb->prefix . 'ap_messages';
                $exists = (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
                if ( ! $exists ) {
                        return $responder->fail( 'ap_db_missing', 'Required table missing', 500 );
                }

                return $responder->ok( self::get_recent_messages_for_user( get_current_user_id() ) );
        }

	private static function get_recent_messages_for_user( int $user_id ): array {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_messages';
		$sql   = $wpdb->prepare(
			"SELECT m.id,
                    m.content,
                    m.sender_id,
                    u.display_name AS sender_name,
                    m.created_at AS timestamp,
                    CASE WHEN m.is_read = 0 THEN 'unread' ELSE 'read' END AS status
             FROM $table m
             JOIN {$wpdb->users} u ON m.sender_id = u.ID
             WHERE m.recipient_id = %d OR m.sender_id = %d
             ORDER BY m.created_at DESC
             LIMIT 5",
			$user_id,
			$user_id
		);
		$rows  = $wpdb->get_results( $sql, ARRAY_A );
		foreach ( $rows as &$row ) {
			$row['id']        = (int) $row['id'];
			$row['sender_id'] = (int) $row['sender_id'];
			$row['timestamp'] = $row['timestamp'];
			$row['status']    = $row['status'];
		}
		return $rows;
	}
}
