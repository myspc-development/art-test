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
<div id="onboarding-tracker" class="ap-card" role="region" aria-labelledby="onboarding-tracker-title" data-widget="onboarding-tracker" <?php echo $visible ? '' : 'hidden'; ?>>
	<h2 id="onboarding-tracker-title" class="ap-card__title"><?php esc_html_e( 'Onboarding Checklist', 'artpulse' ); ?></h2>
	<div class="ap-react-widget" data-widget-id="onboarding_tracker"></div>
</div>
