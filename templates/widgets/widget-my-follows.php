<?php
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}
/**
 * Widget template shim for widget_my_follows.
 *
 * Uses legacy widget-followed-artists markup for backward compatibility.
 */
require __DIR__ . '/widget-followed-artists.php';
