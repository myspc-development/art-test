<?php

function ap_render_help_markdown( $filename = 'Admin_Help.md' ) {
	$path = plugin_dir_path( __FILE__ ) . '../assets/docs/' . $filename;

	if ( ! file_exists( $path ) ) {
		return '<div class="ap-admin-help"><p><strong>Error:</strong> Help file not found.</p></div>';
	}

	$markdown  = file_get_contents( $path );
	$Parsedown = new Parsedown();
	$html      = $Parsedown->text( $markdown );

	return '<div class="ap-admin-help">' . $html . '</div>';
}
