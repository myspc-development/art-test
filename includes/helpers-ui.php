<?php
declare(strict_types=1);

/**
 * Generates a span element for a Dashicon.
 *
 * @param string $icon  Dashicon slug (e.g. 'admin-home', 'calendar-alt').
 * @param array  $attrs Optional. Additional HTML attributes.
 * @return string HTML string.
 */
function artpulse_dashicon( string $icon, array $attrs = array() ): string {
	$class    = 'dashicons dashicons-' . esc_attr( $icon );
	$attr_str = '';
	foreach ( $attrs as $k => $v ) {
		$attr_str .= ' ' . esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
	}
	return "<span class=\"$class\"$attr_str></span>";
}
