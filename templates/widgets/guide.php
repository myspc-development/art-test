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
		'id'        => 'guide',
		'widget_id' => null,
		'title'     => '',
		'content'   => '',
		'visible'   => true,
	)
);
$id        = $args['id'] ?? 'guide';
$widget_id = $args['widget_id'] ?? null;
$title     = $args['title'] ?? '';
$content   = $args['content'] ?? '';
$visible   = $args['visible'] ?? true;
$widget_id = $widget_id ?: $id;
?>
<div id="<?php echo esc_attr( $id ); ?>" class="ap-card" role="region" aria-labelledby="<?php echo esc_attr( $id ); ?>-title" data-widget="<?php echo esc_attr( $widget_id ); ?>" data-widget-id="<?php echo esc_attr( $widget_id ); ?>" <?php echo $visible ? '' : 'hidden'; ?>>
	<h2 id="<?php echo esc_attr( $id ); ?>-title" class="ap-card__title"><?php echo esc_html( $title ); ?></h2>
	<div class="ap-guide-content">
		<?php echo $content; ?>
	</div>
</div>
