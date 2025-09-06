<?php
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}
if ( ! user_can( get_current_user_id(), 'read' ) ) {
	return;
}
$args        = ap_template_context(
	$args ?? array(),
	array(
		'visible'     => true,
		'show_forms'  => false,
		'artist_form' => '',
		'org_form'    => '',
	)
);
$visible     = $args['visible'] ?? true;
$show_forms  = $args['show_forms'] ?? false;
$artist_form = $args['artist_form'] ?? '';
$org_form    = $args['org_form'] ?? '';
/**
 * Dashboard widget: Upgrade account.
 */
?>
<section id="upgrade" class="ap-card" role="region" aria-labelledby="upgrade-title" data-widget="upgrade" <?php echo $visible ? '' : 'hidden'; ?>>
	<h2 id="upgrade-title" class="ap-card__title"><?php esc_html_e( 'Upgrade Your Account', 'artpulse' ); ?></h2>
	<div id="ap-upgrade-options"></div>
	<?php if ( ! empty( $show_forms ) ) : ?>
	<div class="ap-dashboard-forms">
		<?php echo $artist_form ?? ''; ?>
		<?php echo $org_form ?? ''; ?>
	</div>
	<?php endif; ?>
	<button class="ap-widget-settings-btn ap-form-button nectar-button" data-widget-settings="upgrade"><?php esc_html_e( 'Settings', 'artpulse' ); ?></button>
</section>
