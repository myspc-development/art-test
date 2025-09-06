<?php
namespace ArtPulse\Admin;

/**
 * Register the "spotlight" custom post type and meta boxes.
 */
class SpotlightPostType {

	public static function register(): void {
		add_action( 'init', array( self::class, 'register_cpt' ) );
		add_action( 'init', __NAMESPACE__ . '\\ap_register_spotlight_taxonomy' );
		add_action( 'add_meta_boxes', array( self::class, 'add_meta_boxes' ) );
		add_action( 'save_post_spotlight', array( self::class, 'save_meta' ) );
	}

	public static function register_cpt(): void {
		register_post_type(
			'spotlight',
			array(
				'label'        => __( 'Spotlights', 'artpulse' ),
				'public'       => false,
				'show_ui'      => true,
				'show_in_rest' => true,
				'supports'     => array( 'title', 'editor' ),
				'menu_icon'    => 'dashicons-star-filled',
			)
		);

				register_post_meta(
					'spotlight',
					'visible_to_roles',
					array(
						'show_in_rest' => array(
							'schema' => array(
								'type'  => 'array',
								'items' => array( 'type' => 'string' ),
							),
						),
						'single'       => true,
						'type'         => 'array',
					)
				);
		register_post_meta(
			'spotlight',
			'start_at',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);
		register_post_meta(
			'spotlight',
			'expires_at',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);
		register_post_meta(
			'spotlight',
			'cta_text',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);
		register_post_meta(
			'spotlight',
			'cta_url',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);
		register_post_meta(
			'spotlight',
			'cta_target',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		register_post_meta(
			'spotlight',
			'is_pinned',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'boolean',
			)
		);

		register_post_meta(
			'spotlight',
			'_is_featured',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'boolean',
			)
		);
	}

	public static function add_meta_boxes(): void {
		add_meta_box( 'spotlight_roles', __( 'Visible To Roles', 'artpulse' ), array( self::class, 'render_roles_meta' ), 'spotlight', 'side' );
		add_meta_box( 'spotlight_schedule', __( 'Visibility Schedule', 'artpulse' ), array( self::class, 'render_schedule_meta' ), 'spotlight', 'side' );
		add_meta_box( 'spotlight_pin', __( 'Pin Spotlight', 'artpulse' ), array( self::class, 'render_pin_meta' ), 'spotlight', 'side' );
		add_meta_box( 'spotlight_feature', __( 'Feature Spotlight', 'artpulse' ), array( self::class, 'render_feature_meta' ), 'spotlight', 'side' );
		add_meta_box( 'spotlight_cta', __( 'Call to Action', 'artpulse' ), array( self::class, 'render_cta_meta' ), 'spotlight', 'normal' );
	}

	public static function render_roles_meta( $post ): void {
		$roles    = array( 'member', 'artist', 'organization' );
		$selected = get_post_meta( $post->ID, 'visible_to_roles', true ) ?: array();
		foreach ( $roles as $role ) {
			$checked = in_array( $role, (array) $selected, true ) ? 'checked' : '';
			echo "<label><input type='checkbox' name='visible_to_roles[]' value='{$role}' {$checked} /> " . esc_html( $role ) . '</label><br>';
		}
		// Scheduling is handled in a separate meta box.
	}

	public static function render_schedule_meta( $post ): void {
		$start = get_post_meta( $post->ID, 'start_at', true );
		$end   = get_post_meta( $post->ID, 'expires_at', true );
		?>
		<p><label><?php _e( 'Start Date', 'artpulse' ); ?><br>
			<input type="date" name="start_at" value="<?php echo esc_attr( $start ); ?>" class="widefat" /></label></p>

		<p><label><?php _e( 'End Date', 'artpulse' ); ?><br>
			<input type="date" name="expires_at" value="<?php echo esc_attr( $end ); ?>" class="widefat" /></label></p>
		<?php
	}

	public static function render_pin_meta( $post ): void {
		$pinned = get_post_meta( $post->ID, 'is_pinned', true );
		?>
		<p><label><input type="checkbox" name="is_pinned" value="1" <?php echo $pinned ? 'checked' : ''; ?> />
			<?php _e( 'Pin this spotlight', 'artpulse' ); ?></label></p>
		<?php
	}

	public static function render_feature_meta( $post ): void {
		$featured = get_post_meta( $post->ID, '_is_featured', true );
		?>
		<p><label><input type="checkbox" name="_is_featured" value="1" <?php echo $featured ? 'checked' : ''; ?> />
			<?php _e( 'Featured Listing', 'artpulse' ); ?></label></p>
		<?php
	}

	public static function render_cta_meta( $post ): void {
		$text   = get_post_meta( $post->ID, 'cta_text', true );
		$url    = get_post_meta( $post->ID, 'cta_url', true );
		$target = get_post_meta( $post->ID, 'cta_target', true );
		?>
		<p><label><?php _e( 'Button Text', 'artpulse' ); ?><br>
			<input type="text" name="cta_text" value="<?php echo esc_attr( $text ); ?>" class="widefat" /></label></p>
		<p><label><?php _e( 'URL', 'artpulse' ); ?><br>
			<input type="url" name="cta_url" value="<?php echo esc_url( $url ); ?>" class="widefat" /></label></p>
		<p><label><input type="checkbox" name="cta_target" value="_blank" <?php echo $target === '_blank' ? 'checked' : ''; ?> />
			<?php _e( 'Open in new tab', 'artpulse' ); ?></label></p>
		<?php
	}

	public static function save_meta( int $post_id ): void {
		if ( isset( $_POST['visible_to_roles'] ) ) {
			update_post_meta( $post_id, 'visible_to_roles', array_map( 'sanitize_text_field', (array) $_POST['visible_to_roles'] ) );
		} else {
			delete_post_meta( $post_id, 'visible_to_roles' );
		}

		if ( isset( $_POST['start_at'] ) ) {
			update_post_meta( $post_id, 'start_at', sanitize_text_field( $_POST['start_at'] ) );
		} else {
			delete_post_meta( $post_id, 'start_at' );
		}

		if ( isset( $_POST['expires_at'] ) ) {
			update_post_meta( $post_id, 'expires_at', sanitize_text_field( $_POST['expires_at'] ) );
		} else {
			delete_post_meta( $post_id, 'expires_at' );
		}

		update_post_meta( $post_id, 'cta_text', sanitize_text_field( $_POST['cta_text'] ?? '' ) );
		update_post_meta( $post_id, 'cta_url', esc_url_raw( $_POST['cta_url'] ?? '' ) );
		update_post_meta( $post_id, 'cta_target', sanitize_text_field( $_POST['cta_target'] ?? '' ) );

		if ( isset( $_POST['is_pinned'] ) ) {
			update_post_meta( $post_id, 'is_pinned', '1' );
		} else {
			delete_post_meta( $post_id, 'is_pinned' );
		}

		if ( isset( $_POST['_is_featured'] ) ) {
			update_post_meta( $post_id, '_is_featured', '1' );
		} else {
			delete_post_meta( $post_id, '_is_featured' );
		}
	}
}

function ap_register_spotlight_taxonomy(): void {
	register_taxonomy(
		'spotlight_category',
		'spotlight',
		array(
			'label'        => 'Spotlight Categories',
			'public'       => true,
			'hierarchical' => false,
			'show_ui'      => true,
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => 'spotlight-category' ),
		)
	);
}
