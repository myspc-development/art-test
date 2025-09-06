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
<div id="upcoming-events-location" class="ap-card" role="region" aria-labelledby="upcoming-events-location-title" data-widget="upcoming-events-by-location" <?php echo $visible ? '' : 'hidden'; ?>>
	<h2 id="upcoming-events-location-title" class="ap-card__title"><?php esc_html_e( 'Upcoming Events Near You', 'artpulse' ); ?></h2>
	<div class="ap-upcoming-events-location"></div>
</div>
