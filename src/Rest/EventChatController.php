<?php
declare(strict_types=1);

namespace ArtPulse\Rest;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class EventChatController extends WP_REST_Controller {

	/** @var string */
	protected $namespace;

	public function __construct() {
		// Safe fallback if constant isn't defined yet.
		$this->namespace = \defined( 'ARTPULSE_API_NAMESPACE' ) ? \ARTPULSE_API_NAMESPACE : 'artpulse/v1';
	}

	public static function register(): void {
		$controller = new self();
		add_action( 'rest_api_init', array( $controller, 'register_routes' ) );
	}

	public function register_routes(): void {
		// Ensure DB helpers are available (correct path: plugin root, not /src).
		if ( ! \function_exists( '\\ArtPulse\\DB\\Chat\\maybe_install_tables' ) ) {
			// Requires that ARTPULSE_PLUGIN_FILE points to the plugin bootstrap file.
			$db_file = \plugin_dir_path( \defined( 'ARTPULSE_PLUGIN_FILE' ) ? \ARTPULSE_PLUGIN_FILE : __FILE__ ) . 'includes/chat-db.php';
			if ( is_readable( $db_file ) ) {
				require_once $db_file;
			}
		}

		register_rest_route(
			$this->namespace,
			'/event/(?P<id>\d+)/chat',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_messages' ),
					'permission_callback' => array( $this, 'can_read' ),
					'args'                => array(
						'id' => array(
							'validate_callback' => static fn( $v ) => \is_numeric( $v ) && (int) $v > 0,
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'can_write' ),
					'args'                => array(
						'id'      => array( 'validate_callback' => static fn( $v ) => \is_numeric( $v ) && (int) $v > 0 ),
						'content' => array(
							'type'     => 'string',
							'required' => true,
						),
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/chat/(?P<id>\d+)/reaction',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'add_reaction' ),
				'permission_callback' => array( $this, 'can_write' ),
				'args'                => array(
					'id'    => array( 'validate_callback' => static fn( $v ) => \is_numeric( $v ) && (int) $v > 0 ),
					'emoji' => array(
						'type'     => 'string',
						'required' => true,
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/chat/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'moderator_check' ),
				'args'                => array(
					'id' => array( 'validate_callback' => static fn( $v ) => \is_numeric( $v ) && (int) $v > 0 ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/chat/(?P<id>\d+)/flag',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'flag_item' ),
				'permission_callback' => array( $this, 'moderator_check' ),
				'args'                => array(
					'id' => array( 'validate_callback' => static fn( $v ) => \is_numeric( $v ) && (int) $v > 0 ),
				),
			)
		);

		// Optional slug route to avoid ID mismatches
		register_rest_route(
			$this->namespace,
			'/event/by-slug/(?P<slug>[A-Za-z0-9-_]+)/chat',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_messages_by_slug' ),
					'permission_callback' => array( $this, 'can_read_by_slug' ),
				),
			)
		);
	}

	/** Read permission for chat (logged-in + event exists) */
	public function can_read( WP_REST_Request $request ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'rest_forbidden', __( 'Authentication required.', 'artpulse' ), array( 'status' => 401 ) );
		}
		$event_id = (int) $request['id'];
		$post     = get_post( $event_id );
		if ( ! $post ) {
			error_log( "[artpulse] chat 404: event {$event_id} not found" );
			return new WP_Error( 'ap_event_not_found', __( 'Event not found.', 'artpulse' ), array( 'status' => 404 ) );
		}
		if ( $post->post_type !== 'artpulse_event' ) {
			error_log( "[artpulse] chat 404: id {$event_id} is type {$post->post_type}, expected artpulse_event" );
			return new WP_Error( 'ap_event_not_found', __( 'Event not found.', 'artpulse' ), array( 'status' => 404 ) );
		}
		if ( ! current_user_can( 'read_post', $event_id ) && ! current_user_can( 'read' ) ) {
			return new WP_Error( 'rest_forbidden', __( 'You cannot view this chat.', 'artpulse' ), array( 'status' => 403 ) );
		}
		return true;
	}

	/** Write permission (nonce + read access) */
	public function can_write( WP_REST_Request $request ) {
		$auth = $this->can_read( $request );
		if ( $auth !== true ) {
			return $auth;
		}
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new WP_Error( 'rest_forbidden', __( 'Invalid or missing nonce.', 'artpulse' ), array( 'status' => 401 ) );
		}
		return true;
	}

	public function get_messages( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$event_id = (int) $request['id'];

		try {
			if ( ! \function_exists( '\\ArtPulse\\DB\\Chat\\get_messages' ) ) {
				throw new \RuntimeException( 'Chat DB helpers not loaded' );
			}

			\ArtPulse\DB\Chat\maybe_install_tables();
			$rows = \ArtPulse\DB\Chat\get_messages( $event_id );

			$messages = array_map(
				static function ( array $row ) {
					$uid    = (int) ( $row['user_id'] ?? 0 );
					$user   = $uid ? get_userdata( $uid ) : null;
					$avatar = $uid ? get_avatar_url( $uid, array( 'size' => 48 ) ) : '';
					if ( $avatar ) {
						$avatar = set_url_scheme( $avatar, is_ssl() ? 'https' : 'http' ); // don’t force https on LAN
					}

					$reactions = $row['reactions'] ?? array();
					if ( \is_string( $reactions ) ) {
						$decoded   = json_decode( $reactions, true );
						$reactions = \is_array( $decoded ) ? $decoded : array();
					}

					return array(
						'id'         => (int) ( $row['id'] ?? 0 ),
						'user_id'    => $uid,
						'author'     => $user ? (string) $user->display_name : '',
						'avatar'     => $avatar ?: '',
						'content'    => (string) ( $row['content'] ?? '' ),
						'created_at' => (string) ( $row['created_at'] ?? '' ),
						'flagged'    => (int) ( $row['flagged'] ?? 0 ),
						'reactions'  => $reactions,
					);
				},
				is_array( $rows ) ? $rows : array()
			);

			return \rest_ensure_response( $messages );
		} catch ( \Throwable $e ) {
			error_log( '[artpulse] chat GET error for event ' . $event_id . ': ' . $e->getMessage() );
			return new WP_Error( 'ap_chat_error', __( 'Chat load failed.', 'artpulse' ), array( 'status' => 500 ) );
		}
	}

	/**
	 * Create a chat message.
	 *
	 * Matches the signature expected by WP_REST_Controller::create_item().
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_item( $request ) {
		$event_id = (int) $request['id'];
		$content  = (string) $request['content'];

		if ( ! $event_id || get_post_type( $event_id ) !== 'artpulse_event' ) {
			return new WP_Error( 'invalid_event', __( 'Invalid event.', 'artpulse' ), array( 'status' => 404 ) );
		}

		// Basic sanitation/limits (adjust if you allow markup)
		$content = sanitize_text_field( $content );
		if ( $content === '' ) {
			return new WP_Error( 'empty_content', __( 'Message content required.', 'artpulse' ), array( 'status' => 400 ) );
		}
		if ( mb_strlen( $content ) > 1000 ) {
			return new WP_Error( 'content_too_long', __( 'Message too long.', 'artpulse' ), array( 'status' => 413 ) );
		}

		try {
			if ( ! \function_exists( '\\ArtPulse\\DB\\Chat\\insert_message' ) ) {
				throw new \RuntimeException( 'Chat DB helpers not loaded' );
			}
			\ArtPulse\DB\Chat\maybe_install_tables();
			$msg = \ArtPulse\DB\Chat\insert_message( $event_id, get_current_user_id(), $content );
			return \rest_ensure_response( $msg );
		} catch ( \Throwable $e ) {
			error_log( '[artpulse] chat POST error for event ' . $event_id . ': ' . $e->getMessage() );
			return new WP_Error( 'ap_chat_error', __( 'Could not post message.', 'artpulse' ), array( 'status' => 500 ) );
		}
	}

	public function add_reaction( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$msg_id = (int) $request['id'];
		$emoji  = sanitize_text_field( (string) $request['emoji'] );
		if ( ! $msg_id || $emoji === '' ) {
			return new WP_Error( 'invalid_request', __( 'Invalid reaction.', 'artpulse' ), array( 'status' => 400 ) );
		}

		try {
			if ( ! \function_exists( '\\ArtPulse\\DB\\Chat\\add_reaction' ) ) {
				throw new \RuntimeException( 'Chat DB helpers not loaded' );
			}
			\ArtPulse\DB\Chat\add_reaction( $msg_id, get_current_user_id(), $emoji );
			return \rest_ensure_response( array( 'status' => 'ok' ) );
		} catch ( \Throwable $e ) {
			error_log( '[artpulse] chat REACTION error for message ' . $msg_id . ': ' . $e->getMessage() );
			return new WP_Error( 'ap_chat_error', __( 'Could not add reaction.', 'artpulse' ), array( 'status' => 500 ) );
		}
	}

	/**
	 * Delete a chat message.
	 *
	 * Matches the signature expected by WP_REST_Controller::delete_item().
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {
		$id = (int) $request['id'];
		if ( ! $id ) {
			return new WP_Error( 'invalid_request', __( 'Invalid message id.', 'artpulse' ), array( 'status' => 400 ) );
		}

		try {
			if ( ! \function_exists( '\\ArtPulse\\DB\\Chat\\delete_message' ) ) {
				throw new \RuntimeException( 'Chat DB helpers not loaded' );
			}
			\ArtPulse\DB\Chat\delete_message( $id );
			return \rest_ensure_response( array( 'status' => 'deleted' ) );
		} catch ( \Throwable $e ) {
			error_log( '[artpulse] chat DELETE error for message ' . $id . ': ' . $e->getMessage() );
			return new WP_Error( 'ap_chat_error', __( 'Could not delete message.', 'artpulse' ), array( 'status' => 500 ) );
		}
	}

	public function flag_item( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$id = (int) $request['id'];
		if ( ! $id ) {
			return new WP_Error( 'invalid_request', __( 'Invalid message id.', 'artpulse' ), array( 'status' => 400 ) );
		}

		try {
			if ( ! \function_exists( '\\ArtPulse\\DB\\Chat\\flag_message' ) ) {
				throw new \RuntimeException( 'Chat DB helpers not loaded' );
			}
			\ArtPulse\DB\Chat\flag_message( $id );
			return \rest_ensure_response( array( 'status' => 'flagged' ) );
		} catch ( \Throwable $e ) {
			error_log( '[artpulse] chat FLAG error for message ' . $id . ': ' . $e->getMessage() );
			return new WP_Error( 'ap_chat_error', __( 'Could not flag message.', 'artpulse' ), array( 'status' => 500 ) );
		}
	}

	public function moderator_check( WP_REST_Request $request ) {
		if ( ! current_user_can( 'moderate_comments' ) ) {
			return new WP_Error( 'rest_forbidden', __( 'Moderator permission required.', 'artpulse' ), array( 'status' => 403 ) );
		}
		return true;
	}

	// --- Slug helpers (optional) ---
	public function can_read_by_slug( \WP_REST_Request $req ) {
		if ( ! is_user_logged_in() ) {
			return new \WP_Error( 'rest_forbidden', __( 'Authentication required.', 'artpulse' ), array( 'status' => 401 ) );
		}
		$slug = sanitize_title( $req['slug'] ?? '' );
		$post = get_page_by_path( $slug, OBJECT, 'artpulse_event' );
		if ( ! $post ) {
			return new \WP_Error( 'ap_event_not_found', __( 'Event not found.', 'artpulse' ), array( 'status' => 404 ) );
		}
		if ( ! current_user_can( 'read_post', $post->ID ) && ! current_user_can( 'read' ) ) {
			return new \WP_Error( 'rest_forbidden', __( 'You cannot view this chat.', 'artpulse' ), array( 'status' => 403 ) );
		}
		return true;
	}

	public function get_messages_by_slug( \WP_REST_Request $req ) {
		$slug = sanitize_title( $req['slug'] ?? '' );
		$post = get_page_by_path( $slug, OBJECT, 'artpulse_event' );
		if ( ! $post ) {
			return new \WP_Error( 'ap_event_not_found', __( 'Event not found.', 'artpulse' ), array( 'status' => 404 ) );
		}
		$req['id'] = (int) $post->ID;
		return $this->get_messages( $req );
	}
}
