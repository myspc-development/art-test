<?php
/**
 * RSVP stats widget template.
 *
 * @package ArtPulse
 */

if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
		return;
}
if ( ! user_can( get_current_user_id(), 'read' ) ) {
		return;
}
use ArtPulse\Frontend\EventRsvpHandler;
$rsvp_data = array();
if ( ! defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	$rsvp_data = EventRsvpHandler::get_rsvp_summary_for_user( get_current_user_id() );
} else {
	echo '<p class="notice">' . esc_html__( 'Preview mode — dynamic content hidden', 'artpulse' ) . '</p>';
	return;
}
?>

<div id="ap-rsvp-stats" class="ap-card" role="region" aria-labelledby="ap-rsvp-stats-title">
	<h2 id="ap-rsvp-stats-title" class="ap-card__title">📅 <?php echo esc_html__( 'RSVP Stats', 'artpulse' ); ?></h2>
	<div>
	<p><strong><?php echo esc_html( $rsvp_data['going'] ); ?></strong> <?php echo esc_html__( 'Going', 'artpulse' ); ?></p>
	<p><strong><?php echo esc_html( $rsvp_data['interested'] ); ?></strong> <?php echo esc_html__( 'Interested', 'artpulse' ); ?></p>

	<?php if ( ! empty( $rsvp_data['trend'] ) ) : ?>
		<div class="ap-rsvp-chart" data-chart="<?php echo esc_attr( wp_json_encode( $rsvp_data['trend'] ) ); ?>"></div>
	<?php endif; ?>

	<a href="<?php echo esc_url( '/dashboard/events' ); ?>" class="button small"><?php echo esc_html__( 'Manage Events', 'artpulse' ); ?></a>
	</div>
</div>
