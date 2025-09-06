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
 * Dashboard widget: Site Stats.
 */
?>
<div id="site-stats" class="ap-card" role="region" aria-labelledby="site-stats-title" data-slug="widget_site_stats" data-widget="site_stats" <?php echo $visible ? '' : 'hidden'; ?>>
	<h2 id="site-stats-title" class="ap-card__title"><?php esc_html_e( 'Site Stats', 'artpulse' ); ?></h2>
	<div id="ap-site-stats"></div>
</div>
