<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetRegistry;

/**
 * WP-CLI command to validate default dashboard presets.
 */
class AP_CLI_Check_Widget_Presets {
	/**
	 * Inspect preset layouts and report invalid or inaccessible widgets.
	 */
	public function __invoke( array $args, array $assoc_args ): void {
                $presets = DashboardController::get_raw_presets();
		$errors  = false;

		foreach ( $presets as $key => $preset ) {
			$role = $preset['role'];
			foreach ( $preset['layout'] as $item ) {
				$id     = $item['id'] ?? '';
				$config = DashboardWidgetRegistry::getById( $id );
				if ( ! $config ) {
					\WP_CLI::warning( "{$id} not registered in preset {$key}" );
					$errors = true;
					continue;
				}

				$roles = isset( $config['roles'] ) ? (array) $config['roles'] : array();
				if ( $roles && ! in_array( $role, $roles, true ) ) {
					\WP_CLI::warning( "{$id} not visible to role {$role} in preset {$key}" );
					$errors = true;
				}

				$cap = $config['capability'] ?? '';
				if ( $cap && $role !== 'administrator' ) {
					$role_obj = function_exists( 'get_role' ) ? get_role( $role ) : null;
					if ( ! $role_obj || ! $role_obj->has_cap( $cap ) ) {
						\WP_CLI::warning( "{$id} requires capability {$cap} not available to {$role} in preset {$key}" );
						$errors = true;
					}
				}
			}
		}

		if ( $errors ) {
			\WP_CLI::error( 'Preset check found issues.' );
		} else {
			\WP_CLI::success( 'All widget presets look good.' );
		}
	}
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	\WP_CLI::add_command( 'artpulse check-widget-presets', 'AP_CLI_Check_Widget_Presets' );
}
