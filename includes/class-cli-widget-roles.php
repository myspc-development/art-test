<?php
if (!defined('ABSPATH')) { exit; }

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
     * [--export=<file>]
     * : Export mapping to JSON file.
     *
     * [--import=<file>]
     * : Import mapping from JSON file and save to options.
     */
    public function __invoke( array $args, array $assoc_args ): void {
        $map = DashboardWidgetRegistry::get_role_widget_map();
        if ( isset( $assoc_args['export'] ) ) {
            file_put_contents( $assoc_args['export'], wp_json_encode( $map, JSON_PRETTY_PRINT ) );
            \WP_CLI::success( 'Exported widget-role map.' );
            return;
        }
        if ( isset( $assoc_args['import'] ) ) {
            $file = $assoc_args['import'];
            if ( ! file_exists( $file ) ) {
                \WP_CLI::error( 'File not found.' );
            }
            $data = json_decode( file_get_contents( $file ), true );
            if ( ! is_array( $data ) ) {
                \WP_CLI::error( 'Invalid JSON.' );
            }
            update_option( 'artpulse_widget_roles', $data );
            \WP_CLI::success( 'Imported widget-role map.' );
            return;
        }
        \WP_CLI::print_value( $map, [ 'json' => true ] );
    }
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
    \WP_CLI::add_command( 'artpulse widget-roles', 'AP_CLI_Widget_Roles' );
}
