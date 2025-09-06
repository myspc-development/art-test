<?php
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}
$args    = ap_template_context( $args ?? array(), array( 'visible' => true ) );
$visible = $args['visible'] ?? true;
if ( ! user_can( get_current_user_id(), 'read' ) ) {
	?>
	<section id="events" class="ap-card" role="region" aria-labelledby="events-title" data-widget="widget_events" <?php echo $visible ? '' : 'hidden'; ?>>
		<h2 id="events-title" class="ap-card__title"><?php esc_html_e( 'Upcoming Events', 'artpulse' ); ?></h2>
		<p class="ap-widget__placeholder"><?php esc_html_e( 'Widget unavailable.', 'artpulse' ); ?></p>
	</section>
	<?php
	return;
}
/**
 * Dashboard widget: Upcoming events.
 */
?>
<section id="events" class="ap-card" role="region" aria-labelledby="events-title" data-widget="widget_events" <?php echo $visible ? '' : 'hidden'; ?>>
	<h2 id="events-title" class="ap-card__title"><?php esc_html_e( 'Upcoming Events', 'artpulse' ); ?></h2>
	<div id="ap-events-feed"></div>
	<button class="ap-widget-settings-btn ap-form-button nectar-button" data-widget-settings="events"><?php esc_html_e( 'Settings', 'artpulse' ); ?></button>
</section>
