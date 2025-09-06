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
 * Dashboard widget: Support History.
 */
?>
<div id="support-history" class="ap-card" role="region" aria-labelledby="support-history-title" data-widget="support-history" <?php echo $visible ? '' : 'hidden'; ?>>
	<h2 id="support-history-title" class="ap-card__title"><?php esc_html_e( 'Support History', 'artpulse' ); ?></h2>
	<div id="ap-support-history"></div>
	<button class="ap-widget-settings-btn ap-form-button nectar-button" data-widget-settings="support-history"><?php esc_html_e( 'Settings', 'artpulse' ); ?></button>
</div>
