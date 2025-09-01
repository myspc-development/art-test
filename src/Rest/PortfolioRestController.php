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
 * Artist portfolio persistence API.
 */
class PortfolioRestController extends WP_REST_Controller {
	use RestResponder;

	protected $namespace = 'ap/v1';

	public static function register(): void {
		$controller = new self();
		add_action( 'rest_api_init', array( $controller, 'register_routes' ) );
	}

	public function register_routes(): void {
		$permission = Auth::require_login_and_cap( 'read' );

		register_rest_route(
			$this->namespace,
			'/portfolio',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_portfolio' ),
				'permission_callback' => $permission,
				'args'                => array(
					'user' => array(
						'type'    => 'string',
						'default' => 'me',
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/portfolio/items',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'add_item' ),
				'permission_callback' => $permission,
				'args'                => array(
					'media_id' => array(
						'type'     => 'integer',
						'required' => true,
					),
					'meta'     => array(
						'type'     => 'object',
						'required' => true,
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/portfolio/order',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'save_order' ),
				'permission_callback' => $permission,
				'args'                => array(
					'order' => array(
						'type'     => 'array',
						'required' => true,
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/portfolio/featured',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'set_featured' ),
				'permission_callback' => $permission,
				'args'                => array(
					'attachment_id' => array(
						'type'     => 'integer',
						'required' => true,
					),
				),
			)
		);
	}

	protected function get_profile_id( int $user_id ): int {
		$profile = get_posts(
			array(
				'post_type'      => 'artist_profile',
				'post_status'    => array( 'publish', 'draft', 'pending' ),
				'author'         => $user_id,
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);
		if ( $profile ) {
			return (int) $profile[0];
		}
		$userdata = get_userdata( $user_id );
		return wp_insert_post(
			array(
				'post_type'   => 'artist_profile',
				'post_status' => 'draft',
				'post_author' => $user_id,
				'post_title'  => $userdata ? $userdata->display_name : 'Artist',
			)
		);
	}

	public function get_portfolio( WP_REST_Request $request ): WP_REST_Response {
		$user_id    = get_current_user_id();
		$profile_id = $this->get_profile_id( $user_id );
		return \rest_ensure_response( $this->build_response( $profile_id ) );
	}

	public function add_item( WP_REST_Request $request ) {
		$user_id    = get_current_user_id();
		$profile_id = $this->get_profile_id( $user_id );
		$media_id   = absint( $request['media_id'] );
		$meta       = (array) $request->get_param( 'meta' );

		if ( (int) get_post_field( 'post_author', $profile_id ) !== $user_id ) {
			return new WP_Error( 'rest_forbidden', __( "You don't have permission to modify this resource.", 'artpulse' ), array( 'status' => 403 ) );
		}

		if ( $media_id < 1 || (int) get_post_field( 'post_author', $media_id ) !== $user_id ) {
			return new WP_Error( 'rest_forbidden', __( "You don't have permission to modify this resource.", 'artpulse' ), array( 'status' => 403 ) );
		}

		$alt = sanitize_text_field( $meta['alt'] ?? '' );
		if ( $alt === '' ) {
			return new WP_Error( 'invalid_alt', __( 'ALT text is required for accessibility.', 'artpulse' ), array( 'status' => 400 ) );
		}

		wp_update_post(
			array(
				'ID'           => $media_id,
				'post_title'   => sanitize_text_field( $meta['title'] ?? '' ),
				'post_excerpt' => sanitize_text_field( $meta['caption'] ?? '' ),
			)
		);
		update_post_meta( $media_id, '_wp_attachment_image_alt', $alt );
		update_post_meta( $media_id, 'ap_year', sanitize_text_field( $meta['year'] ?? '' ) );
		update_post_meta( $media_id, 'ap_medium', array_map( 'sanitize_text_field', (array) ( $meta['medium'] ?? array() ) ) );
		update_post_meta( $media_id, 'ap_tags', array_map( 'sanitize_text_field', (array) ( $meta['tags'] ?? array() ) ) );

		$order = get_post_meta( $profile_id, 'ap_portfolio_order', true );
		$order = is_array( $order ) ? array_map( 'intval', $order ) : array();
		if ( ! in_array( $media_id, $order, true ) ) {
			$order[] = $media_id;
			update_post_meta( $profile_id, 'ap_portfolio_order', $order );
		}

		return \rest_ensure_response( $this->build_response( $profile_id ) );
	}

	public function save_order( WP_REST_Request $request ) {
		$user_id    = get_current_user_id();
		$profile_id = $this->get_profile_id( $user_id );
		if ( (int) get_post_field( 'post_author', $profile_id ) !== $user_id ) {
			return new WP_Error( 'rest_forbidden', __( "You don't have permission to modify this resource.", 'artpulse' ), array( 'status' => 403 ) );
		}
		$order = array_map( 'intval', (array) $request['order'] );
		foreach ( $order as $att_id ) {
			if ( (int) get_post_field( 'post_author', $att_id ) !== $user_id ) {
				return new WP_Error( 'rest_forbidden', __( "You don't have permission to modify this resource.", 'artpulse' ), array( 'status' => 403 ) );
			}
		}
		update_post_meta( $profile_id, 'ap_portfolio_order', $order );
		return \rest_ensure_response( $this->build_response( $profile_id ) );
	}

	public function set_featured( WP_REST_Request $request ) {
		$user_id    = get_current_user_id();
		$profile_id = $this->get_profile_id( $user_id );
		$att_id     = absint( $request['attachment_id'] );
		if ( (int) get_post_field( 'post_author', $profile_id ) !== $user_id || (int) get_post_field( 'post_author', $att_id ) !== $user_id ) {
			return new WP_Error( 'rest_forbidden', __( "You don't have permission to modify this resource.", 'artpulse' ), array( 'status' => 403 ) );
		}
		update_post_meta( $profile_id, 'ap_portfolio_featured_id', $att_id );
		return \rest_ensure_response( $this->build_response( $profile_id ) );
	}

	protected function build_response( int $profile_id ): array {
		$order    = get_post_meta( $profile_id, 'ap_portfolio_order', true );
		$order    = is_array( $order ) ? array_map( 'intval', $order ) : array();
		$featured = (int) get_post_meta( $profile_id, 'ap_portfolio_featured_id', true );
		$items    = array();
		foreach ( $order as $att_id ) {
			$img     = wp_get_attachment_image_src( $att_id, 'large' );
			$items[] = array(
				'id'      => $att_id,
				'title'   => get_the_title( $att_id ),
				'alt'     => get_post_meta( $att_id, '_wp_attachment_image_alt', true ),
				'caption' => get_post_field( 'post_excerpt', $att_id ),
				'year'    => get_post_meta( $att_id, 'ap_year', true ),
				'medium'  => (array) get_post_meta( $att_id, 'ap_medium', true ),
				'tags'    => (array) get_post_meta( $att_id, 'ap_tags', true ),
				'src'     => $img[0] ?? '',
				'srcset'  => wp_get_attachment_image_srcset( $att_id, 'large' ) ?: '',
			);
		}
		return array(
			'profile_id'  => $profile_id,
			'featured_id' => $featured,
			'items'       => $items,
		);
	}
}
