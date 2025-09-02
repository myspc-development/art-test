<?php
namespace ArtPulse\Frontend;

class EventListingShortcode {

        public static function register(): void {
                \ArtPulse\Core\ShortcodeRegistry::register( 'ap_event_listing', 'Event Listing', array( self::class, 'render' ) );
                add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue' ) );
                add_action( 'init', array( self::class, 'maybe_register_event_category' ) );
        }

       public static function maybe_register_event_category(): void {
               if ( ! \taxonomy_exists( 'event_category' ) ) {
                       \register_taxonomy(
                               'event_category',
                               'artpulse_event',
                               array(
                                       'label'  => 'Event Categories',
                                       'public' => true,
                               )
                       );
               }
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
		wp_enqueue_style(
			'ap-event-listing',
			plugin_dir_url( ARTPULSE_PLUGIN_FILE ) . 'assets/css/event-listing.css',
			array(),
			'1.0.0'
		);
		wp_enqueue_script(
			'ap-event-listing',
			plugin_dir_url( ARTPULSE_PLUGIN_FILE ) . 'assets/js/event-listing.js',
			array( 'wp-api-fetch' ),
			'1.0.0',
			true
		);
		wp_localize_script(
			'ap-event-listing',
			'APEventListing',
			array(
				'root'  => esc_url_raw( rest_url() ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

       public static function render( $atts ): string {
               self::maybe_register_event_category();
               $atts = shortcode_atts(
			array(
				'posts_per_page' => 12,
			),
			$atts,
			'ap_event_listing'
		);

               $event_types = \get_terms(
                       'event_type',
                       array(
                               'hide_empty' => false,
                       )
               );
		if ( is_wp_error( $event_types ) ) {
			$event_types = array();
		}

               $categories = \get_terms(
                       'event_category',
                       array(
                               'hide_empty' => false,
                       )
               );

               if ( is_wp_error( $categories ) ) {
                       $categories = array();
               }

               ob_start();
		?>
		<div class="ap-event-listing-wrapper" data-per-page="<?php echo intval( $atts['posts_per_page'] ); ?>">
			<nav class="ap-alpha-bar" aria-label="<?php esc_attr_e( 'Filter by alphabet', 'artpulse' ); ?>"></nav>
			<form id="ap-event-listing-form" class="ap-event-filter-form" autocomplete="off">
				<input type="text" name="venue" placeholder="<?php esc_attr_e( 'Venue', 'artpulse' ); ?>">
				<input type="date" name="after">
				<input type="date" name="before">
				<select name="category">
					<option value=""><?php esc_html_e( 'All Categories', 'artpulse' ); ?></option>
					<?php foreach ( $categories as $cat ) : ?>
						<option value="<?php echo esc_attr( $cat->slug ); ?>"><?php echo esc_html( $cat->name ); ?></option>
					<?php endforeach; ?>
				</select>
				<select name="event_type">
					<option value=""><?php esc_html_e( 'All Types', 'artpulse' ); ?></option>
					<?php foreach ( $event_types as $type ) : ?>
						<option value="<?php echo esc_attr( $type->slug ); ?>"><?php echo esc_html( $type->name ); ?></option>
					<?php endforeach; ?>
				</select>
				<select name="sort">
					<option value="soonest"><?php esc_html_e( 'Soonest', 'artpulse' ); ?></option>
					<option value="az"><?php esc_html_e( 'A–Z', 'artpulse' ); ?></option>
					<option value="newest"><?php esc_html_e( 'Newest', 'artpulse' ); ?></option>
				</select>
				<input type="hidden" name="alpha" value="">
				<input type="hidden" name="lat" value="">
				<input type="hidden" name="lng" value="">
				<select name="radius">
					<option value="10">10 km</option>
					<option value="25">25 km</option>
					<option value="50" selected>50 km</option>
				</select>
				<button type="button" id="ap-nearby-btn" class="ap-form-button"><?php esc_html_e( 'Events Near Me', 'artpulse' ); ?></button>
				<button type="submit" class="ap-form-button"><?php esc_html_e( 'Apply', 'artpulse' ); ?></button>
			</form>
			<div class="ap-filter-chips" aria-label="<?php esc_attr_e( 'Active filters', 'artpulse' ); ?>"></div>
			<div class="ap-event-listing-results" aria-live="polite"></div>
		</div>
		<?php
		return ob_get_clean();
	}
}
