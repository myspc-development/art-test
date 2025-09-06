<?php
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}
if ( ! user_can( get_current_user_id(), 'read' ) ) {
	return;
}
use ArtPulse\Services\RecommendationService;
$user_id = get_current_user_id();
$items   = RecommendationService::get_for_user( $user_id );
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	echo '<p class="notice">' . esc_html__( 'Preview mode — dynamic content hidden', 'artpulse' ) . '</p>';
	return;
}
?>
<div id="recommended-for-you-widget" class="ap-card" role="region" aria-labelledby="recommended-for-you-title" data-slug="widget_recommended_for_you" data-widget="recommended-for-you">
	<h2 id="recommended-for-you-title" class="ap-card__title"><?php esc_html_e( 'Recommended For You', 'artpulse' ); ?></h2>
	<?php if ( $items ) : ?>
	<ul>
		<?php foreach ( $items as $item ) : ?>
		<li><a href="<?php echo esc_url( $item['link'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a></li>
	<?php endforeach; ?>
	</ul>
	<?php else : ?>
	<p><?php esc_html_e( 'No recommendations available.', 'artpulse' ); ?></p>
	<?php endif; ?>
</div>
