<?php
namespace ArtPulse\Community;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Core\EmailService;
use ArtPulse\Community\CommunityRoles;

class DirectMessages {

	public static function register(): void {
		add_action( 'init', array( self::class, 'maybe_install_table' ) );
		add_action( 'init', array( self::class, 'maybe_install_flags_table' ) );
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function maybe_install_table(): void {
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_messages';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists !== $table ) {
			self::install_table();
		}
	}

	public static function install_table(): void {
		global $wpdb;
		$table   = $wpdb->prefix . 'ap_messages';
		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE $table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (id),
            sender_id BIGINT NOT NULL,
            recipient_id BIGINT NOT NULL,
            content TEXT NOT NULL,
            context_type VARCHAR(32) NULL,
            context_id BIGINT NULL,
            parent_id BIGINT NULL,
            attachments TEXT NULL,
            tags TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            is_delivered TINYINT(1) NOT NULL DEFAULT 1,
            KEY sender_id (sender_id),
            KEY recipient_id (recipient_id),
            KEY context (context_type, context_id),
            KEY parent_id (parent_id)
        ) $charset;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( $sql ); }
		dbDelta( $sql );
	}

	public static function maybe_install_flags_table(): void {
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_message_flags';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists !== $table ) {
			self::install_flags_table();
		}
	}

	public static function install_flags_table(): void {
		global $wpdb;
		$table   = $wpdb->prefix . 'ap_message_flags';
		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE $table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (id),
            message_id BIGINT NOT NULL,
            user_id BIGINT NOT NULL,
            action VARCHAR(20) NOT NULL,
            note TEXT NULL,
            logged_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY message_id (message_id),
            KEY user_id (user_id)
        ) $charset;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( $sql ); }
		dbDelta( $sql );
	}

	public static function log_flag( int $message_id, int $user_id, string $action, string $note = '' ): void {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_message_flags';
		$wpdb->insert(
			$table,
			array(
				'message_id' => $message_id,
				'user_id'    => $user_id,
				'action'     => $action,
				'note'       => $note,
				'logged_at'  => current_time( 'mysql' ),
			)
		);
	}

	private static function ensure_messages_table(): ?WP_Error {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_messages';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return new WP_Error( 'missing_table', 'Messages table missing.', array( 'status' => 500 ) );
		}
		return null;
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/messages/send' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/messages/send',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'send_v2' ),
					'permission_callback' => array( self::class, 'permission_send' ),
					'args'                => array(
						'recipient_id' => array(
							'type'     => 'integer',
							'required' => true,
						),
						'content'      => array(
							'type'     => 'string',
							'required' => true,
						),
						'context_type' => array(
							'type'     => 'string',
							'required' => false,
						),
						'context_id'   => array(
							'type'     => 'integer',
							'required' => false,
						),
						'parent_id'    => array(
							'type'     => 'integer',
							'required' => false,
						),
						'attachments'  => array(
							'type'     => 'array',
							'required' => false,
						),
						'tags'         => array(
							'type'     => 'array',
							'required' => false,
						),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/messages' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/messages',
				array(
					array(
						'methods'             => 'POST',
						'callback'            => array( self::class, 'send' ),
						'permission_callback' => array( self::class, 'permission_send' ),
						'args'                => array(
							'recipient_id' => array(
								'type'     => 'integer',
								'required' => true,
							),
							'content'      => array(
								'type'     => 'string',
								'required' => true,
							),
							'nonce'        => array(
								'type'     => 'string',
								'required' => true,
							),
							'context_type' => array(
								'type'     => 'string',
								'required' => false,
							),
							'context_id'   => array(
								'type'     => 'integer',
								'required' => false,
							),
						),
					),
					array(
						'methods'             => 'GET',
						'callback'            => array( self::class, 'fetch' ),
						'permission_callback' => static function () {
							return current_user_can( 'read' );
						},
						'args'                => array(
							'with' => array(
								'type'     => 'integer',
								'required' => true,
							),
						),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/messages/updates' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/messages/updates',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'updates' ),
					'permission_callback' => array( self::class, 'permission_view' ),
					'args'                => array(
						'since'      => array(
							'type'     => 'string',
							'required' => true,
						),
						'context_id' => array(
							'type'     => 'integer',
							'required' => false,
						),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/messages/seen' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/messages/seen',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'seen' ),
					'permission_callback' => array( self::class, 'permission_view' ),
					'args'                => array(
						'message_ids' => array(
							'type'     => 'array',
							'required' => true,
						),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/messages/search' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/messages/search',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'search' ),
					'permission_callback' => array( self::class, 'permission_view' ),
					'args'                => array(
						'q' => array(
							'type'     => 'string',
							'required' => true,
						),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/messages/context/(?P<type>[a-zA-Z0-9_-]+)/(?P<id>\d+)' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/messages/context/(?P<type>[a-zA-Z0-9_-]+)/(?P<id>\d+)',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'fetch_context' ),
					'permission_callback' => array( self::class, 'permission_view' ),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/messages/block' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/messages/block',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'block_user' ),
					'permission_callback' => fn() => CommunityRoles::can_block( get_current_user_id() ),
					'args'                => array(
						'user_id' => array(
							'type'     => 'integer',
							'required' => true,
						),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/messages/label' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/messages/label',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'label' ),
					'permission_callback' => array( self::class, 'permission_view' ),
					'args'                => array(
						'id'     => array(
							'type'     => 'integer',
							'required' => true,
						),
						'tag'    => array(
							'type'     => 'string',
							'required' => true,
						),
						'action' => array(
							'type'     => 'string',
							'required' => false,
						),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/messages/thread' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/messages/thread',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'thread' ),
					'permission_callback' => array( self::class, 'permission_view' ),
					'args'                => array(
						'id' => array(
							'type'     => 'integer',
							'required' => true,
						),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/messages/bulk' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/messages/bulk',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'bulk_action' ),
					'permission_callback' => array( self::class, 'permission_view' ),
					'args'                => array(
						'ids'    => array(
							'type'     => 'array',
							'required' => true,
						),
						'action' => array(
							'type'     => 'string',
							'required' => true,
						),
						'tag'    => array(
							'type'     => 'string',
							'required' => false,
						),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/conversations' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/conversations',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'rest_list_conversations' ),
					'permission_callback' => static function () {
						return is_user_logged_in();
					},
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/message/read' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/message/read',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'mark_read' ),
					'permission_callback' => array( self::class, 'permission_view' ),
					'args'                => array(
						'nonce' => array(
							'type'     => 'string',
							'required' => true,
						),
					),
				)
			);
		}
	}

	public static function send( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		if ( $error = self::ensure_messages_table() ) {
			return $error;
		}
		$nonce = $req->get_param( 'nonce' );
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new WP_Error( 'rest_forbidden', 'Unauthorized.', array( 'status' => 403 ) );
		}

		$sender_id    = get_current_user_id();
		$recipient_id = absint( $req['recipient_id'] );
		$content      = wp_kses_post( $req['content'] );
		$context_type = $req->get_param( 'context_type' ) ? sanitize_key( $req['context_type'] ) : null;
		$context_id   = $req->get_param( 'context_id' ) ? absint( $req['context_id'] ) : null;
		$parent_id    = $req->get_param( 'parent_id' ) ? absint( $req['parent_id'] ) : null;
		$attachments  = $req->get_param( 'attachments' );
		$attachments  = is_array( $attachments ) ? array_map( 'intval', $attachments ) : array();
		$tags         = $req->get_param( 'tags' );
		$tags         = is_array( $tags ) ? array_map( 'sanitize_key', $tags ) : array();

		if ( ! $recipient_id || $content === '' || ! get_user_by( 'id', $recipient_id ) ) {
			return new WP_Error( 'invalid_params', 'Invalid recipient or content.', array( 'status' => 400 ) );
		}

		if ( class_exists( __NAMESPACE__ . '\\BlockedUsers' ) ) {
			if ( BlockedUsers::is_blocked( $recipient_id, $sender_id ) || BlockedUsers::is_blocked( $sender_id, $recipient_id ) ) {
				return new WP_Error( 'blocked', 'User has blocked messages.', array( 'status' => 403 ) );
			}
		}

		try {
			self::add_message( $sender_id, $recipient_id, $content, $context_type, $context_id );
			return \rest_ensure_response( array( 'status' => 'sent' ) );
		} catch ( \Throwable $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'DirectMessages send error: ' . $e->getMessage() );
			}
			return new WP_Error( 'server_error', 'Unexpected error.', array( 'status' => 500 ) );
		}
	}

	public static function send_v2( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		if ( $error = self::ensure_messages_table() ) {
			return $error;
		}
		try {
			$sender_id    = get_current_user_id();
			$recipient_id = absint( $req['recipient_id'] );
			$content      = wp_kses_post( $req['content'] );
			$context_type = $req->get_param( 'context_type' ) ? sanitize_key( $req['context_type'] ) : null;
			$context_id   = $req->get_param( 'context_id' ) ? absint( $req['context_id'] ) : null;
			$parent_id    = $req->get_param( 'parent_id' ) ? absint( $req['parent_id'] ) : null;
			$attachments  = $req->get_param( 'attachments' );
			$attachments  = is_array( $attachments ) ? array_map( 'intval', $attachments ) : array();
			$tags         = $req->get_param( 'tags' );
			$tags         = is_array( $tags ) ? array_map( 'sanitize_key', $tags ) : array();

			if ( ! $recipient_id || $content === '' || ! get_user_by( 'id', $recipient_id ) ) {
				return new WP_Error( 'invalid_params', 'Invalid recipient or content.', array( 'status' => 400 ) );
			}

			if ( class_exists( __NAMESPACE__ . '\\BlockedUsers' ) ) {
				if ( BlockedUsers::is_blocked( $recipient_id, $sender_id ) || BlockedUsers::is_blocked( $sender_id, $recipient_id ) ) {
					return new WP_Error( 'blocked', 'User has blocked messages.', array( 'status' => 403 ) );
				}
			}

			$id  = self::add_message(
				$sender_id,
				$recipient_id,
				$content,
				$context_type,
				$context_id,
				$parent_id,
				$attachments,
				$tags
			);
			$row = self::get_message( $id );

			return \rest_ensure_response( $row );
		} catch ( \Throwable $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'DirectMessages send_v2 error: ' . $e->getMessage() );
			}
			return new WP_Error( 'server_error', 'Unexpected error.', array( 'status' => 500 ) );
		}
	}

	public static function fetch( WP_REST_Request $req ): WP_REST_Response {
		if ( $error = self::ensure_messages_table() ) {
			return $error;
		}
		try {
			$user_id  = get_current_user_id();
			$other_id = absint( $req['with'] );

			$messages = self::get_conversation( $user_id, $other_id );

			return \rest_ensure_response( $messages );
		} catch ( \Throwable $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'DirectMessages fetch error: ' . $e->getMessage() );
			}
			return new WP_Error( 'server_error', 'Unexpected error.', array( 'status' => 500 ) );
		}
	}

	public static function updates( WP_REST_Request $req ): WP_REST_Response {
		if ( $error = self::ensure_messages_table() ) {
			return $error;
		}
		try {
			$user_id    = get_current_user_id();
			$since      = $req->get_param( 'since' );
			$context_id = $req->get_param( 'context_id' ) ? absint( $req['context_id'] ) : null;

			if ( is_numeric( $since ) ) {
				$since = gmdate( 'Y-m-d H:i:s', (int) $since );
			} else {
				$since = sanitize_text_field( $since );
			}

			global $wpdb;
			$table   = $wpdb->prefix . 'ap_messages';
			$columns = 'id, sender_id, recipient_id, content, context_type, context_id, parent_id, attachments, tags, created_at, is_read, is_delivered';
			$sql     = "SELECT $columns FROM %i WHERE created_at > %s AND (sender_id = %d OR recipient_id = %d)";
			$args    = array( $table, $since, $user_id, $user_id );
			if ( $context_id ) {
				$sql   .= ' AND context_id = %d';
				$args[] = $context_id;
			}
			$sql     .= ' ORDER BY created_at ASC';
			$sql      = $wpdb->prepare( $sql, ...$args );
			$rows     = $wpdb->get_results( $sql, ARRAY_A );
			$messages = array_map(
				static function ( $row ) {
					$row['id']           = (int) $row['id'];
					$row['sender_id']    = (int) $row['sender_id'];
					$row['recipient_id'] = (int) $row['recipient_id'];
					$row['is_read']      = (int) $row['is_read'];
					$row['is_delivered'] = isset( $row['is_delivered'] ) ? (int) $row['is_delivered'] : 1;
					return $row;
				},
				$rows
			);

			return \rest_ensure_response( $messages );
		} catch ( \Throwable $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'DirectMessages updates error: ' . $e->getMessage() );
			}
			return new WP_Error( 'server_error', 'Unexpected error.', array( 'status' => 500 ) );
		}
	}

	public static function add_message(
		int $sender_id,
		int $recipient_id,
		string $content,
		?string $context_type = null,
		?int $context_id = null,
		?int $parent_id = null,
		array $attachments = array(),
		array $tags = array()
	): int {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_messages';
		$wpdb->insert(
			$table,
			array(
				'sender_id'    => $sender_id,
				'recipient_id' => $recipient_id,
				'content'      => $content,
				'context_type' => $context_type,
				'context_id'   => $context_id,
				'parent_id'    => $parent_id,
				'attachments'  => $attachments ? implode( ',', array_map( 'intval', $attachments ) ) : null,
				'tags'         => $tags ? implode( ',', array_map( 'sanitize_key', $tags ) ) : null,
				'created_at'   => current_time( 'mysql' ),
				'is_read'      => 0,
				'is_delivered' => 1,
			)
		);
		$id = (int) $wpdb->insert_id;

		$recipient = get_user_by( 'id', $recipient_id );
		$sender    = get_user_by( 'id', $sender_id );
		if ( $recipient && $sender && is_email( $recipient->user_email ) ) {
			$subject = sprintf( 'New message from %s', $sender->display_name );
			EmailService::send( $recipient->user_email, $subject, $content );
		}

		return $id;
	}

	public static function get_message( int $id ): array {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_messages';
		$sql   = $wpdb->prepare( 'SELECT id, sender_id, recipient_id, content, context_type, context_id, parent_id, attachments, tags, created_at, is_read, is_delivered FROM %i WHERE id = %d', $table, $id );
		$row   = $wpdb->get_row( $sql, ARRAY_A );
		if ( ! $row ) {
			return array();
		}
		$row['id']           = (int) $row['id'];
		$row['sender_id']    = (int) $row['sender_id'];
		$row['recipient_id'] = (int) $row['recipient_id'];
		$row['is_read']      = (int) $row['is_read'];
		$row['is_delivered'] = isset( $row['is_delivered'] ) ? (int) $row['is_delivered'] : 1;
		$row['parent_id']    = isset( $row['parent_id'] ) ? (int) $row['parent_id'] : null;
		$row['attachments']  = isset( $row['attachments'] ) && $row['attachments'] !== ''
			? array_map( 'intval', explode( ',', $row['attachments'] ) )
			: array();
		$row['tags']         = isset( $row['tags'] ) && $row['tags'] !== ''
			? array_map( 'sanitize_key', explode( ',', $row['tags'] ) )
			: array();
		return $row;
	}

	public static function get_conversation( int $user_id, int $other_user ): array {
		global $wpdb;
		$table   = $wpdb->prefix . 'ap_messages';
		$columns = 'id, sender_id, recipient_id, content, context_type, context_id, parent_id, attachments, tags, created_at, is_read, is_delivered';
		$sql     = $wpdb->prepare(
			"SELECT $columns FROM %i WHERE (sender_id = %d AND recipient_id = %d) OR (sender_id = %d AND recipient_id = %d) ORDER BY created_at ASC",
			$table,
			$user_id,
			$other_user,
			$other_user,
			$user_id
		);
		$rows    = $wpdb->get_results( $sql, ARRAY_A );
		return array_map(
			static function ( $row ) {
				$row['id']           = (int) $row['id'];
				$row['sender_id']    = (int) $row['sender_id'];
				$row['recipient_id'] = (int) $row['recipient_id'];
				$row['is_read']      = (int) $row['is_read'];
				$row['is_delivered'] = isset( $row['is_delivered'] ) ? (int) $row['is_delivered'] : 1;
				$row['parent_id']    = isset( $row['parent_id'] ) ? (int) $row['parent_id'] : null;
				$row['attachments']  = isset( $row['attachments'] ) && $row['attachments'] !== ''
				? array_map( 'intval', explode( ',', $row['attachments'] ) )
				: array();
				$row['tags']         = isset( $row['tags'] ) && $row['tags'] !== ''
				? array_map( 'sanitize_key', explode( ',', $row['tags'] ) )
				: array();
				return $row;
			},
			$rows
		);
	}

	public static function get_context_conversation( int $user_id, string $context_type, int $context_id ): array {
		global $wpdb;
		$table   = $wpdb->prefix . 'ap_messages';
		$columns = 'id, sender_id, recipient_id, content, context_type, context_id, parent_id, attachments, tags, created_at, is_read, is_delivered';
		$sql     = $wpdb->prepare(
			"SELECT $columns FROM %i WHERE context_type = %s AND context_id = %d AND (sender_id = %d OR recipient_id = %d) ORDER BY created_at ASC",
			$table,
			$context_type,
			$context_id,
			$user_id,
			$user_id
		);
		$rows    = $wpdb->get_results( $sql, ARRAY_A );
		return array_map(
			static function ( $row ) {
				$row['id']           = (int) $row['id'];
				$row['sender_id']    = (int) $row['sender_id'];
				$row['recipient_id'] = (int) $row['recipient_id'];
				$row['is_read']      = (int) $row['is_read'];
				$row['is_delivered'] = isset( $row['is_delivered'] ) ? (int) $row['is_delivered'] : 1;
				$row['parent_id']    = isset( $row['parent_id'] ) ? (int) $row['parent_id'] : null;
				$row['attachments']  = isset( $row['attachments'] ) && $row['attachments'] !== ''
				? array_map( 'intval', explode( ',', $row['attachments'] ) )
				: array();
				$row['tags']         = isset( $row['tags'] ) && $row['tags'] !== ''
				? array_map( 'sanitize_key', explode( ',', $row['tags'] ) )
				: array();
				return $row;
			},
			$rows
		);
	}

	public static function list_conversations( int $user_id ): array {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_messages';
		$sql   = $wpdb->prepare(
			'SELECT CASE WHEN sender_id = %d THEN recipient_id ELSE sender_id END AS other_id,
                       SUM(CASE WHEN recipient_id = %d AND is_read = 0 THEN 1 ELSE 0 END) AS unread
                FROM %i
                WHERE sender_id = %d OR recipient_id = %d
                GROUP BY other_id',
			$user_id,
			$user_id,
			$table,
			$user_id,
			$user_id
		);
		$rows  = $wpdb->get_results( $sql, ARRAY_A );
		return array_map(
			static function ( $row ) {
				$other_id = (int) $row['other_id'];
				$user     = get_user_by( 'id', $other_id );
				return array(
					'user_id'      => $other_id,
					'unread'       => (int) $row['unread'],
					'display_name' => $user ? $user->display_name : '',
					'avatar'       => $user ? get_avatar_url( $other_id, array( 'size' => 48 ) ) : '',
				);
			},
			$rows
		);
	}

	public static function rest_list_conversations( WP_REST_Request $req ): WP_REST_Response {
		if ( $error = self::ensure_messages_table() ) {
			return $error;
		}
		try {
			$user_id = get_current_user_id();
			$list    = self::list_conversations( $user_id );
			return \rest_ensure_response( $list );
		} catch ( \Throwable $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'DirectMessages conversations error: ' . $e->getMessage() );
			}
			return new WP_Error( 'server_error', 'Unexpected error.', array( 'status' => 500 ) );
		}
	}

	public static function permission_view( WP_REST_Request $req ): bool {
		return is_user_logged_in();
	}

	public static function permission_send( WP_REST_Request $req ): bool {
		return current_user_can( 'ap_send_messages' ) && self::permission_view( $req );
	}

	public static function mark_read( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		if ( $error = self::ensure_messages_table() ) {
			return $error;
		}
		try {
			$ids = $req->get_param( 'ids' );
			if ( ! is_array( $ids ) ) {
				$id  = $req->get_param( 'id' );
				$ids = $id ? array( $id ) : array();
			}
			$ids = array_map( 'intval', $ids );
			if ( ! $ids ) {
				return new WP_Error( 'invalid_params', 'No message IDs', array( 'status' => 400 ) );
			}

			self::mark_read_ids( $ids, get_current_user_id() );

			return \rest_ensure_response( array( 'updated' => count( $ids ) ) );
		} catch ( \Throwable $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'DirectMessages mark_read error: ' . $e->getMessage() );
			}
			return new WP_Error( 'server_error', 'Unexpected error.', array( 'status' => 500 ) );
		}
	}

	public static function seen( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		if ( $error = self::ensure_messages_table() ) {
			return $error;
		}
		try {
			$ids = $req->get_param( 'message_ids' );
			if ( ! is_array( $ids ) ) {
				return new WP_Error( 'invalid_params', 'message_ids must be array', array( 'status' => 400 ) );
			}
			$ids = array_map( 'intval', $ids );
			self::mark_read_ids( $ids, get_current_user_id() );

			return \rest_ensure_response( array( 'updated' => count( $ids ) ) );
		} catch ( \Throwable $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'DirectMessages seen error: ' . $e->getMessage() );
			}
			return new WP_Error( 'server_error', 'Unexpected error.', array( 'status' => 500 ) );
		}
	}

	public static function mark_read_ids( array $ids, int $user_id ): void {
		if ( ! $ids ) {
			return;
		}
		global $wpdb;
		$ids     = array_map( 'absint', $ids );
		$user_id = absint( $user_id );
		$table   = $wpdb->prefix . 'ap_messages';
		$place   = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$args    = array_merge( array( $table ), $ids, array( $user_id ) );
		$sql     = $wpdb->prepare( "UPDATE %i SET is_read = 1 WHERE id IN ($place) AND recipient_id = %d", ...$args );
		$wpdb->query( $sql );
	}

	public static function mark_unread_ids( array $ids, int $user_id ): void {
		if ( ! $ids ) {
			return;
		}
		global $wpdb;
		$ids     = array_map( 'absint', $ids );
		$user_id = absint( $user_id );
		$table   = $wpdb->prefix . 'ap_messages';
		$place   = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$args    = array_merge( array( $table ), $ids, array( $user_id ) );
		$sql     = $wpdb->prepare( "UPDATE %i SET is_read = 0 WHERE id IN ($place) AND recipient_id = %d", ...$args );
		$wpdb->query( $sql );
	}

	public static function search( WP_REST_Request $req ): WP_REST_Response {
		$user_id = absint( get_current_user_id() );
		$term    = sanitize_text_field( $req->get_param( 'q' ) );

		global $wpdb;
		$table    = $wpdb->prefix . 'ap_messages';
		$like     = ap_db_like( $term );
		$columns  = 'id, sender_id, recipient_id, content, context_type, context_id, parent_id, attachments, tags, created_at, is_read, is_delivered';
		$sql      = $wpdb->prepare(
			"SELECT $columns, other_id FROM (SELECT $columns, CASE WHEN sender_id = %d THEN recipient_id ELSE sender_id END AS other_id FROM %i WHERE (sender_id = %d OR recipient_id = %d) AND content LIKE %s ORDER BY created_at DESC) t GROUP BY other_id",
			$user_id,
			$table,
			$user_id,
			$user_id,
			$like
		);
		$rows     = $wpdb->get_results( $sql, ARRAY_A );
		$messages = array_map(
			static function ( $row ) {
				$row['id']           = (int) $row['id'];
				$row['sender_id']    = (int) $row['sender_id'];
				$row['recipient_id'] = (int) $row['recipient_id'];
				$row['is_read']      = (int) $row['is_read'];
				$row['is_delivered'] = isset( $row['is_delivered'] ) ? (int) $row['is_delivered'] : 1;
				return $row;
			},
			$rows
		);

		return \rest_ensure_response( $messages );
	}

	public static function fetch_context( WP_REST_Request $req ): WP_REST_Response {
		if ( $error = self::ensure_messages_table() ) {
			return $error;
		}
		try {
			$user_id  = get_current_user_id();
			$type     = sanitize_key( $req['type'] );
			$id       = absint( $req['id'] );
			$messages = self::get_context_conversation( $user_id, $type, $id );
			return \rest_ensure_response( $messages );
		} catch ( \Throwable $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'DirectMessages fetch_context error: ' . $e->getMessage() );
			}
			return new WP_Error( 'server_error', 'Unexpected error.', array( 'status' => 500 ) );
		}
	}

	public static function block_user( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		if ( $error = self::ensure_messages_table() ) {
			return $error;
		}
		$user_id  = get_current_user_id();
		$block_id = absint( $req['user_id'] );
		if ( ! $block_id || ! get_user_by( 'id', $block_id ) ) {
			return new WP_Error( 'invalid_user', 'Invalid user ID', array( 'status' => 400 ) );
		}
		if ( ! class_exists( __NAMESPACE__ . '\\BlockedUsers' ) ) {
			return new WP_Error( 'missing_feature', 'Blocking not available', array( 'status' => 500 ) );
		}
		BlockedUsers::add( $user_id, $block_id );
		return \rest_ensure_response(
			array(
				'status'  => 'blocked',
				'user_id' => $block_id,
			)
		);
	}

	public static function label( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		if ( $error = self::ensure_messages_table() ) {
			return $error;
		}
		try {
			$id     = absint( $req['id'] );
			$tag    = sanitize_key( $req['tag'] );
			$action = $req->get_param( 'action' ) ? sanitize_key( $req['action'] ) : 'add';

			global $wpdb;
			$table = $wpdb->prefix . 'ap_messages';
			$sql   = $wpdb->prepare( 'SELECT tags FROM %i WHERE id = %d AND (sender_id = %d OR recipient_id = %d)', $table, $id, get_current_user_id(), get_current_user_id() );
			$row   = $wpdb->get_row( $sql, ARRAY_A );
			if ( ! $row ) {
				return new WP_Error( 'not_found', 'Message not found', array( 'status' => 404 ) );
			}
			$tags = $row['tags'] ? explode( ',', $row['tags'] ) : array();
			if ( $action === 'add' ) {
				if ( ! in_array( $tag, $tags, true ) ) {
					$tags[] = $tag; }
			} elseif ( $action === 'remove' ) {
				$tags = array_diff( $tags, array( $tag ) );
			}
			$wpdb->update( $table, array( 'tags' => implode( ',', $tags ) ), array( 'id' => $id ) );

			return \rest_ensure_response( array( 'tags' => $tags ) );
		} catch ( \Throwable $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'DirectMessages label error: ' . $e->getMessage() );
			}
			return new WP_Error( 'server_error', 'Unexpected error.', array( 'status' => 500 ) );
		}
	}

	public static function thread( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		if ( $error = self::ensure_messages_table() ) {
			return $error;
		}
		try {
			$id  = absint( $req['id'] );
			$msg = self::get_message( $id );
			if ( ! $msg ) {
				return new WP_Error( 'not_found', 'Message not found', array( 'status' => 404 ) );
			}
			$thread_id = $msg['parent_id'] ? $msg['parent_id'] : $msg['id'];
			global $wpdb;
			$table    = $wpdb->prefix . 'ap_messages';
			$sql      = $wpdb->prepare( 'SELECT id, sender_id, recipient_id, content, context_type, context_id, parent_id, attachments, tags, created_at, is_read, is_delivered FROM %i WHERE id = %d OR parent_id = %d ORDER BY created_at ASC', $table, $thread_id, $thread_id );
			$rows     = $wpdb->get_results( $sql, ARRAY_A );
			$messages = array_map( array( self::class, 'get_message' ), wp_list_pluck( $rows, 'id' ) );
			return \rest_ensure_response( $messages );
		} catch ( \Throwable $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'DirectMessages thread error: ' . $e->getMessage() );
			}
			return new WP_Error( 'server_error', 'Unexpected error.', array( 'status' => 500 ) );
		}
	}

	public static function bulk_action( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		if ( $error = self::ensure_messages_table() ) {
			return $error;
		}
		try {
			$ids    = $req->get_param( 'ids' );
			$action = sanitize_key( $req['action'] );
			$tag    = $req->get_param( 'tag' );
			$ids    = array_map( 'intval', (array) $ids );

			if ( $action === 'read' ) {
				self::mark_read_ids( $ids, get_current_user_id() );
			} elseif ( $action === 'unread' ) {
				self::mark_unread_ids( $ids, get_current_user_id() );
			} elseif ( $action === 'label-add' && $tag ) {
				foreach ( $ids as $id ) {
					$r = new WP_REST_Request( 'POST', '/' );
					$r->set_param( 'id', $id );
					$r->set_param( 'tag', $tag );
					$r->set_param( 'action', 'add' );
					self::label( $r );
				}
			} elseif ( $action === 'label-remove' && $tag ) {
				foreach ( $ids as $id ) {
					$r = new WP_REST_Request( 'POST', '/' );
					$r->set_param( 'id', $id );
					$r->set_param( 'tag', $tag );
					$r->set_param( 'action', 'remove' );
					self::label( $r );
				}
			} else {
				return new WP_Error( 'invalid_action', 'Unknown bulk action', array( 'status' => 400 ) );
			}

			return \rest_ensure_response( array( 'updated' => count( $ids ) ) );
		} catch ( \Throwable $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'DirectMessages bulk_action error: ' . $e->getMessage() );
			}
			return new WP_Error( 'server_error', 'Unexpected error.', array( 'status' => 500 ) );
		}
	}
}
