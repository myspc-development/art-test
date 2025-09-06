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
 * Dashboard widget: Webhooks.
 */
?>
<div id="webhooks" class="ap-card" role="region" aria-labelledby="webhooks-title" data-slug="widget_webhooks" data-widget="webhooks" data-widget-id="webhooks" <?php echo $visible ? '' : 'hidden'; ?>>
	<h2 id="webhooks-title" class="ap-card__title"><?php esc_html_e( 'Webhooks', 'artpulse' ); ?></h2>
	<div id="ap-webhook-controls">
		<button id="ap-add-webhook" class="ap-form-button nectar-button"><?php esc_html_e( 'Add Webhook', 'artpulse' ); ?></button>
	</div>
	<ul id="ap-webhook-list"></ul>
	<div id="ap-webhook-modal" class="ap-org-modal" hidden>
		<button id="ap-webhook-close" type="button" class="ap-form-button nectar-button"><?php esc_html_e( 'Close', 'artpulse' ); ?></button>
		<div id="ap-webhook-msg" class="ap-form-messages" role="status" aria-live="polite"></div>
		<form id="ap-webhook-form" class="ap-form-container" data-no-ajax="true">
			<input type="hidden" id="ap_webhook_id" name="id">
			<label class="ap-form-label" for="ap_webhook_url"><?php esc_html_e( 'Endpoint URL', 'artpulse' ); ?></label>
			<input class="ap-input" id="ap_webhook_url" type="url" name="url" required>
			<fieldset>
				<legend><?php esc_html_e( 'Events', 'artpulse' ); ?></legend>
				<label><input type="checkbox" value="ticket_sold" name="events[]"> <?php esc_html_e( 'Ticket Sold', 'artpulse' ); ?></label>
				<label><input type="checkbox" value="rsvp_created" name="events[]"> <?php esc_html_e( 'RSVP Created', 'artpulse' ); ?></label>
				<label><input type="checkbox" value="payout_processed" name="events[]"> <?php esc_html_e( 'Payout Processed', 'artpulse' ); ?></label>
			</fieldset>
			<label class="ap-form-label"><input type="checkbox" id="ap_webhook_active" name="active" value="1" checked> <?php esc_html_e( 'Active', 'artpulse' ); ?></label>
			<button type="submit" class="ap-form-button nectar-button"><?php esc_html_e( 'Save', 'artpulse' ); ?></button>
		</form>
	</div>
</div>
