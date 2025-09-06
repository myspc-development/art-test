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
<div id="dashboard-feedback" class="dashboard-widget" role="region" aria-labelledby="dashboard-feedback-title" data-widget="dashboard-feedback" <?php echo $visible ? '' : 'hidden'; ?>>
	<div class="inside">
		<h2 id="dashboard-feedback-title"><?php esc_html_e( 'Feedback', 'artpulse' ); ?></h2>
		<form id="ap-dashboard-feedback-form">
			<textarea name="message" required></textarea>
			<?php wp_nonce_field( 'ap_dashboard_feedback', 'nonce' ); ?>
			<button type="submit" class="ap-form-button nectar-button"><?php esc_html_e( 'Send', 'artpulse' ); ?></button>
		</form>
		<div id="ap-dashboard-feedback-msg" role="status" aria-live="polite"></div>
	</div>
</div>
