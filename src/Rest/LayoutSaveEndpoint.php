<?php
namespace ArtPulse\Rest;

class LayoutSaveEndpoint {

	public static function register(): void {
			add_action( 'wp_ajax_save_dashboard_layout', array( self::class, 'handle' ) );
			add_action( 'wp_ajax_nopriv_save_dashboard_layout', array( self::class, 'handle' ) );
	}

	public static function handle(): void {
		if ( ! is_user_logged_in() || ! current_user_can( 'read' ) ) {
				wp_send_json_error( null, 403 );
		}

			check_ajax_referer( 'ap_dashboard_nonce' );

			$user_id = get_current_user_id();
			$raw     = $_POST['layout'] ?? array();
			$raw     = wp_unslash( $raw );

		if ( is_string( $raw ) ) {
			$raw = json_decode( $raw, true );
		}

		if ( ! is_array( $raw ) ) {
			wp_send_json_error( 'Invalid layout format.' );
		}

			$valid_ids = array_column( \ArtPulse\Core\DashboardWidgetRegistry::get_definitions(), 'id' );
			$layout    = array();
		foreach ( $raw as $item ) {
			if ( is_array( $item ) && isset( $item['id'] ) ) {
				$id  = sanitize_key( $item['id'] );
				$vis = isset( $item['visible'] ) ? filter_var( $item['visible'], FILTER_VALIDATE_BOOLEAN ) : true;
			} elseif ( is_string( $item ) ) {
				$id  = sanitize_key( $item );
				$vis = true;
			} else {
				wp_send_json_error( 'Invalid layout data.' );
			}

			if ( in_array( $id, $valid_ids, true ) ) {
				$layout[] = array(
					'id'      => $id,
					'visible' => $vis,
				);
			}
		}

			update_user_meta( $user_id, 'ap_dashboard_layout', $layout );
			wp_send_json_success( array( 'message' => 'Layout saved.' ) );
	}
}
