<?php
namespace ArtPulse\Core;

class ShortcodeManager {

	public static function register() {
		ShortcodeRegistry::register( 'ap_events', 'Events List', array( self::class, 'renderEvents' ) );
		ShortcodeRegistry::register( 'ap_artists', 'Artists List', array( self::class, 'renderArtists' ) );
		ShortcodeRegistry::register( 'ap_artworks', 'Artworks List', array( self::class, 'renderArtworks' ) );
		ShortcodeRegistry::register( 'ap_organizations', 'Organizations List', array( self::class, 'renderOrganizations' ) );
		ShortcodeRegistry::register( 'ap_spotlights', 'Spotlights', array( self::class, 'renderSpotlights' ) );
	}

	public static function renderEvents( $atts ) {
		$atts  = shortcode_atts( array( 'limit' => 10 ), $atts, 'ap_events' );
		$query = new \WP_Query(
			array(
				'post_type'      => 'artpulse_event',
				'posts_per_page' => intval( $atts['limit'] ),
				// Fetch IDs only to reduce memory footprint.
				'fields'         => 'ids',
				// No pagination required so skip FOUND_ROWS calculation.
				'no_found_rows'  => true,
			)
		);
		ob_start();
		echo '<div class="ap-portfolio-grid">';
		foreach ( $query->posts as $post_id ) {
			echo ap_get_event_card( $post_id );
		}
		echo '</div>';
		return ob_get_clean();
	}

	public static function renderArtists( $atts ) {
		$atts  = shortcode_atts( array( 'limit' => 10 ), $atts, 'ap_artists' );
		$query = new \WP_Query(
			array(
				'post_type'      => 'artpulse_artist',
				'posts_per_page' => intval( $atts['limit'] ),
				// Fetch IDs only to reduce memory footprint.
				'fields'         => 'ids',
				// No pagination required so skip FOUND_ROWS calculation.
				'no_found_rows'  => true,
			)
		);
		ob_start();
		echo '<div class="ap-portfolio-grid">';
		foreach ( $query->posts as $post_id ) {
			echo '<div class="portfolio-item">';
			echo get_the_post_thumbnail( $post_id, 'medium' );
			echo '<h3><a href="' . esc_url( get_permalink( $post_id ) ) . '">' . esc_html( get_the_title( $post_id ) ) . '</a></h3>';
			echo '</div>';
		}
		echo '</div>';
		return ob_get_clean();
	}

	public static function renderArtworks( $atts ) {
		$atts  = shortcode_atts( array( 'limit' => 10 ), $atts, 'ap_artworks' );
		$query = new \WP_Query(
			array(
				'post_type'      => 'artpulse_artwork',
				'posts_per_page' => intval( $atts['limit'] ),
				// Fetch IDs only to reduce memory footprint.
				'fields'         => 'ids',
				// No pagination required so skip FOUND_ROWS calculation.
				'no_found_rows'  => true,
			)
		);
		ob_start();
		echo '<div class="ap-portfolio-grid">';
		foreach ( $query->posts as $post_id ) {
			echo '<div class="portfolio-item">';
			echo get_the_post_thumbnail( $post_id, 'medium' );
			echo '<h3><a href="' . esc_url( get_permalink( $post_id ) ) . '">' . esc_html( get_the_title( $post_id ) ) . '</a></h3>';
			echo '</div>';
		}
		echo '</div>';
		return ob_get_clean();
	}

	public static function renderOrganizations( $atts ) {
		$atts  = shortcode_atts( array( 'limit' => 10 ), $atts, 'ap_organizations' );
		$query = new \WP_Query(
			array(
				'post_type'      => 'artpulse_org',
				'posts_per_page' => intval( $atts['limit'] ),
				// Fetch IDs only to reduce memory footprint.
				'fields'         => 'ids',
				// No pagination required so skip FOUND_ROWS calculation.
				'no_found_rows'  => true,
			)
		);
		ob_start();
		echo '<div class="ap-portfolio-grid">';
		foreach ( $query->posts as $post_id ) {
			echo '<div class="portfolio-item">';
			echo get_the_post_thumbnail( $post_id, 'medium' );
			echo '<h3><a href="' . esc_url( get_permalink( $post_id ) ) . '">' . esc_html( get_the_title( $post_id ) ) . '</a></h3>';
			echo '</div>';
		}
		echo '</div>';
		return ob_get_clean();
	}

	public static function renderSpotlights( $atts ) {
		$atts = shortcode_atts( array( 'limit' => 5 ), $atts, 'ap_spotlights' );

		$today = current_time( 'Y-m-d' );
		$query = new \WP_Query(
			array(
				'post_type'      => 'artpulse_artist',
				'posts_per_page' => intval( $atts['limit'] ),
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_query'     => array(
					array(
						'key'   => 'artist_spotlight',
						'value' => '1',
					),
					array(
						'key'     => 'spotlight_start_date',
						'value'   => $today,
						'compare' => '<=',
						'type'    => 'DATE',
					),
					array(
						'relation' => 'OR',
						array(
							'key'     => 'spotlight_end_date',
							'value'   => $today,
							'compare' => '>=',
							'type'    => 'DATE',
						),
						array(
							'key'     => 'spotlight_end_date',
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'     => 'spotlight_end_date',
							'value'   => '',
							'compare' => '=',
						),
					),
				),
			)
		);

		ob_start();
		echo '<div class="ap-spotlights">';
		foreach ( $query->posts as $post_id ) {
			set_query_var( 'post', get_post( $post_id ) );
			$template_path = plugin_dir_path( __FILE__ ) . '../../templates/partials/content-artpulse-item.php';
			if ( file_exists( $template_path ) ) {
				include $template_path;
			} else {
                                printf( '<a href="%1$s">%2$s</a>', esc_url( get_permalink( $post_id ) ), esc_html( get_the_title( $post_id ) ) );
                        }
                }
                echo '</div>';
                return ob_get_clean();
	}
}
