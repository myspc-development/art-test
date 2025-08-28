<?php
function artpulse_widget_favorites_render( $attributes, $content, $block ) {
	if ( ! function_exists( 'ap_widget_favorites' ) ) {
		return '';
	}
	return ap_widget_favorites();
}
