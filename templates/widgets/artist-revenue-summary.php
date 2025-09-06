<?php
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}
if ( ! user_can( get_current_user_id(), 'read' ) ) {
	return;
}
$args     = ap_template_context( $args ?? array(), array( 'visible' => true ) );
$visible  = $args['visible'] ?? true;
$api_root = esc_url_raw( rest_url() );
$nonce    = wp_create_nonce( 'wp_rest' );
?>
<div id="artist-revenue-summary" class="ap-card" role="region" aria-labelledby="artist-revenue-summary-title" data-slug="widget_artist_revenue_summary" data-widget="artist-revenue-summary" <?php echo $visible ? '' : 'hidden'; ?>>
	<h2 id="artist-revenue-summary-title" class="ap-card__title"><?php esc_html_e( 'Revenue Summary', 'artpulse' ); ?></h2>
	<div class="ap-revenue-summary-widget" data-api-root="<?php echo esc_attr( $api_root ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>"></div>
</div>
