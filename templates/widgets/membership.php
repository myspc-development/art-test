<?php
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}
if ( ! user_can( get_current_user_id(), 'read' ) ) {
	return;
}
$args             = ap_template_context(
	$args ?? array(),
	array(
		'visible'          => true,
		'badges'           => array(),
		'profile_edit_url' => '',
	)
);
$visible          = $args['visible'] ?? true;
$badges           = $args['badges'] ?? array();
$profile_edit_url = $args['profile_edit_url'] ?? '';
/**
 * Dashboard widget: Membership.
 */
?>
<section id="membership" class="ap-card" role="region" aria-labelledby="membership-title" data-slug="widget_membership" data-widget="widget_membership" <?php echo $visible ? '' : 'hidden'; ?>>
	<h2 id="membership-title" class="ap-card__title"><?php esc_html_e( 'Subscription Status', 'artpulse' ); ?></h2>
	<div id="ap-membership-info"></div>
	<?php if ( ! empty( $badges ) ) : ?>
	<div class="ap-badges"></div>
	<?php endif; ?>
	<div id="ap-membership-actions"></div>
	<?php if ( ! empty( $profile_edit_url ) ) : ?>
	<a class="ap-edit-profile-link ap-form-button nectar-button" href="<?php echo esc_url( $profile_edit_url ); ?>"><?php esc_html_e( 'Edit Profile', 'artpulse' ); ?></a>
	<?php endif; ?>
	<button class="ap-widget-settings-btn ap-form-button nectar-button" data-widget-settings="widget_membership"><?php esc_html_e( 'Settings', 'artpulse' ); ?></button>
</section>
