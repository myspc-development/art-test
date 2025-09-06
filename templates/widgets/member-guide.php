<?php
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}
if ( ! user_can( get_current_user_id(), 'read' ) ) {
	return;
}
$args              = $args ?? array();
$args['widget_id'] = 'member_guide';
require __DIR__ . '/guide.php';
