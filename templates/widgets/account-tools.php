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
 * Dashboard widget: Account Tools.
 */
?>
<section id="account-tools" class="ap-card" role="region" aria-labelledby="account-tools-title" data-slug="widget_account_tools" data-widget="account-tools" <?php echo $visible ? '' : 'hidden'; ?>>
	<h2 id="account-tools-title" class="ap-card__title"><?php esc_html_e( 'Account Tools', 'artpulse' ); ?></h2>
	<div id="ap-account-tools">
		<button id="ap-export-json" class="ap-form-button nectar-button"><?php esc_html_e( 'Export JSON', 'artpulse' ); ?></button>
		<button id="ap-export-csv" class="ap-form-button nectar-button"><?php esc_html_e( 'Export CSV', 'artpulse' ); ?></button>
		<button id="ap-delete-account" class="ap-form-button nectar-button"><?php esc_html_e( 'Delete Account', 'artpulse' ); ?></button>
	</div>
	<button class="ap-widget-settings-btn ap-form-button nectar-button" data-widget-settings="account-tools"><?php esc_html_e( 'Settings', 'artpulse' ); ?></button>
</section>
