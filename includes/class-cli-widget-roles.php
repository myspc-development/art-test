<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use ArtPulse\Core\DashboardWidgetRegistry;

/**
 * WP-CLI command to view or import/export widget-role mappings.
 */
class AP_CLI_Widget_Roles {
	/**
	 * Handle the command.
	 *
	 * ## OPTIONS
	 *
	 * [--export]
	 * : Output mapping as JSON to STDOUT. Redirect to save.
	 *
	 * [--import=<file>]
	 * : Import mapping from JSON file and save to options.
	 *
	 * ## EXAMPLES
	 *
	 *     wp widget-roles export > roles.json
	 *     wp widget-roles import roles.json
	 */
	public function __invoke( array $args, array $assoc_args ): void {
		$map = DashboardWidgetRegistry::get_role_widget_map();

		$sub = $args[0] ?? '';

		// Support `wp widget-roles export` or `--export`.
		if ( $sub === 'export' || isset( $assoc_args['export'] ) ) {
			\WP_CLI::print_value( $map, array( 'json' => true ) );
			return;
		}

		// Support `wp widget-roles import file.json` or `--import=file.json`.
		if ( $sub === 'import' || isset( $assoc_args['import'] ) ) {
			$file = $assoc_args['import'] ?? ( $args[1] ?? '' );
			if ( ! $file ) {
				\WP_CLI::error( 'Missing file.' );
			}
			if ( ! file_exists( $file ) ) {
				\WP_CLI::error( 'File not found.' );
			}
                        $data = json_decode( file_get_contents( $file ), true );
                        if ( JSON_ERROR_NONE !== json_last_error() ) {
                                \WP_CLI::error( 'Invalid JSON.' );
                        }
                        update_option( 'artpulse_widget_roles', $data );
			\WP_CLI::success( 'Imported widget-role map.' );
			return;
		}

		\WP_CLI::print_value( $map, array( 'json' => true ) );
	}
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	\WP_CLI::add_command( 'widget-roles', 'AP_CLI_Widget_Roles' );
	\WP_CLI::add_command( 'artpulse widget-roles', 'AP_CLI_Widget_Roles' );
}
