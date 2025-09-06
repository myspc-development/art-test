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
 * Dashboard widget: Lead Capture.
 */
?>
<div id="lead-capture" class="ap-card" role="region" aria-labelledby="lead-capture-title" data-widget="lead_capture" <?php echo $visible ? '' : 'hidden'; ?>>
	<h2 id="lead-capture-title" class="ap-card__title"><?php esc_html_e( 'Lead Capture', 'artpulse' ); ?></h2>
	<div id="ap-lead-capture"></div>
</div>
