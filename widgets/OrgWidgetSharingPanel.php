<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
namespace ArtPulse\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}

/**
 * Panel to provide sharing and embed code for organization widgets.
 */
class OrgWidgetSharingPanel {

	public static function register(): void {
		add_action( 'add_meta_boxes', array( self::class, 'add_box' ) );
	}

	public static function add_box(): void {
		add_meta_box( 'ap_widget_sharing', __( 'Widget Sharing', 'artpulse' ), array( self::class, 'render' ), 'dashboard_widget', 'side' );
	}

	public static function render(): void {
		$id   = get_the_ID();
		$code = '<script src="' . esc_url( rest_url( 'widgets/embed.js' ) ) . '?id=' . $id . '"></script>';
		echo '<div class="dashboard-widget" data-widget-id="org_widget_sharing"><div class="inside">';
		echo '<p>' . esc_html__( 'Copy this code to embed on another site:', 'artpulse' ) . '</p>';
		echo '<textarea readonly style="width:100%" rows="3">' . esc_textarea( $code ) . '</textarea>';
		echo '</div></div>';
	}
}

OrgWidgetSharingPanel::register();
