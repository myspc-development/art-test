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
<div id="artist-artwork-manager" class="ap-card" role="region" aria-labelledby="artist-artwork-manager-title" data-slug="widget_artist_artwork_manager" data-widget="artist-artwork-manager" <?php echo $visible ? '' : 'hidden'; ?>>
	<h2 id="artist-artwork-manager-title" class="ap-card__title"><?php esc_html_e( 'Artwork Manager', 'artpulse' ); ?></h2>
	<div class="ap-react-widget" data-widget-id="artist_artwork_manager"></div>
</div>
