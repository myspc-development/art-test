<?php
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}
if ( ! user_can( get_current_user_id(), 'read' ) ) {
	return;
}
// Fetch a random cat fact from the catfact.ninja API with caching.
$cache_key = 'ap_cat_fact';
$fact      = get_transient( $cache_key );
if ( $fact === false && ( ! defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) || ! IS_DASHBOARD_BUILDER_PREVIEW ) ) {
	$response = wp_remote_get( 'https://catfact.ninja/fact' );
	if ( ! is_wp_error( $response ) ) {
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		if ( isset( $data['fact'] ) ) {
			$fact = $data['fact'];
			set_transient( $cache_key, $fact, HOUR_IN_SECONDS );
		}
	}
}
?>
<div id="ap-cat-fact" class="ap-card" role="region" aria-labelledby="ap-cat-fact-title">
	<h2 id="ap-cat-fact-title" class="ap-card__title">😺 <?php esc_html_e( 'Cat Fact', 'artpulse' ); ?></h2>
	<div>
	<?php if ( $fact ) : ?>
		<p><?php echo esc_html( $fact ); ?></p>
	<?php else : ?>
		<p><?php esc_html_e( 'Could not load cat fact.', 'artpulse' ); ?></p>
	<?php endif; ?>
	</div>
</div>
