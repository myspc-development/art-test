<?php
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}
if ( ! user_can( get_current_user_id(), 'read' ) ) {
	return;
}
$args      = ap_template_context(
	$args ?? array(),
	array(
		'visible'   => true,
		'widget_id' => 'widget_my_favorites',
		'id'        => null,
	)
);
$visible   = $args['visible'] ?? true;
$widget_id = $args['widget_id'] ?? 'widget_my_favorites';
$id        = $args['id'] ?? null;
$api_root  = esc_url_raw( rest_url() );
$nonce     = wp_create_nonce( 'wp_rest' );
$id        = $id ?: $widget_id;
?>
<section id="<?php echo esc_attr( $id ); ?>" class="dashboard-widget" role="region" aria-labelledby="<?php echo esc_attr( $id ); ?>-title" data-widget="<?php echo esc_attr( $widget_id ); ?>" data-widget-id="<?php echo esc_attr( $widget_id ); ?>" <?php echo $visible ? '' : 'hidden'; ?>>
	<div class="inside">
		<h2 id="<?php echo esc_attr( $id ); ?>-title"><?php esc_html_e( 'My Favorite Events', 'artpulse' ); ?></h2>
		<div class="ap-favorites-widget" data-api-root="<?php echo esc_attr( $api_root ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>"></div>
	</div>
</section>
