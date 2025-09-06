<?php
// Accessible role switching tabs
if ( function_exists( 'ap_dashboard_v2_enabled' ) && ! ap_dashboard_v2_enabled() ) {
	return;
}

$user  = wp_get_current_user();
$roles = array_values( array_intersect( array( 'member', 'artist', 'organization' ), $user->roles ) );
if ( count( $roles ) <= 1 ) {
	return; // no tabs needed
}

$labels = array(
	'member'       => __( 'Member Tools', 'artpulse' ),
	'artist'       => __( 'Artist Tools', 'artpulse' ),
	'organization' => __( 'Org Tools', 'artpulse' ),
);

$icons = array(
	'member'       => '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><circle cx="12" cy="7" r="4" fill="currentColor"/><path d="M4 21c2.2-3.2 5.3-5 8-5s5.8 1.8 8 5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
	'artist'       => '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M3 21l7-7 2 2 7-7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="19" cy="5" r="2" fill="currentColor"/></svg>',
	'organization' => '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M3 21V9l9-6 9 6v12H3z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M9 21v-6h6v6" fill="none" stroke="currentColor" stroke-width="2"/></svg>',
);

$requested = '';
if ( isset( $_GET['role'] ) ) {
	$requested = sanitize_key( wp_unslash( $_GET['role'] ) );
}
$active = ( $requested && in_array( $requested, $roles, true ) ) ? $requested : $roles[0];
?>
<div class="ap-role-tabs" role="tablist" aria-label="<?php echo esc_attr__( 'Switch role view', 'artpulse' ); ?>">
	<?php foreach ( $roles as $role ) : ?>
		<?php $is_active = ( $role === $active ); ?>
	<button
		type="button"
		class="ap-role-tab<?php echo $is_active ? ' active' : ''; ?>"
		role="tab"
		id="ap-tab-<?php echo esc_attr( $role ); ?>"
		aria-controls="ap-panel-<?php echo esc_attr( $role ); ?>"
		aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
		tabindex="<?php echo $is_active ? '0' : '-1'; ?>"
		data-role="<?php echo esc_attr( $role ); ?>">
		<span class="ap-role-icon" aria-hidden="true"><?php echo $icons[ $role ]; ?></span>
		<span class="ap-role-label"><?php echo esc_html( $labels[ $role ] ?? ucfirst( $role ) ); ?></span>
	</button>
	<?php endforeach; ?>
</div>
