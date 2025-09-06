<?php
/**
 * Generic dashboard template.
 *
 * @package ArtPulse
 */

if ( ! current_user_can( 'read' ) ) {
		wp_die( esc_html__( 'Access denied', 'artpulse' ) );
}
$user_role    = isset( $user_role ) && $user_role ? $user_role : 'member';
$dashboard_v2 = function_exists( 'ap_dashboard_v2_enabled' ) ? ap_dashboard_v2_enabled() : true;

get_header();
?>
<div class="wrap">
	<div class="dashboard-widgets-wrap <?php echo esc_attr( $user_role ); ?>"
		data-ap-v2="<?php echo esc_attr( $dashboard_v2 ? '1' : '0' ); ?>"
		data-role-theme="<?php echo esc_attr( $user_role ); ?>">
	<h2 class="ap-card__title ap-role-header">
				<?php printf( '%1$s %2$s', esc_html( ucfirst( $user_role ) ), esc_html__( 'Dashboard', 'artpulse' ) ); ?>
		</h2>

	<form method="post" class="ap-dashboard-reset ap-inline-form">
		<?php wp_nonce_field( 'ap_reset_user_layout' ); ?>
		<input type="hidden" name="reset_user_layout" value="1" />
		<button class="button"><?php esc_html_e( '♻ Reset My Dashboard', 'artpulse' ); ?></button>
	</form>
	</div>
</div>
<?php get_footer(); ?>
