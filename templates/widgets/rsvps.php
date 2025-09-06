<?php
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}
if ( ! user_can( get_current_user_id(), 'read' ) ) {
	return;
}
$args    = ap_template_context( $args ?? array(), array( 'visible' => true ) );
$visible = $args['visible'] ?? true;
/**
 * Dashboard widget: RSVPs.
 */
?>
<div id="rsvps" class="ap-card" role="region" aria-labelledby="rsvps-title" data-widget="rsvps" <?php echo $visible ? '' : 'hidden'; ?>>
	<h2 id="rsvps-title" class="ap-card__title"><?php esc_html_e( 'My RSVPs', 'artpulse' ); ?></h2>
	<div id="ap-rsvp-events"></div>
	<button class="ap-widget-settings-btn ap-form-button nectar-button" data-widget-settings="rsvps"><?php esc_html_e( 'Settings', 'artpulse' ); ?></button>
</div>
