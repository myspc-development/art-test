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
<div id="artist-inbox-preview" class="ap-card" role="region" aria-labelledby="artist-inbox-preview-title" data-widget="artist-inbox-preview" <?php echo $visible ? '' : 'hidden'; ?>>
	<h2 id="artist-inbox-preview-title" class="ap-card__title"><?php esc_html_e( 'Artist Inbox', 'artpulse' ); ?></h2>
	<div class="ap-artist-inbox-preview" data-api-root="<?php echo esc_attr( $api_root ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>"></div>
	<p class="ap-widget-footer"><a href="/inbox"><?php esc_html_e( 'View All Messages', 'artpulse' ); ?></a></p>
</div>
