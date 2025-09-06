<?php
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}
if ( ! user_can( get_current_user_id(), 'read' ) ) {
	return;
}
/**
 * Instagram widget template.
 */
$token = $args['access_token'] ?? '';
$count = isset( $args['count'] ) ? (int) $args['count'] : 3;
$urls  = array();
$posts = array();

if ( ! defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) || ! IS_DASHBOARD_BUILDER_PREVIEW ) {
	if ( $token ) {
		$cache_key = 'ap_instagram_posts_' . md5( $token . '|' . $count );
		$posts     = get_transient( $cache_key );
		if ( false === $posts ) {
			$resp = wp_remote_get( "https://graph.instagram.com/me/media?fields=permalink,media_url,caption&access_token={$token}&limit={$count}" );
			if ( ! is_wp_error( $resp ) ) {
				$data  = json_decode( wp_remote_retrieve_body( $resp ), true );
				$posts = $data['data'] ?? array();
				set_transient( $cache_key, $posts, HOUR_IN_SECONDS );
			} else {
				$posts = array();
			}
		}
	} elseif ( ! empty( $args['urls'] ) ) {
		$urls = array_slice( (array) $args['urls'], 0, $count );
		foreach ( $urls as $u ) {
			$posts[] = array( 'permalink' => $u );
		}
	}
}
?>
<div id="ap-instagram" class="ap-card" role="region" aria-labelledby="ap-instagram-title">
	<h2 id="ap-instagram-title" class="ap-card__title">📷 <?php _e( 'Instagram', 'artpulse' ); ?></h2>
	<div>
	<?php foreach ( $posts as $post ) : ?>
		<div class="ap-instagram-post">
		<?php
		$embed = wp_oembed_get( $post['permalink'] );
		if ( $embed ) {
			echo $embed; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			echo '<a href="' . esc_url( $post['permalink'] ) . '" target="_blank">' . esc_html( $post['permalink'] ) . '</a>';
		}
		?>
		</div>
	<?php endforeach; ?>
	</div>
</div>
