<?php
namespace ArtPulse\Frontend;

class UserProfileShortcode {

	public static function register() {
		\ArtPulse\Core\ShortcodeRegistry::register( 'ap_user_profile', 'User Profile', array( self::class, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue_styles' ) );
	}

	public static function enqueue_styles(): void {
		if ( function_exists( 'ap_enqueue_global_styles' ) ) {
			ap_enqueue_global_styles();
		}
	}

	public static function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'id'                => get_current_user_id(),
				'show_social'       => 'true',
				'show_membership'   => 'false',
				'show_completeness' => 'false',
			),
			$atts,
			'ap_user_profile'
		);

		$show_social       = filter_var( $atts['show_social'], FILTER_VALIDATE_BOOLEAN );
		$show_membership   = filter_var( $atts['show_membership'], FILTER_VALIDATE_BOOLEAN );
		$show_completeness = filter_var( $atts['show_completeness'], FILTER_VALIDATE_BOOLEAN );

		$user_id = intval( $atts['id'] );
		$user    = get_userdata( $user_id );

		if ( ! $user ) {
			return '<div class="ap-user-profile-error">User not found.</div>';
		}

		\ArtPulse\Core\ProfileMetrics::log_metric( $user_id, 'view' );

		$bio       = get_user_meta( $user_id, 'description', true );
		$followers = (int) get_user_meta( $user_id, 'ap_follower_count', true );
		$following = (int) get_user_meta( $user_id, 'ap_following_count', true );
		$avatar    = get_user_meta( $user_id, 'ap_custom_avatar', true );
		$twitter   = get_user_meta( $user_id, 'ap_social_twitter', true );
		$instagram = get_user_meta( $user_id, 'ap_social_instagram', true );
		$website   = get_user_meta( $user_id, 'ap_social_website', true );

		$country          = get_user_meta( $user_id, 'ap_country', true );
		$state            = get_user_meta( $user_id, 'ap_state', true );
		$city             = get_user_meta( $user_id, 'ap_city', true );
		$email_privacy    = get_user_meta( $user_id, 'ap_privacy_email', true ) ?: 'public';
		$location_privacy = get_user_meta( $user_id, 'ap_privacy_location', true ) ?: 'public';

		if ( $show_membership ) {
			$level   = get_user_meta( $user_id, 'ap_membership_level', true ) ?: __( 'Free', 'artpulse' );
			$expires = get_user_meta( $user_id, 'ap_membership_expires', true );
			$expires = $expires ? date_i18n( get_option( 'date_format' ), intval( $expires ) ) : __( 'Never', 'artpulse' );
		}

		if ( $show_completeness ) {
			$fields = array( $bio, $avatar, $twitter, $instagram, $website, $country, $state, $city );
			$filled = 0;
			foreach ( $fields as $field ) {
				if ( ! empty( $field ) ) {
					++$filled;
				}
			}
			$percentage = round( $filled / count( $fields ) * 100 );
		}

		ob_start(); ?>
		<div class="ap-user-profile">
			<div class="ap-user-profile-header">
				<img src="<?php echo esc_url( $avatar ? $avatar : get_avatar_url( $user_id ) ); ?>" class="ap-user-avatar" alt="User avatar">
				<h2 class="ap-user-name"><?php echo esc_html( $user->display_name ); ?></h2>
			</div>
			<div class="ap-user-profile-body">
				<?php if ( $bio ) : ?>
					<p class="ap-user-bio"><?php echo esc_html( $bio ); ?></p>
				<?php endif; ?>
				<p><strong>Followers:</strong> <?php echo intval( $followers ); ?></p>
				<p><strong>Following:</strong> <?php echo intval( $following ); ?></p>
				<?php if ( $show_membership ) : ?>
					<p class="ap-user-membership">
						<strong><?php esc_html_e( 'Membership Level', 'artpulse' ); ?>:</strong>
						<?php echo esc_html( $level ); ?>
						<br>
						<strong><?php esc_html_e( 'Expires', 'artpulse' ); ?>:</strong>
						<?php echo esc_html( $expires ); ?>
					</p>
				<?php endif; ?>

				<?php if ( $show_social ) : ?>
					<div class="ap-user-social-links">
						<?php if ( $twitter ) : ?>
							<p><a href="<?php echo esc_url( $twitter ); ?>" target="_blank">Twitter</a></p>
						<?php endif; ?>
						<?php if ( $instagram ) : ?>
							<p><a href="<?php echo esc_url( $instagram ); ?>" target="_blank">Instagram</a></p>
						<?php endif; ?>
						<?php if ( $website ) : ?>
							<p><a href="<?php echo esc_url( $website ); ?>" target="_blank">Website</a></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<?php if ( $email_privacy === 'public' ) : ?>
					<p class="ap-user-email"><strong><?php esc_html_e( 'Email:', 'artpulse' ); ?></strong> <?php echo esc_html( $user->user_email ); ?></p>
				<?php endif; ?>
				<?php if ( $location_privacy === 'public' && ( $country || $state || $city ) ) : ?>
					<p class="ap-user-location"><strong><?php esc_html_e( 'Location:', 'artpulse' ); ?></strong> <?php echo esc_html( trim( implode( ', ', array_filter( array( $city, $state, $country ) ) ) ) ); ?></p>
				<?php endif; ?>

				<?php if ( $show_completeness ) : ?>
					<p class="ap-profile-completeness">
                                                <?php printf( esc_html__( 'Profile completeness: %1$d%%', 'artpulse' ), intval( $percentage ) ); ?>
                                                </p>
                                        <?php endif; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function countFollowers( $user_id ) {
		return (int) get_user_meta( $user_id, 'ap_follower_count', true );
	}
}
