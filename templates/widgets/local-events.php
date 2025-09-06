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
 * Dashboard widget: Local events.
 */
?>
<div id="local-events" class="ap-card" role="region" aria-labelledby="local-events-title" data-slug="widget_local_events" data-widget="local-events" <?php echo $visible ? '' : 'hidden'; ?>>
	<h2 id="local-events-title" class="ap-card__title"><?php esc_html_e( 'Events Near You', 'artpulse' ); ?></h2>
	<div id="ap-local-events"></div>
	<button class="ap-widget-settings-btn ap-form-button nectar-button" data-widget-settings="local-events"><?php esc_html_e( 'Settings', 'artpulse' ); ?></button>
</div>
