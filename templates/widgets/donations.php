<?php
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}
if ( ! user_can( get_current_user_id(), 'read' ) ) {
	return;
}
?>
<p><?php esc_html_e( 'Example donations', 'artpulse' ); ?></p>
