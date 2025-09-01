<?php
namespace ArtPulse\Rest;

use ArtPulse\Rest\Util\Auth;
use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Rest\RestResponder;

class EventChatPostController extends WP_REST_Controller {
	use RestResponder;

	/**
	 * REST API namespace for these routes.
	 *
	 * @var string
	 */
	protected $namespace = ARTPULSE_API_NAMESPACE;

	public static function register(): void {
		$controller = new self();
		add_action( 'rest_api_init', array( $controller, 'register_routes' ) );
	}

	public function register_routes(): void {
		// Duplicate route /event/(?P<id>\d+)/chat (POST) removed; handled in EventChatController.php
		// register_rest_route($this->namespace, '/event/(?P<id>\d+)/chat', [
		// 'methods'             => WP_REST_Server::CREATABLE,
		// 'callback'            => [$this, 'create_item'],
		// 'permission_callback' => [$this, 'permissions'],
		// 'args'                => [
		// 'id'      => ['validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value )],
		// 'content' => ['type' => 'string', 'required' => true],
		// ],
		// ]);

                register_rest_route(
                        $this->namespace,
                        '/chat/(?P<id>\d+)/reaction',
                        array(
                                'methods'             => WP_REST_Server::CREATABLE,
                                'callback'            => array( $this, 'add_reaction' ),
                                'permission_callback' => array( Auth::class, 'guard_read' ),
                                'args'                => array(
                                        'id'    => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ),
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
                                'permission_callback' => array( Auth::class, 'guard_manage' ),
                                'args'                => array( 'id' => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ) ),
                        )
                );

                register_rest_route(
                        $this->namespace,
                        '/chat/(?P<id>\d+)/flag',
                        array(
                                'methods'             => WP_REST_Server::EDITABLE,
                                'callback'            => array( $this, 'flag_item' ),
                                'permission_callback' => array( Auth::class, 'guard_manage' ),
                                'args'                => array( 'id' => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ) ),
                        )
                );
        }

	public function add_reaction( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$msg_id = absint( $request['id'] );
		$emoji  = sanitize_text_field( $request['emoji'] );
		if ( ! $msg_id || $emoji === '' ) {
			return new WP_Error( 'invalid_request', 'Invalid reaction.', array( 'status' => 400 ) );
		}
		\ArtPulse\DB\Chat\add_reaction( $msg_id, get_current_user_id(), $emoji );
		return \rest_ensure_response( array( 'status' => 'ok' ) );
	}

	/** @param WP_REST_Request $request */
	public function delete_item( $request ) {
		$id = absint( $request['id'] );
		\ArtPulse\DB\Chat\delete_message( $id );
		return \rest_ensure_response( array( 'status' => 'deleted' ) );
	}

	public function flag_item( WP_REST_Request $request ): WP_REST_Response {
		$id = absint( $request['id'] );
		\ArtPulse\DB\Chat\flag_message( $id );
		return \rest_ensure_response( array( 'status' => 'flagged' ) );
	}
}
