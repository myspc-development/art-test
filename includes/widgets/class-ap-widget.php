<?php
/**
 * AP_Widget allows administrators to render a shortcode inside
 * a dashboard widget. It extends the base WP_Widget class and
 * stores the shortcode and optional attributes.
 */
if ( ! class_exists( 'AP_Widget' ) && class_exists( 'WP_Widget' ) ) {
	class AP_Widget extends WP_Widget {
		public function __construct() {
			parent::__construct(
				'ap_shortcode_widget',
				__( 'AP Shortcode Widget', 'artpulse' ),
				array( 'description' => __( 'Render a shortcode inside a widget.', 'artpulse' ) )
			);
		}

		public function widget( $args, $instance ) {
			echo $args['before_widget'] ?? '';
			$shortcode = $instance['shortcode'] ?? '';
			$atts      = $instance['atts'] ?? '';
                        if ( $shortcode ) {
                                $out = do_shortcode( $shortcode . ( $atts ? ' ' . $atts : '' ) );
                                echo wp_kses_post( $out );
                        }
                        echo $args['after_widget'] ?? '';
                }

		public function form( $instance ) {
			$shortcode = esc_attr( $instance['shortcode'] ?? '' );
			$atts      = esc_attr( $instance['atts'] ?? '' );
			?>
			<p>
				<label><?php _e( 'Shortcode:', 'artpulse' ); ?></label>
				<input class="widefat" name="<?php echo $this->get_field_name( 'shortcode' ); ?>" type="text" value="<?php echo $shortcode; ?>" />
			</p>
			<p>
				<label><?php _e( 'Attributes:', 'artpulse' ); ?></label>
				<input class="widefat" name="<?php echo $this->get_field_name( 'atts' ); ?>" type="text" value="<?php echo $atts; ?>" />
			</p>
			<?php
		}

		public function update( $new_instance, $old_instance ) {
			return array(
				'shortcode' => sanitize_text_field( $new_instance['shortcode'] ?? '' ),
				'atts'      => sanitize_text_field( $new_instance['atts'] ?? '' ),
			);
		}
	}
}
