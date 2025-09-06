<?php
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}
if ( ! user_can( get_current_user_id(), 'read' ) ) {
	return;
}
$args    = ap_template_context( $args ?? array(), array( 'visible' => true ) );
$visible = $args['visible'] ?? true;
?>
<div id="my-rsvps-widget" class="ap-card" role="region" aria-labelledby="my-rsvps-title" data-widget="my-rsvps" <?php echo $visible ? '' : 'hidden'; ?>>
	<h2 id="my-rsvps-title" class="ap-card__title"><?php esc_html_e( 'My RSVPs', 'artpulse' ); ?></h2>
	<div id="ap-my-rsvps"></div>
</div>
