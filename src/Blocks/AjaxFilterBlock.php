<?php
namespace ArtPulse\Blocks;

class AjaxFilterBlock {

	public static function register() {
		add_action( 'init', array( self::class, 'register_block' ) );
		add_action( 'rest_api_init', array( self::class, 'register_rest_routes' ) );
	}

	public static function register_block() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type(
			'artpulse/ajax-filter',
			array(
				'editor_script'   => 'artpulse-ajax-filter-block',
				'render_callback' => array( self::class, 'render_callback' ),
				'attributes'      => array(
					'postType' => array(
						'type'    => 'string',
						'default' => 'artpulse_artist',
					),
					'taxonomy' => array(
						'type'    => 'string',
						'default' => 'artist_specialty',
					),
				),
			)
		);

               $path = __DIR__ . '/../../assets/js/ajax-filter-block.js';
               $version = \ArtPulse\Blocks\ap_block_version();
               $ver  = file_exists( $path ) ? filemtime( $path ) : $version;
               wp_register_script(
                       'artpulse-ajax-filter-block',
                       plugins_url( 'assets/js/ajax-filter-block.js', ARTPULSE_PLUGIN_FILE ),
                       array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor', 'wp-api-fetch' ),
                       $ver
               );
       }

	public static function register_rest_routes() {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/filtered-posts' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/filtered-posts',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'rest_filtered_posts' ),
					'permission_callback' => fn() => is_user_logged_in(),
					'args'                => array(
						'post_type' => array( 'required' => true ),
						'taxonomy'  => array( 'required' => true ),
						'terms'     => array( 'required' => false ),
						'per_page'  => array(
							'required' => false,
							'default'  => 5,
						),
						'page'      => array(
							'required' => false,
							'default'  => 1,
						),
					),
				)
			);
		}
	}

	public static function rest_filtered_posts( \WP_REST_Request $request ) {
		$post_type = sanitize_text_field( $request->get_param( 'post_type' ) );
		$taxonomy  = sanitize_text_field( $request->get_param( 'taxonomy' ) );
		$terms     = $request->get_param( 'terms' );
		$per_page  = intval( $request->get_param( 'per_page' ) );
		$page      = intval( $request->get_param( 'page' ) );

		$args = array(
			'post_type'      => $post_type,
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'post_status'    => 'publish',
		);

		if ( ! empty( $terms ) ) {
			$terms_array       = explode( ',', sanitize_text_field( $terms ) );
			$args['tax_query'] = array(
				array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => $terms_array,
				),
			);
		}

		// Keep found rows for pagination but retrieve IDs only.
		$args['fields'] = 'ids';
		$query          = new \WP_Query( $args );

		$posts = array();
		foreach ( $query->posts as $post_id ) {
			$posts[] = array(
				'id'    => $post_id,
				'title' => get_the_title( $post_id ),
				'link'  => get_permalink( $post_id ),
			);
		}

		return array(
			'posts'      => $posts,
			'total'      => (int) $query->found_posts,
			'totalPages' => (int) $query->max_num_pages,
		);
	}

	public static function render_callback( $attributes ) {
		return '<div class="artpulse-ajax-filter-block" data-post-type="' . esc_attr( $attributes['postType'] ) . '" data-taxonomy="' . esc_attr( $attributes['taxonomy'] ) . '"></div>';
	}
}
