<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ap_render_report_templates_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Insufficient permissions', 'artpulse' ) );
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Budget & Impact Templates', 'artpulse' ); ?></h1>
		<div id="ap-report-template-root"></div>
	</div>
	<?php
}

ap_render_report_templates_page();
