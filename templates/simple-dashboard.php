<?php
/**
 * Template Name: Simple Dashboard
 *
 * Minimal template rendering widgets for the current user's role.
 */

if ( ! is_user_logged_in() ) {
	return;
}

\ArtPulse\Core\DashboardWidgetRegistry::init();

$role = function_exists( 'get_query_var' ) ? sanitize_key( get_query_var( 'ap_role' ) ) : 'member';
if ( ! in_array( $role, array( 'member', 'artist', 'organization' ), true ) ) {
	$role = 'member';
}
?>
<section
	class="ap-role-layout"
	role="tabpanel"
	id="ap-panel-<?php echo esc_attr( $role ); ?>"
	aria-labelledby="ap-tab-<?php echo esc_attr( $role ); ?>"
	data-role="<?php echo esc_attr( $role ); ?>"
>
	<?php foreach ( \ArtPulse\Admin\RolePresets::get_preset_slugs( $role ) as $slug ) : ?>
		<?php echo \ArtPulse\Core\DashboardWidgetRegistry::render( $slug, array( 'preview_role' => $role ) ); ?>
	<?php endforeach; ?>
</section>

