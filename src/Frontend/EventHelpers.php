<?php
namespace ArtPulse\Frontend;

/**
 * Helper functions for rendering RSVP and favorite buttons.
 */
if ( ! function_exists( __NAMESPACE__ . '\ap_render_rsvp_button' ) ) {
	function ap_render_rsvp_button( int $event_id ): string {
		if ( ! is_user_logged_in() ) {
				$login = \ArtPulse\Core\Plugin::get_login_url();
			if ( $login ) {
				return '<a href="' . esc_url( $login ) . '" class="ap-btn ap-login-rsvp">' . esc_html__( 'Login to RSVP', 'artpulse' ) . '</a>';
			}

				return esc_html__( 'Login to RSVP', 'artpulse' );
		}

			$list       = get_post_meta( $event_id, 'event_rsvp_list', true );
			$rsvp_count = is_array( $list ) ? count( $list ) : 0;
			$user_id    = get_current_user_id();
			$rsvps      = $user_id ? (array) get_user_meta( $user_id, 'ap_rsvp_events', true ) : array();
			$joined     = in_array( $event_id, $rsvps, true );

			$label = $joined ? __( 'Cancel RSVP', 'artpulse' ) : __( 'RSVP', 'artpulse' );
			ob_start();
		?>
				<button class="ap-rsvp-btn<?php echo $joined ? ' ap-rsvped' : ''; ?> ap-form-button" data-event="<?php echo esc_attr( $event_id ); ?>" aria-label="<?php echo esc_attr( $label ); ?>"><?php echo esc_html( $label ); ?></button>
				<?php
				return trim( ob_get_clean() );
	}
}

if ( ! function_exists( __NAMESPACE__ . '\ap_render_favorite_button' ) ) {
	function ap_render_favorite_button( int $object_id, string $object_type = '' ): string {
		if ( ! $object_type ) {
				$object_type = get_post_type( $object_id ) ?: '';
		}

			$fav_count = intval( get_post_meta( $object_id, 'ap_favorite_count', true ) );
			$user_id   = get_current_user_id();
			$favorited = $user_id && function_exists( 'ap_user_has_favorited' ) ? ap_user_has_favorited( $user_id, $object_id ) : false;
			$icon      = $favorited ? '❤' : '♡';
			$label     = $favorited ? __( 'Remove favorite', 'artpulse' ) : __( 'Add to favorites', 'artpulse' );
			ob_start();
		?>
				<button class="ap-fav-btn<?php echo $favorited ? ' ap-favorited' : ''; ?>" data-object-id="<?php echo esc_attr( $object_id ); ?>" data-object-type="<?php echo esc_attr( $object_type ); ?>" aria-label="<?php echo esc_attr( $label ); ?>"><?php echo $icon; ?></button>
				<?php
				return trim( ob_get_clean() );
	}
}

if ( ! function_exists( __NAMESPACE__ . '\ap_render_basic_rsvp_form' ) ) {
	function ap_render_basic_rsvp_form( int $event_id ): string {
			ob_start();
		?>
				<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST">
					<?php wp_nonce_field( 'ap_rsvp_event' ); ?>
						<input type="hidden" name="action" value="ap_rsvp_event">
						<input type="hidden" name="event_id" value="<?php echo esc_attr( $event_id ); ?>">
						<button type="submit"><?php esc_html_e( 'RSVP to this Event', 'artpulse' ); ?></button>
				</form>
				<?php
				return trim( ob_get_clean() );
	}
}
