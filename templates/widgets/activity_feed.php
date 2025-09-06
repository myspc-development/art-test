<?php
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}
if ( ! user_can( get_current_user_id(), 'read' ) ) {
	return;
}
use ArtPulse\Widgets\Member\ActivityFeedWidget;

$heading_id = sanitize_title( ActivityFeedWidget::id() ) . '-heading-' . uniqid();
?>
<section role="region" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>"
	data-widget="<?php echo esc_attr( ActivityFeedWidget::id() ); ?>"
	data-widget-id="<?php echo esc_attr( ActivityFeedWidget::id() ); ?>"
	class="ap-widget ap-<?php echo esc_attr( ActivityFeedWidget::id() ); ?>">
	<h2 id="<?php echo esc_attr( $heading_id ); ?>"><?php esc_html_e( 'Recent Activity', 'artpulse' ); ?></h2>
