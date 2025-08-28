<?php
/**
 * Shim to unhide specific widgets regardless of saved options.
 */
add_filter(
	'ap_dashboard_hidden_widgets',
	static function ( array $hidden, $role ) {
		return array_values(
			array_diff(
				$hidden,
				array(
					'widget_my_favorites',
					'widget_nearby_events_map',
				)
			)
		);
	},
	10,
	2
);
