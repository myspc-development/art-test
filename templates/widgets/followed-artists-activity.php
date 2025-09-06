<?php
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}
if ( ! user_can( get_current_user_id(), 'read' ) ) {
	return;
}
$posts = array();
if ( ! defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) || ! IS_DASHBOARD_BUILDER_PREVIEW ) {
	$user_id = get_current_user_id();
	$ids     = get_user_meta( $user_id, 'followed_artists', true );
	$ids     = array_filter( array_map( 'intval', (array) $ids ) );
	$posts   = $ids ? get_posts(
		array(
			'post_type'      => array( 'artpulse_event', 'post' ),
			'author__in'     => $ids,
			'posts_per_page' => 5,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	) : array();
}
?>
<div id="followed-artists-activity" class="ap-card" role="region" aria-labelledby="followed-artists-activity-title" data-widget="followed-artists-activity">
	<h2 id="followed-artists-activity-title" class="ap-card__title"><?php esc_html_e( 'Followed Artists Activity', 'artpulse' ); ?></h2>
	<?php if ( $posts ) : ?>
		<ul>
			<?php foreach ( $posts as $p ) : ?>
				<li><a href="<?php echo esc_url( get_permalink( $p ) ); ?>"><?php echo esc_html( get_the_title( $p ) ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	<?php else : ?>
		<p><?php esc_html_e( 'No recent activity from followed artists.', 'artpulse' ); ?></p>
	<?php endif; ?>
</div>
