<?php
namespace ArtPulse\Community;

class ProfileLinkRequestRestController {

	public static function register() {
		// ✅ Properly defer route registration to rest_api_init
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes() {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/link-request' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/link-request',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'create_request' ),
					'permission_callback' => function () {
						return is_user_logged_in(); },
					'args'                => array(
						'org_id'  => array(
							'type'     => 'integer',
							'required' => true,
						),
						'message' => array(
							'type'     => 'string',
							'required' => false,
						),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/link-request/(?P<id>\d+)/approve' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/link-request/(?P<id>\d+)/approve',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'approve_request' ),
					'permission_callback' => function () {
						return current_user_can( 'edit_others_posts' ); },
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/link-request/(?P<id>\d+)/deny' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/link-request/(?P<id>\d+)/deny',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'deny_request' ),
					'permission_callback' => function () {
						return current_user_can( 'edit_others_posts' ); },
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/link-requests' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/link-requests',
				array(
					array(
						'methods'             => 'GET',
						'callback'            => array( self::class, 'list_requests' ),
						'permission_callback' => function () {
							return current_user_can( 'edit_others_posts' ); },
						'args'                => array(
							'org_id' => array(
								'type'     => 'integer',
								'required' => true,
							),
							'status' => array(
								'type'     => 'string',
								'required' => false,
								'enum'     => array( 'pending', 'approved', 'denied', 'all' ),
							),
						),
					),
					array(
						'methods'             => 'POST',
						'callback'            => array( ProfileLinkRequestManager::class, 'handle_create_request' ),
						'permission_callback' => fn() => is_user_logged_in(),
						'args'                => array(
							'target_id' => array(
								'type'     => 'integer',
								'required' => true,
							),
						),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/link-requests/bulk' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/link-requests/bulk',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'bulk_update' ),
					'permission_callback' => function () {
						return current_user_can( 'edit_others_posts' ); },
					'args'                => array(
						'ids'    => array(
							'type'     => 'array',
							'required' => true,
							'items'    => array( 'type' => 'integer' ),
						),
						'action' => array(
							'type'     => 'string',
							'enum'     => array( 'approve', 'deny' ),
							'required' => true,
						),
					),
				)
			);
		}
	}

	public static function create_request( $request ) {
		$artist_user_id = get_current_user_id();
		$org_id         = intval( $request['org_id'] ?? 0 );
		$message        = sanitize_text_field( $request['message'] ?? '' );

		if ( ! $org_id ) {
			return new \WP_Error( 'missing_org', 'Missing org_id', array( 'status' => 400 ) );
		}

		$id = \ArtPulse\Community\ProfileLinkRequestManager::create( $artist_user_id, $org_id, $message );

		if ( $id ) {
			return \rest_ensure_response(
				array(
					'success'    => true,
					'request_id' => $id,
				)
			);
		}

		return new \WP_Error( 'create_failed', 'Could not create request', array( 'status' => 500 ) );
	}

	public static function approve_request( $request ) {
		$id = intval( $request['id'] );

		if ( ! get_post( $id ) ) {
			return new \WP_Error( 'not_found', 'Request not found', array( 'status' => 404 ) );
		}

		\ArtPulse\Community\ProfileLinkRequestManager::approve( $id, get_current_user_id() );
		return \rest_ensure_response( array( 'success' => true ) );
	}

	public static function deny_request( $request ) {
		$id = intval( $request['id'] );

		if ( ! get_post( $id ) ) {
			return new \WP_Error( 'not_found', 'Request not found', array( 'status' => 404 ) );
		}

		\ArtPulse\Community\ProfileLinkRequestManager::deny( $id, get_current_user_id() );
		return \rest_ensure_response( array( 'success' => true ) );
	}

	public static function list_requests( $request ) {
		$org_id = intval( $request['org_id'] ?? 0 );
		$status = $request['status'] ?? 'pending';

		if ( ! $org_id ) {
			return new \WP_Error( 'no_org', 'No org_id given', array( 'status' => 400 ) );
		}

		$meta_query = array(
			array(
				'key'   => 'org_id',
				'value' => $org_id,
			),
		);

		if ( $status && $status !== 'all' ) {
			$meta_query[] = array(
				'key'   => 'status',
				'value' => $status,
			);
		}

		$args = array(
			'post_type'   => 'ap_profile_link_req',
			'post_status' => 'publish',
			'meta_query'  => $meta_query,
			'numberposts' => 200,
		);

		$requests = get_posts( $args );
		$out      = array();

		foreach ( $requests as $r ) {
			$artist_id   = get_post_meta( $r->ID, 'artist_user_id', true );
			$artist_user = get_userdata( $artist_id );
			$org_id_val  = get_post_meta( $r->ID, 'org_id', true );
			$org_post    = get_post( $org_id_val );

			$out[] = array(
				'ID'             => $r->ID,
				'artist_user_id' => $artist_id,
				'artist_user'    => $artist_user ? array(
					'ID'           => $artist_user->ID,
					'user_login'   => $artist_user->user_login,
					'display_name' => $artist_user->display_name,
				) : null,
				'org_id'         => $org_id_val,
				'org_title'      => $org_post ? $org_post->post_title : '',
				'message'        => get_post_meta( $r->ID, 'message', true ),
				'requested_on'   => get_post_meta( $r->ID, 'requested_on', true ),
				'status'         => get_post_meta( $r->ID, 'status', true ),
			);
		}

		return \rest_ensure_response( $out );
	}

	public static function bulk_update( $request ) {
		$ids    = $request->get_param( 'ids' );
		$action = $request->get_param( 'action' );

		if ( ! is_array( $ids ) || ! in_array( $action, array( 'approve', 'deny' ), true ) ) {
			return new \WP_Error( 'invalid_args', 'Invalid arguments', array( 'status' => 400 ) );
		}

		foreach ( $ids as $id ) {
			$id = intval( $id );
			if ( $action === 'approve' ) {
				\ArtPulse\Community\ProfileLinkRequestManager::approve( $id, get_current_user_id() );
			} else {
				\ArtPulse\Community\ProfileLinkRequestManager::deny( $id, get_current_user_id() );
			}
		}

		return \rest_ensure_response(
			array(
				'updated' => $ids,
				'action'  => $action,
			)
		);
	}
}
