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
<div id="artist-audience-insights" class="ap-card" role="region" aria-labelledby="artist-audience-insights-title" data-slug="widget_artist_audience_insights" data-widget="artist-audience-insights" <?php echo $visible ? '' : 'hidden'; ?>>
	<h2 id="artist-audience-insights-title" class="ap-card__title"><?php esc_html_e( 'Audience Insights', 'artpulse' ); ?></h2>
	<div class="ap-react-widget" data-widget-id="artist_audience_insights"></div>
</div>
