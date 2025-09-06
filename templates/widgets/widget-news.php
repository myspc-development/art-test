<?php
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}
if ( ! user_can( get_current_user_id(), 'read' ) ) {
	return;
}
?>
<div id="ap-widget-news" class="postbox" role="region" aria-labelledby="ap-widget-news-title">
	<h2 id="ap-widget-news-title" class="hndle"><span class="dashicons dashicons-megaphone"></span> <?php esc_html_e( 'Latest News', 'artpulse' ); ?></h2>
	<div class="inside">
	<?php
	$recent_posts = array();
	if ( ! defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) || ! IS_DASHBOARD_BUILDER_PREVIEW ) {
		$recent_posts = get_posts(
			array(
				'post_type'   => 'post',
				'numberposts' => 3,
			)
		);
	}
	foreach ( $recent_posts as $post ) {
		echo '<p><a href="' . esc_url( get_permalink( $post ) ) . '">' . esc_html( $post->post_title ) . '</a></p>';
	}
	?>
	</div>
</div>

