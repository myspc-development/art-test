<form method="post" action="options.php">
	<?php
		settings_fields( 'artpulse_widget_settings' );
		do_settings_sections( 'artpulse_widget_settings' );
		$widgets         = artpulse_get_available_dashboard_widgets();
		$enabled_widgets = get_option( 'artpulse_enabled_widgets', array() );

	foreach ( $widgets as $key => $info ) {
		$checked = in_array( $key, $enabled_widgets ) ? 'checked' : '';
		echo "<label><input type='checkbox' name='artpulse_enabled_widgets[]' value='$key' $checked /> {$info['title']}</label><br>";
	}

		submit_button();
	?>
</form>
