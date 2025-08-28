<?php
namespace ArtPulse\Admin;

use ArtPulse\Reporting\SnapshotBuilder;
use ArtPulse\Support\WpAdminFns;
use ArtPulse\Support\FileSystem;

/**
 * Admin page to generate reporting snapshots on demand.
 */
class SnapshotPage {

	/**
	 * Register hooks.
	 */
	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'addMenu' ) );
	}

	/**
	 * Add submenu item under ArtPulse settings.
	 */
	public static function addMenu(): void {
		add_submenu_page(
			'artpulse-settings',
			__( 'Snapshots', 'artpulse' ),
			__( 'Snapshots', 'artpulse' ),
			'manage_options',
			'ap-snapshots',
			array( self::class, 'render' )
		);
	}

	/**
	 * Render the page and handle form submissions.
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Insufficient permissions', 'artpulse' ) );
		}

		if ( ! empty( $_POST['ap_generate_snapshot'] ) && check_admin_referer( 'ap_generate_snapshot' ) ) {
			$title  = sanitize_text_field( $_POST['title'] ?? 'Snapshot' );
			$format = sanitize_text_field( $_POST['format'] ?? 'csv' );

			$data = array(
				'Generated' => current_time( 'mysql' ),
			);

			if ( $format === 'pdf' ) {
				$path = SnapshotBuilder::generate_pdf(
					array(
						'title' => $title,
						'data'  => $data,
					)
				);
				$type = 'application/pdf';
			} else {
				$path = SnapshotBuilder::generate_csv(
					array(
						'title' => $title,
						'data'  => $data,
					)
				);
				$type = 'text/csv';
			}

			if ( $path && file_exists( $path ) ) {
				header( 'Content-Type: ' . $type );
				header( 'Content-Disposition: attachment; filename="' . basename( $path ) . '"' );
				readfile( $path );
				FileSystem::safe_unlink( $path );
				exit;
			}
		}

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Generate Snapshot', 'artpulse' ) . '</h1>';
		echo '<form method="post">';
		\wp_nonce_field( 'ap_generate_snapshot' );
		echo '<p><label>' . esc_html__( 'Title', 'artpulse' ) . ' <input type="text" name="title" required></label></p>';
		echo '<p><label>' . esc_html__( 'Format', 'artpulse' ) . ' <select name="format"><option value="csv">CSV</option><option value="pdf">PDF</option></select></label></p>';
		WpAdminFns::submit_button( __( 'Generate', 'artpulse' ), 'primary', 'ap_generate_snapshot' );
		echo '</form>';
		echo '</div>';
	}
}
