<?php
// Register a primary navigation menu for the child theme.
function salient_child_register_menus() {
	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'salient-child' ),
		)
	);
}
add_action( 'after_setup_theme', 'salient_child_register_menus' );
