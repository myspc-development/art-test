<?php
namespace ArtPulse\Admin;

use ArtPulse\Support\FileSystem;

class UpdatesTab {

	public static function register(): void {
		add_action( 'admin_post_ap_check_updates', array( self::class, 'check_updates' ) );
		add_action( 'admin_post_ap_run_update', array( self::class, 'run_update' ) );
		\register_activation_hook( ARTPULSE_PLUGIN_FILE, array( self::class, 'schedule_cron' ) );
		\register_deactivation_hook( ARTPULSE_PLUGIN_FILE, array( self::class, 'unschedule_cron' ) );
		add_action( 'ap_daily_update_check', array( self::class, 'maybe_auto_update' ) );
	}

	public static function schedule_cron(): void {
		if ( ! wp_next_scheduled( 'ap_daily_update_check' ) ) {
			wp_schedule_event( time(), 'daily', 'ap_daily_update_check' );
		}
	}

	public static function unschedule_cron(): void {
		\wp_clear_scheduled_hook( 'ap_daily_update_check' );
	}

	public static function maybe_auto_update(): void {
		$opts = get_option( 'artpulse_settings', array() );
		if ( empty( $opts['auto_update_enabled'] ) ) {
			return;
		}
		$result = self::check_updates( true );
		if ( $result === true ) {
			$update_result = self::run_update( true );
			if ( is_wp_error( $update_result ) ) {
				error_log( '🔧 Auto update failed: ' . $update_result->get_error_message() );
			}
		} elseif ( is_wp_error( $result ) ) {
			error_log( '🔧 Update check failed: ' . $result->get_error_message() );
		}
	}

	/**
	 * Execute an update using the latest GitHub release ZIP.
	 *
	 * @return bool|\WP_Error
	 */
	private static function do_update() {
		$opts  = self::get_repo_info();
		$repo  = $opts['url'];
		$token = $opts['token'];

		return self::zip_release_update( $repo, $token );
	}

	private static function parse_repo( string $url ): array {
		if ( ! str_contains( $url, '://' ) && substr_count( $url, '/' ) === 1 ) {
			return explode( '/', $url, 2 );
		}

		$url   = rtrim( $url, '/' );
		$url   = preg_replace( '/\.git$/', '', $url );
		$parts = parse_url( $url );
		$path  = trim( $parts['path'] ?? '', '/' );
		if ( strpos( $path, '/' ) === false ) {
			return array( '', '' );
		}
		[$owner, $repo] = explode( '/', $path, 2 );
		return array( $owner, $repo );
	}

	private static function get_repo_info(): array {
		$opts      = get_option( 'artpulse_settings', array() );
		$legacyUrl = $opts['github_repo'] ?? ( $opts['update_repo_url'] ?? '' );
		$legacyTok = $opts['update_access_token'] ?? '';

		return array(
			'url'    => get_option( 'ap_github_repo_url' ) ?: $legacyUrl,
			'branch' => $opts['update_branch'] ?? 'main',
			'token'  => get_option( 'ap_github_token' ) ?: $legacyTok,
		);
	}

	private static function redirect_to_updates( array $params = array() ): void {
		$url = admin_url( 'admin.php?page=artpulse-settings' );
		if ( $params ) {
			$url = add_query_arg( $params, $url );
		}
		wp_safe_redirect( $url . '#updates' );
		exit;
	}

	/**
	 * Check if an update is available.
	 *
	 * @param bool $silent Whether to suppress redirects/messages.
	 * @return bool|\WP_Error True if update available, false if not, WP_Error on failure.
	 */
	public static function check_updates( bool $silent = false ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			if ( $silent ) {
				return false;
			}
			wp_die( __( 'Unauthorized', 'artpulse' ) );
		}
		if ( ! $silent && ! check_admin_referer( 'ap_check_updates' ) ) {
			wp_die( __( 'Unauthorized', 'artpulse' ) );
		}
		$info = self::get_repo_info();
		if ( empty( $info['url'] ) ) {
			$err = new \WP_Error( 'missing_repo', 'Repository URL not configured' );
			update_option( 'ap_update_log', '❌ Update failed: ' . $err->get_error_message() );
			if ( ! $silent ) {
				self::redirect_to_updates( array( 'ap_update_error' => urlencode( $err->get_error_message() ) ) );
			}
			return $err;
		}
		[$owner, $repo] = self::parse_repo( $info['url'] );
		if ( empty( $owner ) || empty( $repo ) ) {
			$err = new \WP_Error( 'invalid_repo', 'Invalid repository URL' );
			update_option( 'ap_update_log', '❌ Update failed: ' . $err->get_error_message() );
			if ( ! $silent ) {
				self::redirect_to_updates( array( 'ap_update_error' => urlencode( $err->get_error_message() ) ) );
			}
			return $err;
		}
		$api  = "https://api.github.com/repos/{$owner}/{$repo}/commits/{$info['branch']}";
		$args = array(
			'headers' => array(
				'Accept'     => 'application/vnd.github.v3+json',
				'User-Agent' => 'ArtPulse-Updater',
			),
		);
		if ( ! empty( $info['token'] ) ) {
			$args['headers']['Authorization'] = 'token ' . $info['token'];
		}
		$response = wp_remote_get( $api, $args );
		if ( is_wp_error( $response ) ) {
			update_option( 'ap_update_log', '❌ Update failed: ' . $response->get_error_message() );
			if ( ! $silent ) {
				self::redirect_to_updates( array( 'ap_update_error' => urlencode( $response->get_error_message() ) ) );
			}
			return $response;
		}
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! isset( $body['sha'] ) ) {
			$err = new \WP_Error( 'invalid_response', 'Invalid response from GitHub' );
			update_option( 'ap_update_log', '❌ Update failed: ' . $err->get_error_message() );
			if ( ! $silent ) {
				self::redirect_to_updates( array( 'ap_update_error' => urlencode( $err->get_error_message() ) ) );
			}
			return $err;
		}
		$remote_sha  = $body['sha'];
		$current_sha = get_option( 'ap_current_repo_sha' );
		update_option( 'ap_update_last_check', current_time( 'mysql' ) );
		update_option( 'ap_update_log', '✅ Update checked at ' . current_time( 'mysql' ) );
		if ( $remote_sha !== $current_sha ) {
			update_option( 'ap_update_available', 1 );
			update_option( 'ap_update_remote_sha', $remote_sha );
			if ( ! $silent ) {
				self::redirect_to_updates( array( 'update_available' => '1' ) );
			}
			return true;
		}
		update_option( 'ap_update_available', 0 );
		if ( ! $silent ) {
			self::redirect_to_updates( array( 'update_checked' => '1' ) );
		}
		return false;
	}

	/**
	 * Perform the plugin update.
	 *
	 * @param bool $silent Whether to suppress redirects/messages.
	 * @return bool|\WP_Error True on success, WP_Error on failure.
	 */
	public static function run_update( bool $silent = false ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			if ( $silent ) {
				return new \WP_Error( 'permission_denied', 'Insufficient permissions' );
			}
			wp_die( __( 'Unauthorized', 'artpulse' ) );
		}
		if ( ! $silent && ! check_admin_referer( 'ap_run_update' ) ) {
			wp_die( __( 'Unauthorized', 'artpulse' ) );
		}
		error_log( '🔧 Starting update...' );
		$result = self::do_update();

		if ( $result === true ) {
			update_option( 'ap_last_update_time', current_time( 'mysql' ) );
			update_option( 'ap_update_log', '✅ Updated successfully on ' . current_time( 'mysql' ) );
			if ( ! $silent ) {
				self::redirect_to_updates( array( 'ap_update_success' => '1' ) );
			}
			return true;
		}

		update_option( 'ap_update_log', '❌ ' . $result->get_error_message() );
		if ( ! $silent ) {
			self::redirect_to_updates( array( 'ap_update_error' => urlencode( $result->get_error_message() ) ) );
		}
		return $result;
	}

	/**
	 * Download the latest GitHub release ZIP and replace plugin files.
	 */
	private static function zip_release_update( string $repo, string $token = '' ) {
		if ( empty( $repo ) ) {
			return new \WP_Error( 'no_repo', 'GitHub repo not configured.' );
		}

		$headers = array(
			'Accept'     => 'application/vnd.github+json',
			'User-Agent' => 'ArtPulse-Updater',
		);
		if ( $token ) {
			$headers['Authorization'] = 'token ' . $token;
		}

		$response = wp_remote_get(
			"https://api.github.com/repos/{$repo}/releases/latest",
			array(
				'headers' => $headers,
			)
		);
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$body     = json_decode( wp_remote_retrieve_body( $response ), true );
		$download = $body['zipball_url'] ?? '';
		if ( ! $download && ! empty( $body['assets'][0]['browser_download_url'] ) ) {
			$download = $body['assets'][0]['browser_download_url'];
		}
		if ( ! $download ) {
			return new \WP_Error( 'bad_api', 'Invalid release response.' );
		}

		include_once ABSPATH . 'wp-admin/includes/file.php';
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		$tmp = download_url( $download, 300, '', array( 'headers' => $headers ) );
		if ( is_wp_error( $tmp ) ) {
			return $tmp;
		}

		$plugin_dir = plugin_dir_path( ARTPULSE_PLUGIN_FILE );
		$temp_dir   = trailingslashit( get_temp_dir() ) . 'ap_update_' . wp_generate_password( 8, false );
		wp_mkdir_p( $temp_dir );
		$result = unzip_file( $tmp, $temp_dir );
		$files  = array();
		if ( ! is_wp_error( $result ) ) {
			$zip = new \ZipArchive();
			if ( $zip->open( $tmp ) === true ) {
				for ( $i = 0; $i < $zip->numFiles; $i++ ) {
					$name = $zip->getNameIndex( $i );
					if ( ! str_ends_with( $name, '/' ) ) {
						$files[] = $name;
					}
				}
				$zip->close();
			}
		}
		if ( ! is_wp_error( $result ) ) {
			$entries = array_values( array_filter( scandir( $temp_dir ), fn( $e ) => $e !== '.' && $e !== '..' ) );
			$src     = $temp_dir;
			if ( count( $entries ) === 1 && is_dir( $temp_dir . '/' . $entries[0] ) ) {
				$src = $temp_dir . '/' . $entries[0];
			}
			self::copy_recursive( $src, $plugin_dir );
		}
		self::delete_recursive( $temp_dir );
		FileSystem::safe_unlink( $tmp );

		if ( is_wp_error( $result ) ) {
			return $result;
		}
		update_option( 'ap_updated_files', $files );
		return true;
	}


	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Insufficient permissions', 'artpulse' ) );
		}

		if ( isset( $_GET['ap_update_success'] ) && $_GET['ap_update_success'] === '1' ) {
			echo '<div class="notice notice-success"><p>✅ Plugin updated successfully.</p></div>';
			$files = get_option( 'ap_updated_files', array() );
			if ( $files ) {
				echo '<ul>';
				foreach ( $files as $f ) {
					echo '<li>' . esc_html( $f ) . '</li>';
				}
				echo '</ul>';
			}
			delete_option( 'ap_updated_files' );
		}

		if ( isset( $_GET['ap_update_error'] ) ) {
			echo '<div class="notice notice-error"><p>❌ Update failed: ' . esc_html( $_GET['ap_update_error'] ) . '</p></div>';
		}

		$last_check  = get_option( 'ap_update_last_check' );
		$last_update = get_option( 'ap_last_update_time' );
		?>
		<h2 class="ap-card__title"><?php esc_html_e( 'Manual Update', 'artpulse' ); ?></h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'ap_check_updates' ); ?>
			<input type="hidden" name="action" value="ap_check_updates" />
			<button type="submit" class="button"><?php esc_html_e( 'Check for Updates', 'artpulse' ); ?></button>
		</form>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'ap_run_update' ); ?>
			<input type="hidden" name="action" value="ap_run_update" />
			<button type="submit" id="ap-update-btn" class="button button-primary">
				<?php esc_html_e( 'Update Now', 'artpulse' ); ?>
				<span class="spinner"></span>
			</button>
		</form>
		<p>
			<?php if ( $last_check ) : ?>
				<?php esc_html_e( 'Last Checked:', 'artpulse' ); ?> <?php echo esc_html( $last_check ); ?><br />
			<?php endif; ?>
			<?php if ( $last_update ) : ?>
				<?php esc_html_e( 'Last Updated:', 'artpulse' ); ?> <?php echo esc_html( $last_update ); ?>
			<?php endif; ?>
		</p>
		<?php
	}

	private static function copy_recursive( string $src, string $dest ): void {
		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			include_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $src, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::SELF_FIRST
		);
		foreach ( $iterator as $item ) {
			$target = $dest . '/' . $iterator->getSubPathName();
			if ( $item->isDir() ) {
				if ( ! $wp_filesystem->is_dir( $target ) ) {
					$wp_filesystem->mkdir( $target, FS_CHMOD_DIR );
				}
			} else {
				$wp_filesystem->copy( $item->getPathname(), $target, true, FS_CHMOD_FILE );
			}
		}
	}

	private static function delete_recursive( string $dir ): void {
		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			include_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$wp_filesystem->delete( $dir, true );
	}
}
