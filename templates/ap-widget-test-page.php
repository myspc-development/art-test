<?php
/**
 * Template Name: AP Widget Test Page
 */

use ArtPulse\Admin\DashboardWidgetTools;

if ( ! is_user_logged_in() ) {
	echo '<p>' . esc_html__( 'Please log in to view widgets.', 'artpulse' ) . '</p>';
	return;
}

$widgets = DashboardWidgetTools::get_role_widgets_for_current_user();
foreach ( $widgets as $widget ) {
	echo do_shortcode( '[ap_widget id="' . esc_attr( $widget['id'] ) . '"]' );
}
