<?php
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}
if ( ! user_can( get_current_user_id(), 'read' ) ) {
	return;
}

\ArtPulse\Frontend\SubmissionForms::register();
wp_enqueue_script( 'ap-submission-form-js' );

$args    = ap_template_context( $args ?? array(), array( 'visible' => true ) );
$visible = $args['visible'] ?? true;
/**
 * Dashboard widget: My Events.
 */
?>
<div id="my-events" class="ap-card" role="region" aria-labelledby="my-events-title" data-slug="widget_my_events" data-widget="my-events" <?php echo $visible ? '' : 'hidden'; ?>>
	<h2 id="my-events-title" class="ap-card__title"><?php esc_html_e( 'My Events', 'artpulse' ); ?></h2>
	<?php if ( current_user_can( 'publish_ap_events' ) ) : ?>
		<button id="ap-add-event-btn" class="ap-form-button nectar-button"><?php esc_html_e( 'Add New Event', 'artpulse' ); ?></button>
		<div id="ap-add-event-form" hidden>
			<?php echo do_shortcode( '[ap_submission_form post_type="artpulse_event"]' ); ?>
		</div>
		<script>
		document.getElementById('ap-add-event-btn')?.addEventListener('click', function() {
			var form = document.getElementById('ap-add-event-form');
			if (form) { form.hidden = false; }
			this.hidden = true;
		});
		</script>
	<?php endif; ?>
	<div id="ap-dashboard-stats" class="ap-dashboard-stats"></div>
	<div id="ap-next-event"></div>
	<div id="ap-my-events"></div>
	<canvas id="ap-trends-chart" height="150"></canvas>
	<canvas id="ap-user-engagement-chart" height="150"></canvas>
	<canvas id="ap-profile-metrics-chart" height="150"></canvas>
	<canvas id="ap-event-analytics-chart" height="150"></canvas>
	<button class="ap-widget-settings-btn ap-form-button nectar-button" data-widget-settings="my-events"><?php esc_html_e( 'Settings', 'artpulse' ); ?></button>
</div>
