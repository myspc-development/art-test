<?php
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}
if ( ! user_can( get_current_user_id(), 'read' ) ) {
	return;
}
$shared = array();
if ( ! defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) || ! IS_DASHBOARD_BUILDER_PREVIEW ) {
	$user_id = get_current_user_id();
	$shared  = get_user_meta( $user_id, 'shared_events', true );
	$shared  = array_filter( (array) $shared );
}
?>
<div id="my-shared-events-activity" class="ap-card" role="region" aria-labelledby="my-shared-events-activity-title" data-widget="my-shared-events-activity">
	<h2 id="my-shared-events-activity-title" class="ap-card__title"><?php esc_html_e( 'My Shared Events', 'artpulse' ); ?></h2>
	<?php if ( $shared ) : ?>
	<ul>
		<?php foreach ( $shared as $event_id ) : ?>
		<li><a href="<?php echo esc_url( get_permalink( $event_id ) ); ?>"><?php echo esc_html( get_the_title( $event_id ) ); ?></a></li>
		<?php endforeach; ?>
	</ul>
	<?php else : ?>
	<p><?php esc_html_e( 'You have not shared any events yet.', 'artpulse' ); ?></p>
	<?php endif; ?>
</div>
