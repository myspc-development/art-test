<?php
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}
if ( ! user_can( get_current_user_id(), 'read' ) ) {
	return;
}
/**
 * Widget: Followed Artists
 */
$artists = array();
if ( ! defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) || ! IS_DASHBOARD_BUILDER_PREVIEW ) {
	$user_id = get_current_user_id();
	$ids     = get_user_meta( $user_id, 'followed_artists', true );
	$ids     = array_filter( array_map( 'intval', (array) $ids ) );
	$artists = $ids ? get_users( array( 'include' => $ids ) ) : array();
}
?>
<div id="ap-widget-followed-artists" class="postbox" role="region" aria-labelledby="ap-widget-followed-artists-title">
	<h2 id="ap-widget-followed-artists-title" class="hndle"><span>🎨 <?php _e( 'Followed Artists', 'artpulse' ); ?></span></h2>
	<div class="inside">
	<?php if ( $artists ) : ?>
		<div class="ap-grid">
		<?php foreach ( $artists as $artist ) : ?>
			<div class="ap-artist-card">
			<a href="<?php echo esc_url( get_author_posts_url( $artist->ID ) ); ?>">
				<?php echo get_avatar( $artist->ID, 64 ); ?>
				<span><?php echo esc_html( $artist->display_name ); ?></span>
			</a>
			</div>
		<?php endforeach; ?>
		</div>
	<?php else : ?>
		<p><?php esc_html_e( 'You are not following any artists.', 'artpulse' ); ?></p>
	<?php endif; ?>
	</div>
</div>
