<?php
namespace ArtPulse\Core;

use WP_REST_Request;
use ArtPulse\Search\ExternalSearch;

class DirectoryManager {
	public static function register() {
		ShortcodeRegistry::register( 'ap_directory', 'Legacy Directory', array( self::class, 'renderDirectory' ) );
		ShortcodeRegistry::register( 'ap_event_directory', 'Event Directory', array( self::class, 'renderEventDirectory' ) );
		ShortcodeRegistry::register( 'ap_artist_directory', 'Artist Directory', array( self::class, 'renderArtistDirectory' ) );
		ShortcodeRegistry::register( 'ap_artwork_directory', 'Artwork Directory', array( self::class, 'renderArtworkDirectory' ) );
		ShortcodeRegistry::register( 'ap_org_directory', 'Organization Directory', array( self::class, 'renderOrgDirectory' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueueAssets' ) );
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
		add_action( 'save_post', array( self::class, 'clear_cache' ), 10, 3 );
		add_action( 'deleted_post', array( self::class, 'clear_cache' ) );
	}

	public static function enqueueAssets() {
               \ArtPulse\Admin\EnqueueAssets::enqueue_script_if_exists( 'ap-directory-js', 'assets/js/ap-directory.js', array( 'wp-api-fetch' ) );
               \ArtPulse\Admin\EnqueueAssets::enqueue_script_if_exists( 'ap-analytics-js', 'assets/js/ap-analytics.js', array( 'ap-directory-js' ), true, array( 'type' => 'module' ) );
		wp_localize_script(
			'ap-directory-js',
			'ArtPulseApi',
			array(
				'root'  => esc_url_raw( rest_url() ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
			)
		);
		if ( function_exists( 'ap_enqueue_global_styles' ) ) {
			add_filter( 'ap_bypass_shortcode_detection', '__return_true' );
			ap_enqueue_global_styles();
		}
	}

	public static function register_routes() {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/filter' ) ) {
				register_rest_route(
                                       ARTPULSE_API_NAMESPACE,
                                       '/filter',
                                       array(
                                               'methods'             => 'GET',
                                               'callback'            => array( self::class, 'handleFilter' ),
                                               'permission_callback' => function () {
                                                       return \ArtPulse\Rest\Util\Auth::require_cap( 'read' );
                                               },
                                               'args'                => array(
                                                       'type'         => array(
                                                               'type'     => 'string',
                                                               'required' => true,
                                                       ),
						'limit'        => array(
							'type'    => 'integer',
							'default' => 10,
						),
						'event_type'   => array( 'type' => 'integer' ),
						'medium'       => array( 'type' => 'integer' ),
						'style'        => array( 'type' => 'integer' ),
						'org_type'     => array( 'type' => 'string' ),
						'location'     => array( 'type' => 'string' ),
						'city'         => array( 'type' => 'string' ),
						'region'       => array( 'type' => 'string' ),
						'for_sale'     => array( 'type' => 'boolean' ),
						'keyword'      => array( 'type' => 'string' ),
						'first_letter' => array( 'type' => 'string' ),
						'page'         => array(
							'type'    => 'integer',
							'default' => 1,
						),
					),
				)
			);
		}
	}

	public static function handleFilter( WP_REST_Request $request ) {
		$type         = sanitize_text_field( $request->get_param( 'type' ) );
		$limit        = intval( $request->get_param( 'limit' ) ?? 10 );
		$page         = max( 1, intval( $request->get_param( 'page' ) ?? 1 ) );
		$event_type   = absint( $request->get_param( 'event_type' ) );
		$medium       = absint( $request->get_param( 'medium' ) );
		$style        = absint( $request->get_param( 'style' ) );
		$org_type     = sanitize_text_field( $request->get_param( 'org_type' ) );
		$location     = sanitize_text_field( $request->get_param( 'location' ) );
		$city         = sanitize_text_field( $request->get_param( 'city' ) );
		$region       = sanitize_text_field( $request->get_param( 'region' ) );
		$for_sale     = $request->has_param( 'for_sale' ) ? rest_sanitize_boolean( $request->get_param( 'for_sale' ) ) : null;
		$keyword      = sanitize_text_field( $request->get_param( 'keyword' ) );
		$first_letter = sanitize_text_field( $request->get_param( 'first_letter' ) );

               $allowed = array( 'event', 'artist', 'artwork', 'org' );
               if ( ! in_array( $type, $allowed, true ) ) {
                       return new \WP_Error( 'invalid_type', 'Invalid directory type', array( 'status' => 400 ) );
               }

               $cache_args = array(
                       'type'         => $type,
                       'limit'        => $limit,
                       'event_type'   => $event_type,
                       'medium'       => $medium,
                       'style'        => $style,
                       'org_type'     => $org_type,
                       'location'     => $location,
                       'city'         => $city,
                       'region'       => $region,
                       'for_sale'     => $for_sale,
                       'keyword'      => $keyword,
                       'first_letter' => $first_letter,
               );

               $cache_key = self::get_cache_key( $cache_args );
               $cached    = get_transient( $cache_key );
               if ( $cached !== false ) {
                       return \rest_ensure_response( $cached );
               }

               $args       = array(
                       'post_type'      => 'artpulse_' . $type,
                       'posts_per_page' => $limit,
                       'paged'          => $page,
                       'orderby'        => 'title',
                       'order'          => 'ASC',
               );
		$tax_query  = array();
		$meta_query = array();

               $search_args = array(
                       'limit'        => $limit,
                       'event_type'   => $event_type,
                       'medium'       => $medium,
                       'style'        => $style,
                       'org_type'     => $org_type,
                       'location'     => $location,
                       'city'         => $city,
                       'region'       => $region,
                       'for_sale'     => $for_sale,
                       'keyword'      => $keyword,
                       'first_letter' => $first_letter,
                       'page'         => $page,
               );

		if ( ExternalSearch::is_enabled() ) {
			$posts = ExternalSearch::search( $type, $search_args );
		} else {
			if ( $type === 'event' ) {
                                if ( $event_type ) {
                                        $tax_query[] = array(
                                                'taxonomy' => 'event_type',
                                                'field'    => 'term_id',
                                                'terms'    => $event_type,
					);
				}

				if ( $city ) {
					$meta_query[] = array(
						'key'   => 'event_city',
						'value' => $city,
					);
				}

				if ( $region ) {
					$meta_query[] = array(
						'key'   => 'event_state',
						'value' => $region,
					);
				}
			}

			if ( $type === 'artwork' && $for_sale !== null ) {
				$meta_query[] = array(
					'key'     => 'for_sale',
					'value'   => $for_sale ? '1' : '0',
					'compare' => '=',
				);
			}

			if ( $medium ) {
				$tax_query[] = array(
					'taxonomy' => $type === 'artist' ? 'artist_specialty' : 'artpulse_medium',
					'field'    => 'term_id',
					'terms'    => $medium,
				);
			}

			if ( $style ) {
				$tax_query[] = array(
					'taxonomy' => 'artwork_style',
					'field'    => 'term_id',
					'terms'    => $style,
				);
			}

			if ( $type === 'org' && $org_type ) {
				$meta_query[] = array(
					'key'   => 'ead_org_type',
					'value' => $org_type,
				);
			}

			if ( $location ) {
				$meta_query[] = array(
					'key'     => 'address_components',
					'value'   => $location,
					'compare' => 'LIKE',
				);
			}

			if ( $keyword ) {
				$args['s'] = $keyword;
			}

			if ( ! empty( $tax_query ) ) {
				$args['tax_query'] = $tax_query;
			}

			if ( ! empty( $meta_query ) ) {
				$args['meta_query'] = $meta_query;
			}

			$posts = get_posts( $args );
		}

		$posts = self::filter_by_first_letter( $posts, $first_letter );

		$data = array_map(
			function ( $p ) use ( $type ) {
				$featured = get_the_post_thumbnail_url( $p, 'medium' );
				if ( $type === 'org' && empty( $featured ) ) {
					$logo_id       = get_post_meta( $p->ID, 'ead_org_logo_id', true );
					$banner_id     = get_post_meta( $p->ID, 'ead_org_banner_id', true );
					$attachment_id = $logo_id ?: $banner_id;
                                        if ( $attachment_id ) {
                                                $featured = wp_get_attachment_url( $attachment_id );
                                        }
                                }

				$item = array(
					'id'                 => $p->ID,
					'title'              => $p->post_title,
					'link'               => get_permalink( $p ),
					'featured_media_url' => $featured,
				);
				if ( $type === 'event' ) {
					$item['date']      = get_post_meta( $p->ID, '_ap_event_date', true );
					$item['location']  = get_post_meta( $p->ID, '_ap_event_location', true );
					$item['card_html'] = ap_get_event_card( $p->ID );
				} elseif ( $type === 'artist' ) {
					$item['bio']    = get_post_meta( $p->ID, '_ap_artist_bio', true );
					$item['org_id'] = (int) get_post_meta( $p->ID, '_ap_artist_org', true );
					$medium_terms   = get_the_terms( $p->ID, 'artist_specialty' );
					$style_terms    = get_the_terms( $p->ID, 'artwork_style' );
					$item['medium'] = $medium_terms && ! is_wp_error( $medium_terms ) ? wp_list_pluck( $medium_terms, 'name' ) : array();
					$item['style']  = $style_terms && ! is_wp_error( $style_terms ) ? wp_list_pluck( $style_terms, 'name' ) : array();
				} elseif ( $type === 'artwork' ) {
					$item['medium'] = get_post_meta( $p->ID, '_ap_artwork_medium', true );
					$terms          = get_the_terms( $p->ID, 'artwork_style' );
					if ( $terms && ! is_wp_error( $terms ) ) {
						$names         = wp_list_pluck( $terms, 'name' );
						$item['style'] = implode( ', ', $names );
					} else {
						$item['style'] = '';
					}
					$item['dimensions'] = get_post_meta( $p->ID, '_ap_artwork_dimensions', true );
					$item['materials']  = get_post_meta( $p->ID, '_ap_artwork_materials', true );
					$item['for_sale']   = (bool) get_post_meta( $p->ID, 'for_sale', true );
					$item['price']      = get_post_meta( $p->ID, 'price', true );
				} elseif ( $type === 'org' ) {
					$item['address']  = get_post_meta( $p->ID, 'ead_org_street_address', true );
					$item['website']  = get_post_meta( $p->ID, 'ead_org_website_url', true );
					$item['org_type'] = get_post_meta( $p->ID, 'ead_org_type', true );
				}
				return $item;
			},
			$posts
		);

		set_transient( $cache_key, $data, 5 * MINUTE_IN_SECONDS );

		return \rest_ensure_response( $data );
       }

	public static function renderDirectory( $atts ) {
		$atts = shortcode_atts(
			array(
				'type'  => 'event',
				'limit' => 10,
			),
			$atts,
			'ap_directory'
		);

		$server_results = self::server_results( $atts );
		ob_start(); ?>
		<div class="ap-directory" data-type="<?php echo esc_attr( $atts['type'] ); ?>" data-limit="<?php echo esc_attr( $atts['limit'] ); ?>">
			<form class="ap-directory-filters" method="get" aria-controls="ap-directory-results">
				<?php if ( $atts['type'] === 'event' ) : ?>
					<label for="ap-filter-event-type"><?php _e( 'Filter by Event Type', 'artpulse' ); ?>:</label>
					<select id="ap-filter-event-type" name="event_type" class="ap-filter-event-type"></select>
					<label for="ap-filter-city" class="screen-reader-text"><?php esc_html_e( 'City', 'artpulse' ); ?></label>
					<input id="ap-filter-city" name="city" type="text" class="ap-filter-city" placeholder="<?php esc_attr_e( 'City', 'artpulse' ); ?>" />
					<label for="ap-filter-region" class="screen-reader-text"><?php esc_html_e( 'Region', 'artpulse' ); ?></label>
					<input id="ap-filter-region" name="region" type="text" class="ap-filter-region" placeholder="<?php esc_attr_e( 'Region', 'artpulse' ); ?>" />
				<?php endif; ?>
				<?php if ( $atts['type'] === 'org' ) : ?>
					<label for="ap-filter-org-type"><?php _e( 'Organization Type', 'artpulse' ); ?>:</label>
					<select id="ap-filter-org-type" name="org_type" class="ap-filter-org-type">
						<option value=""><?php esc_html_e( 'All', 'artpulse' ); ?></option>
						<?php
						foreach ( array( 'gallery', 'museum', 'art-fair', 'studio', 'collective', 'non-profit', 'commercial-gallery', 'public-art-space', 'educational-institution', 'other' ) as $t ) {
							echo '<option value="' . esc_attr( $t ) . '">' . esc_html( ucfirst( str_replace( '-', ' ', $t ) ) ) . '</option>';
						}
						?>
					</select>
				<?php else : ?>
					<label for="ap-filter-medium"><?php _e( 'Medium', 'artpulse' ); ?>:</label>
					<select id="ap-filter-medium" name="medium" class="ap-filter-medium"></select>
					<label for="ap-filter-style"><?php _e( 'Style', 'artpulse' ); ?>:</label>
					<select id="ap-filter-style" name="style" class="ap-filter-style"></select>
				<?php endif; ?>
				<label for="ap-filter-location" class="screen-reader-text"><?php esc_html_e( 'Location', 'artpulse' ); ?></label>
				<input id="ap-filter-location" name="location" type="text" class="ap-filter-location" placeholder="<?php esc_attr_e( 'Location', 'artpulse' ); ?>" />
				<label for="ap-filter-keyword" class="screen-reader-text"><?php esc_html_e( 'Keyword', 'artpulse' ); ?></label>
				<input id="ap-filter-keyword" name="keyword" type="text" class="ap-filter-keyword" placeholder="<?php esc_attr_e( 'Keyword', 'artpulse' ); ?>" />
				<label for="ap-filter-limit"><?php _e( 'Limit', 'artpulse' ); ?>:</label>
				<input id="ap-filter-limit" name="limit" type="number" class="ap-filter-limit" value="<?php echo esc_attr( $atts['limit'] ); ?>" />
				<button class="ap-filter-apply"><?php _e( 'Apply', 'artpulse' ); ?></button>
			</form>
			<nav class="ap-alpha-bar" aria-label="<?php esc_attr_e( 'Alphabet Filter', 'artpulse' ); ?>">
				<?php foreach ( range( 'A', 'Z' ) as $ch ) : ?>
					<button type="button" data-letter="<?php echo $ch; ?>"><?php echo $ch; ?></button>
				<?php endforeach; ?>
				<button type="button" data-letter="#">#</button>
			</nav>
			<div id="ap-directory-results" class="ap-directory-results" role="status" aria-live="polite">
				<?php echo $server_results; ?>
			</div>
			<button class="ap-load-more" style="display:none;" type="button"><?php esc_html_e( 'Load More', 'artpulse' ); ?></button>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function renderEventDirectory( $atts ) {
		$atts['type'] = 'event';
		return self::renderDirectory( $atts );
	}

	public static function renderArtistDirectory( $atts ) {
		$atts['type'] = 'artist';
		return self::renderDirectory( $atts );
	}

	public static function renderArtworkDirectory( $atts ) {
		$atts['type'] = 'artwork';
		return self::renderDirectory( $atts );
	}

	public static function renderOrgDirectory( $atts ) {
		$atts['type'] = 'org';
		return self::renderDirectory( $atts );
	}

	/**
	 * Generate HTML results for the first directory page.
	 *
	 * Hooks:
	 * - `ap_directory_query_args` filters the WP_Query arguments.
	 * - `ap_directory_card_html` filters each rendered card HTML.
	 */
	private static function server_results( array $atts ): string {
		$type  = sanitize_key( $atts['type'] );
		$limit = intval( $atts['limit'] );
		$paged = max( 1, intval( $_GET['ap_page'] ?? 1 ) );

		$args = array(
			'post_type'              => 'artpulse_' . $type,
			'posts_per_page'         => $limit,
			'paged'                  => $paged,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		);

		if ( $type === 'event' ) {
			if ( ! empty( $_GET['event_type'] ) ) {
				$args['tax_query'][] = array(
					'taxonomy' => 'event_type',
					'field'    => 'term_id',
					'terms'    => absint( $_GET['event_type'] ),
				);
			}
			if ( ! empty( $_GET['city'] ) ) {
				$args['meta_query'][] = array(
					'key'   => 'event_city',
					'value' => sanitize_text_field( $_GET['city'] ),
				);
			}
			if ( ! empty( $_GET['region'] ) ) {
				$args['meta_query'][] = array(
					'key'   => 'event_state',
					'value' => sanitize_text_field( $_GET['region'] ),
				);
			}
		}

		$args = apply_filters( 'ap_directory_query_args', $args, $atts );

		$query = new \WP_Query( $args );

		ob_start();
		$ids = array();
		if ( $query->have_posts() ) {
			foreach ( $query->posts as $id ) {
				$html = ap_get_event_card( $id );
				echo apply_filters( 'ap_directory_card_html', $html, $id, $type );
				$ids[] = $id;
			}

			echo paginate_links(
				array(
					'total'    => $query->max_num_pages,
					'current'  => $paged,
					'add_args' => false,
					'format'   => '?ap_page=%#%',
					'type'     => 'list',
				)
			);
		} else {
			echo '<p>' . esc_html__( 'No results found.', 'artpulse' ) . '</p>';
		}
		if ( $ids ) {
			$schema = \ArtPulse\SEO\JsonLdGenerator::directory_schema( $ids );
			echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>';
		}
		wp_reset_postdata();
		return ob_get_clean();
	}

	/**
	 * Filter a list of posts by first letter of the title.
	 *
	 * @param array  $posts        List of WP_Post objects.
	 * @param string $first_letter Desired starting character.
	 * @return array
	 */
	private static function filter_by_first_letter( array $posts, string $first_letter ): array {
		$first_letter = trim( $first_letter );
		if ( $first_letter === '' ) {
			return $posts;
		}

		return array_values(
			array_filter(
				$posts,
				function ( $p ) use ( $first_letter ) {
					$letter = mb_substr( $p->post_title, 0, 1, 'UTF-8' );
					if ( $first_letter === '#' ) {
						return ! preg_match( '/[A-Za-z]/u', $letter );
					}
					return mb_strtoupper( $letter, 'UTF-8' ) === mb_strtoupper( $first_letter, 'UTF-8' );
				}
			)
		);
	}

	/**
	 * Generate a cache key for filter requests.
	 *
	 * @param array<string,mixed> $params Request parameters.
	 * @return string
	 */
	public static function get_cache_key( array $params ): string {
		ksort( $params );
		return 'ap_dir_' . md5( serialize( $params ) );
	}

	/**
	 * Clear all directory filter transients when a related post is updated.
	 */
	public static function clear_cache( int $post_id, ?\WP_Post $post = null, bool $update = true ): void {
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( ! $post ) {
			$post = get_post( $post_id );
		}

		if ( $post && in_array( $post->post_type, array( 'artpulse_event', 'artpulse_artist', 'artpulse_artwork', 'artpulse_org' ), true ) ) {
			global $wpdb;
			$like       = $wpdb->esc_like( '_transient_ap_dir_' ) . '%';
			$transients = $wpdb->get_col( $wpdb->prepare( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s", $like ) );

			foreach ( $transients as $transient ) {
				$transient = substr( $transient, strlen( '_transient_' ) );
				delete_transient( $transient );
			}
		}
       }
}
