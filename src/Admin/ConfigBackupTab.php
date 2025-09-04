<?php
namespace ArtPulse\Admin;

class ConfigBackupTab {

	public static function register(): void {
		add_action( 'admin_post_ap_export_config', array( self::class, 'handle_export' ) );
		add_action( 'admin_post_ap_import_config', array( self::class, 'handle_import' ) );
	}

	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Insufficient permissions', 'artpulse' ) );
		}

		if ( isset( $_GET['import_success'] ) ) {
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings imported.', 'artpulse' ) . '</p></div>';
		} elseif ( isset( $_GET['import_error'] ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Invalid backup file.', 'artpulse' ) . '</p></div>';
		}
		?>
		<h2 class="ap-card__title"><?php esc_html_e( 'Export Settings', 'artpulse' ); ?></h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'ap_export_config' ); ?>
			<input type="hidden" name="action" value="ap_export_config" />
			<button type="submit" class="button"><?php esc_html_e( 'Download Backup', 'artpulse' ); ?></button>
		</form>
		<hr/>
		<h2 class="ap-card__title"><?php esc_html_e( 'Import Settings', 'artpulse' ); ?></h2>
		<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'ap_import_config' ); ?>
			<input type="hidden" name="action" value="ap_import_config" />
			<input type="file" name="ap_config_file" accept=".json" required />
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Upload', 'artpulse' ); ?></button>
		</form>
		<?php
	}

	private static function get_options(): array {
		global $wpdb;
		$names   = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'artpulse_%' OR option_name LIKE 'ap_%'" );
		$options = array();
		foreach ( $names as $name ) {
			$options[ $name ] = get_option( $name );
		}
		return $options;
	}

	public static function handle_export(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Insufficient permissions', 'artpulse' ) );
		}
		check_admin_referer( 'ap_export_config' );
		$options = self::get_options();
		$json    = wp_json_encode( $options, JSON_PRETTY_PRINT );
		header( 'Content-Type: application/json' );
		header( 'Content-Disposition: attachment; filename="artpulse-config.json"' );
		echo $json;
		exit;
	}

	public static function handle_import(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Insufficient permissions', 'artpulse' ) );
		}
                check_admin_referer( 'ap_import_config' );

                if ( ! isset( $_FILES['ap_config_file'] ) || empty( $_FILES['ap_config_file']['tmp_name'] ) ) {
                        wp_safe_redirect( add_query_arg( 'import_error', '1', wp_get_referer() ?: admin_url( 'admin.php?page=artpulse-settings#config_backup' ) ) );
                        exit;
                }

                $file = $_FILES['ap_config_file'];

                if ( $file['size'] > 1024 * 1024 ) {
                        wp_safe_redirect( add_query_arg( 'import_error', '1', wp_get_referer() ?: admin_url( 'admin.php?page=artpulse-settings#config_backup' ) ) );
                        exit;
                }

                $check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], array( 'json' => 'application/json' ) );
                if ( 'json' !== ( $check['ext'] ?? '' ) || 'application/json' !== ( $check['type'] ?? '' ) ) {
                        wp_safe_redirect( add_query_arg( 'import_error', '1', wp_get_referer() ?: admin_url( 'admin.php?page=artpulse-settings#config_backup' ) ) );
                        exit;
                }

                $json = file_get_contents( $file['tmp_name'] );
                try {
                        $data = json_decode( $json, true, 512, JSON_THROW_ON_ERROR );
                } catch ( \JsonException $e ) {
                        wp_safe_redirect( add_query_arg( 'import_error', '1', wp_get_referer() ?: admin_url( 'admin.php?page=artpulse-settings#config_backup' ) ) );
                        exit;
                }

                if ( ! is_array( $data ) ) {
                        wp_safe_redirect( add_query_arg( 'import_error', '1', wp_get_referer() ?: admin_url( 'admin.php?page=artpulse-settings#config_backup' ) ) );
                        exit;
                }

                $allowed_options = array_keys( self::get_options() );

                foreach ( $data as $name => $value ) {
                        if ( is_string( $name ) && in_array( $name, $allowed_options, true ) ) {
                                update_option( $name, $value );
                        }
                }

                wp_safe_redirect( add_query_arg( 'import_success', '1', admin_url( 'admin.php?page=artpulse-settings#config_backup' ) ) );
                exit;
        }
}
