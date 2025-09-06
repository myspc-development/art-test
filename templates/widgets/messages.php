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
 * Dashboard widget: Messages.
 */
?>
<div id="messages" class="ap-card" role="region" aria-labelledby="messages-title" data-widget="messages" <?php echo $visible ? '' : 'hidden'; ?>>
	<h2 id="messages-title" class="ap-card__title"><?php esc_html_e( 'Messages', 'artpulse' ); ?></h2>
	<?php echo do_shortcode( '[ap_messages]' ); ?>
	<button class="ap-widget-settings-btn ap-form-button nectar-button" data-widget-settings="messages"><?php esc_html_e( 'Settings', 'artpulse' ); ?></button>
</div>
