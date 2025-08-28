<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ArtPulse\Core\DashboardWidgetRegistry;

add_action(
	'artpulse_register_dashboard_widget',
	function () {
		DashboardWidgetRegistry::register(
			'ap_messages_widget',
			'Recent Messages',
			'mail',
			'',
			function () {
				echo '<div id="ap-messages-dashboard-widget">Loading messages...</div>';
			},
			array( 'roles' => array( 'administrator' ) )
		);
	}
);

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( $hook === 'index.php' ) {
			$handle = 'ap-dashboard-messages';
			wp_enqueue_script(
				$handle,
				plugin_dir_url( __FILE__ ) . '../assets/js/dashboard-messages.js',
				array(),
				null,
				true
			);

			wp_localize_script(
				$handle,
				'ArtPulseData',
				array(
					'nonce' => wp_create_nonce( 'wp_rest' ),
				)
			);
		}
	}
);

// Modal container for replying directly from the dashboard
add_action(
	'admin_footer-index.php',
	function () {
		echo '<div id="ap-message-modal">';
		echo '<h4>Reply to Message</h4>';
		echo '<textarea id="ap-reply-text" rows="4"></textarea>';
		echo '<button id="ap-send-reply">Send</button>';
		echo '<button id="ap-cancel-reply">Cancel</button>';
		echo '</div>';
	}
);
