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
 * Dashboard widget: Sales Summary.
 */
?>
<div id="sales-summary" class="ap-card" role="region" aria-labelledby="sales-summary-title" data-widget="sales_summary" <?php echo $visible ? '' : 'hidden'; ?>>
	<h2 id="sales-summary-title" class="ap-card__title"><?php esc_html_e( 'Sales Summary', 'artpulse' ); ?></h2>
	<div id="ap-sales-summary"></div>
</div>
