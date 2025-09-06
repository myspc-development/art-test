<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;
use ArtPulse\Rest\RestResponder;

class CommunityAnalyticsController {
	use RestResponder;

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/analytics/community/messaging' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/analytics/community/messaging',
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( self::class, 'get_messaging' ),
					'permission_callback' => array( self::class, 'check_permission' ),
					'args'                => array(
						'range'     => array(
							'type'     => 'string',
							'required' => false,
						),
						'top_users' => array(
							'type'     => 'boolean',
							'required' => false,
						),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/analytics/community/comments' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/analytics/community/comments',
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( self::class, 'get_comments' ),
					'permission_callback' => array( self::class, 'check_permission' ),
					'args'                => array(
						'range' => array(
							'type'     => 'string',
							'required' => false,
						),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/analytics/community/forums' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/analytics/community/forums',
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( self::class, 'get_forums' ),
					'permission_callback' => array( self::class, 'check_permission' ),
					'args'                => array(
						'range' => array(
							'type'     => 'string',
							'required' => false,
						),
					),
				)
			);
		}
	}

	public static function check_permission(): bool {
		return is_user_logged_in() && current_user_can( 'view_artpulse_dashboard' );
	}

	private static function range_date( string $range ): string {
		if ( preg_match( '/^(\d+)([dwm])$/', $range, $m ) ) {
			$n = (int) $m[1];
			switch ( $m[2] ) {
				case 'd':
					return date( 'Y-m-d H:i:s', strtotime( "-{$n} days" ) );
				case 'w':
					return date( 'Y-m-d H:i:s', strtotime( '-' . ( $n * 7 ) . ' days' ) );
				case 'm':
					return date( 'Y-m-d H:i:s', strtotime( "-{$n} months" ) );
			}
		}
		return date( 'Y-m-d H:i:s', strtotime( '-7 days' ) );
	}

	public static function get_messaging( WP_REST_Request $req ): WP_REST_Response|WP_Error {
			global $wpdb;
			$since  = self::range_date( $req->get_param( 'range' ) ?? '7d' );
			$tables = array(
				$wpdb->prefix . 'ap_messages',
				$wpdb->prefix . 'ap_blocked_users',
			);
			foreach ( $tables as $tbl ) {
					$exists = (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tbl ) );
				if ( ! $exists ) {
						return ( new self() )->fail( 'ap_db_missing', 'Required table missing', 500 );
				}
			}

			$table   = $tables[0];
			$total   = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE created_at >= %s", $since ) );
			$per_day = $wpdb->get_results( $wpdb->prepare( "SELECT DATE(created_at) AS day, COUNT(*) AS c FROM $table WHERE created_at >= %s GROUP BY day", $since ), ARRAY_A );

			$top = array();
			if ( $req->get_param( 'top_users' ) ) {
					$top = $wpdb->get_results( $wpdb->prepare( "SELECT sender_id AS user_id, COUNT(*) AS c FROM $table WHERE created_at >= %s GROUP BY sender_id ORDER BY c DESC LIMIT 5", $since ), ARRAY_A );
			}

			$blocked_table = $tables[1];
			$blocked_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $blocked_table" );

			return \rest_ensure_response(
				array(
					'total'         => $total,
					'per_day'       => $per_day,
					'top_users'     => $top,
					'blocked_count' => $blocked_count,
				)
			);
	}

	public static function get_comments( WP_REST_Request $req ): WP_REST_Response|WP_Error {
			global $wpdb;
			$since    = self::range_date( $req->get_param( 'range' ) ?? '7d' );
			$comments = $wpdb->comments;

			$flagged_table = $wpdb->prefix . 'ap_comment_reports';
			$exists        = (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $flagged_table ) );
		if ( ! $exists ) {
				return ( new self() )->fail( 'ap_db_missing', 'Required table missing', 500 );
		}

			$total     = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $comments WHERE comment_approved = '1' AND comment_date >= %s", $since ) );
			$top_posts = $wpdb->get_results( $wpdb->prepare( "SELECT comment_post_ID AS post_id, COUNT(*) AS c FROM $comments WHERE comment_approved = '1' AND comment_date >= %s GROUP BY comment_post_ID ORDER BY c DESC LIMIT 5", $since ), ARRAY_A );

			$flagged_count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $flagged_table WHERE created_at >= %s", $since ) );

			return \rest_ensure_response(
				array(
					'total'         => $total,
					'top_posts'     => $top_posts,
					'flagged_count' => $flagged_count,
				)
			);
	}

	public static function get_forums( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		global $wpdb;
		$since    = self::range_date( $req->get_param( 'range' ) ?? '7d' );
		$posts    = $wpdb->posts;
		$comments = $wpdb->comments;

		$threads_created = $wpdb->get_results( $wpdb->prepare( "SELECT DATE(post_date) AS day, COUNT(ID) AS c FROM $posts WHERE post_type = 'ap_forum_thread' AND post_date >= %s GROUP BY day", $since ), ARRAY_A );

		$top_threads = $wpdb->get_results( $wpdb->prepare( "SELECT comment_post_ID AS thread_id, COUNT(*) AS c FROM $comments WHERE comment_post_ID IN (SELECT ID FROM $posts WHERE post_type='ap_forum_thread') AND comment_approved='1' AND comment_date >= %s GROUP BY comment_post_ID ORDER BY c DESC LIMIT 5", $since ), ARRAY_A );

		return \rest_ensure_response(
			array(
				'threads_created' => $threads_created,
				'top_threads'     => $top_threads,
			)
		);
	}
}
