<?php
namespace ArtPulse\Helpers;

class WidgetHelpers {
	public static function render_callback_output( $callback ) {
		ob_start();
		if ( is_callable( $callback ) ) {
			call_user_func( $callback );
		}
		return ob_get_clean();
	}
}
