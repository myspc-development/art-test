<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( function_exists( 'ap_dashboard_v2_enabled' ) && ! ap_dashboard_v2_enabled() ) {
	return;
}

$user = wp_get_current_user();
$menu = function_exists( 'ap_merge_dashboard_menus' )
	? ap_merge_dashboard_menus( $user->roles, true )
	: array();
?>
<nav class="ap-dashboard-nav" data-ap-nav aria-label="<?php esc_attr_e( 'Dashboard navigation', 'artpulse' ); ?>">
	<ul id="ap-nav-list" role="tablist">
		<?php foreach ( $menu as $i => $item ) : ?>
			<li role="presentation">
				<a
					href="<?php echo esc_url( $item['section'] ); ?>"
					class="dashboard-link"
					data-section="<?php echo esc_attr( trim( $item['section'], '#' ) ); ?>"
					role="tab"
					aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>"
					tabindex="<?php echo 0 === $i ? '0' : '-1'; ?>"
				>
					<?php echo esc_html( $item['label'] ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
