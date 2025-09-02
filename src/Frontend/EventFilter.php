<?php
namespace ArtPulse\Frontend;

/**
 * Frontend event filter form and AJAX callback.
 */
class EventFilter {

	public static function register(): void {
		\ArtPulse\Core\ShortcodeRegistry::register( 'ap_event_filter', 'Event Filter', array( self::class, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue' ) );
		add_action( 'wp_ajax_ap_filter_events', __NAMESPACE__ . '\\ap_filter_events_callback' );
		add_action( 'wp_ajax_nopriv_ap_filter_events', __NAMESPACE__ . '\\ap_filter_events_callback' );
	}

	public static function enqueue(): void {
		if ( function_exists( 'ap_enqueue_global_styles' ) ) {
			ap_enqueue_global_styles();
		}
		wp_enqueue_style(
			'ap-event-filter-form',
			plugin_dir_url( ARTPULSE_PLUGIN_FILE ) . 'assets/css/ap-event-filter-form.css',
			array(),
			'1.0.0'
		);
		wp_enqueue_script(
			'ap-event-filter',
			plugin_dir_url( ARTPULSE_PLUGIN_FILE ) . 'assets/js/event-filters.js',
			array( 'jquery' ),
			'1.0.0',
			true
		);
		wp_localize_script(
			'ap-event-filter',
			'APEventFilter',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'ap_event_filter_nonce' ),
			)
		);
	}

	public static function render(): string {
		$event_types = get_terms(
			array(
				'taxonomy'   => 'event_type',
				'hide_empty' => false,
			)
		);
		if ( is_wp_error( $event_types ) ) {
			$event_types = array();
		}

		ob_start();
		?>
		<form id="ap-event-filter-form" class="ap-event-filter-form">
			<input type="text" name="keyword" placeholder="<?php esc_attr_e( 'Keyword', 'artpulse' ); ?>" />
			<input type="text" name="venue" placeholder="<?php esc_attr_e( 'Venue', 'artpulse' ); ?>" />
			<input type="date" name="after" />
			<input type="date" name="before" />
			<input type="text" name="category" placeholder="<?php esc_attr_e( 'Category', 'artpulse' ); ?>" />
			<select name="event_type">
				<option value=""><?php esc_html_e( 'All Types', 'artpulse' ); ?></option>
				<?php foreach ( $event_types as $type ) : ?>
					<option value="<?php echo esc_attr( $type->slug ); ?>"><?php echo esc_html( $type->name ); ?></option>
				<?php endforeach; ?>
			</select>
			<input type="text" name="tags" placeholder="<?php esc_attr_e( 'Tags', 'artpulse' ); ?>" />
			<select name="price_type">
				<option value=""><?php esc_html_e( 'Any Price', 'artpulse' ); ?></option>
				<option value="free"><?php esc_html_e( 'Free', 'artpulse' ); ?></option>
				<option value="paid"><?php esc_html_e( 'Paid', 'artpulse' ); ?></option>
			</select>
			<input type="text" name="location" placeholder="<?php esc_attr_e( 'Lat,Lon', 'artpulse' ); ?>" />
			<input type="number" step="0.1" name="radius" placeholder="<?php esc_attr_e( 'Radius', 'artpulse' ); ?>" />
			<button type="submit" class="ap-form-button"><?php esc_html_e( 'Filter', 'artpulse' ); ?></button>
		</form>
		<div id="ap-event-filter-results" class="ap-directory-results" role="status" aria-live="polite"></div>
		<?php
		return ob_get_clean();
	}
}

function ap_filter_events_callback(): void {
        check_ajax_referer( 'ap_event_filter_nonce', 'nonce' );
	$venue      = sanitize_text_field( $_REQUEST['venue'] ?? '' );
	$after      = sanitize_text_field( $_REQUEST['after'] ?? '' );
	$before     = sanitize_text_field( $_REQUEST['before'] ?? '' );
	$category   = sanitize_text_field( $_REQUEST['category'] ?? '' );
	$event_type = sanitize_text_field( $_REQUEST['event_type'] ?? '' );
	$tags       = sanitize_text_field( $_REQUEST['tags'] ?? '' );
	$price_type = sanitize_text_field( $_REQUEST['price_type'] ?? '' );
	$location   = sanitize_text_field( $_REQUEST['location'] ?? '' );
	$radius     = sanitize_text_field( $_REQUEST['radius'] ?? '' );
	$categories = array_map( 'sanitize_key', array_map( 'trim', explode( ',', $category ) ) );
	$tag_terms  = array_map( 'sanitize_key', array_map( 'trim', explode( ',', $tags ) ) );
	$keyword    = sanitize_text_field( $_REQUEST['keyword'] ?? '' );

	$meta_query = array();
	if ( $venue !== '' ) {
		$meta_query[] = array(
			'key'     => 'venue_name',
			'value'   => $venue,
			'compare' => 'LIKE',
		);
	}
	if ( $after !== '' ) {
		$meta_query[] = array(
			'key'     => 'event_start_date',
			'value'   => $after,
			'type'    => 'DATE',
			'compare' => '>=',
		);
	}
	if ( $before !== '' ) {
		$meta_query[] = array(
			'key'     => 'event_end_date',
			'value'   => $before,
			'type'    => 'DATE',
			'compare' => '<=',
		);
	}
	if ( $price_type !== '' ) {
		$meta_query[] = array(
			'key'   => 'price_type',
			'value' => $price_type,
		);
	}
	if ( $location !== '' && is_numeric( $radius ) ) {
		[$lat, $lng] = array_pad( array_map( 'floatval', explode( ',', $location ) ), 2, 0.0 );
		$r           = floatval( $radius );
		if ( $lat && $lng && $r > 0 ) {
			$meta_query[] = array(
				'key'     => 'event_lat',
				'value'   => array( $lat - $r, $lat + $r ),
				'compare' => 'BETWEEN',
				'type'    => 'numeric',
			);
			$meta_query[] = array(
				'key'     => 'event_lng',
				'value'   => array( $lng - $r, $lng + $r ),
				'compare' => 'BETWEEN',
				'type'    => 'numeric',
			);
		}
	}

	$tax_query = array();
	if ( $category !== '' ) {
		$tax_query[] = array(
			'taxonomy' => 'category',
			'field'    => 'slug',
			'terms'    => $categories,
		);
	}
	if ( $event_type !== '' ) {
		$tax_query[] = array(
			'taxonomy' => 'event_type',
			'field'    => 'slug',
			'terms'    => array( $event_type ),
		);
	}
	if ( $tags !== '' ) {
		$tax_query[] = array(
			'taxonomy' => 'post_tag',
			'field'    => 'slug',
			'terms'    => $tag_terms,
		);
	}

	$args = array(
		'post_type'      => 'artpulse_event',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	);
	if ( $keyword !== '' ) {
		$args['s'] = $keyword;
	}
	if ( $meta_query ) {
		$args['meta_query'] = $meta_query;
	}
	if ( $tax_query ) {
		$args['tax_query'] = $tax_query;
	}

	$query = new \WP_Query( $args );

	ob_start();
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			echo ap_get_event_card( get_the_ID() );
		}
		wp_reset_postdata();
	} else {
		echo '<div class="ap-empty">' . esc_html__( 'No events found.', 'artpulse' ) . '</div>';
	}
	$html = ob_get_clean();
        echo $html;
        wp_die( '', 200 );
}
