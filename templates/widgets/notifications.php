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
 * Dashboard widget: Notifications.
 */
?>
<section id="notifications" class="ap-card" role="region" aria-labelledby="notifications-title" data-widget="notifications" <?php echo $visible ? '' : 'hidden'; ?>>
	<h2 id="notifications-title" class="ap-card__title"><?php esc_html_e( 'Notifications', 'artpulse' ); ?></h2>
	<div id="ap-dashboard-notifications"></div>
	<button class="ap-widget-settings-btn ap-form-button nectar-button" data-widget-settings="notifications"><?php esc_html_e( 'Settings', 'artpulse' ); ?></button>
</section>
