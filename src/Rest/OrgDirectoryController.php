<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use WP_REST_Server;
use ArtPulse\Rest\RestResponder;

/**
 * REST controller for organization directory listings.
 */
final class OrgDirectoryController {
		use RestResponder;

	public static function register(): void {
		$controller = new self();
		if ( did_action( 'rest_api_init' ) ) {
			$controller->register_routes();
		} else {
			add_action( 'rest_api_init', array( $controller, 'register_routes' ) );
		}
	}

	public function register_routes(): void {
		register_rest_route(
			ARTPULSE_API_NAMESPACE,
			'/directory/orgs',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_orgs' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'org_type' => array( 'type' => 'string' ),
				),
			)
		);
	}

	public function get_orgs( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$args = array(
			'post_type'      => 'artpulse_org',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		);

		$org_type = sanitize_text_field( $request->get_param( 'org_type' ) );
		if ( $org_type !== '' ) {
			$args['meta_query'][] = array(
				'key'   => 'ead_org_type',
				'value' => $org_type,
			);
		}

		$posts = get_posts( $args );
		$data  = array_map(
			function ( $p ) {
				$logo_id  = get_post_meta( $p->ID, 'ead_org_logo_id', true );
				$featured = $logo_id ? wp_get_attachment_url( $logo_id ) : get_the_post_thumbnail_url( $p, 'medium' );
				return array(
					'id'                 => $p->ID,
					'title'              => $p->post_title,
					'featured_media_url' => $featured,
					'address'            => get_post_meta( $p->ID, 'ead_org_street_address', true ),
					'website'            => get_post_meta( $p->ID, 'ead_org_website_url', true ),
					'org_type'           => get_post_meta( $p->ID, 'ead_org_type', true ),
				);
			},
			$posts
		);

		return $this->ok( $data );
	}
}
