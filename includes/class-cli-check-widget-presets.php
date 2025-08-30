<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use ArtPulse\Core\DashboardController;
use ArtPulse\Core\WidgetAccessValidator;

/**
 * WP-CLI command to validate default dashboard presets.
 */
class AP_CLI_Check_Widget_Presets {
	/**
	 * Inspect preset layouts and report invalid or inaccessible widgets.
	 */
	public function __invoke( array $args, array $assoc_args ): void {
		$presets = DashboardController::get_default_presets();
		$errors  = false;

		foreach ( $presets as $key => $preset ) {
			$role = $preset['role'];
                        foreach ( $preset['layout'] as $item ) {
                                $id     = $item['id'] ?? '';
                                $result = WidgetAccessValidator::validate( $id, $role, $item );
                                if ( ! $result['allowed'] ) {
                                        switch ( $result['reason'] ) {
                                                case 'unregistered':
                                                        \WP_CLI::warning( "{$id} not registered in preset {$key}" );
                                                        break;
                                                case 'role_mismatch':
                                                        \WP_CLI::warning( "{$id} not visible to role {$role} in preset {$key}" );
                                                        break;
                                                case 'missing_capability':
                                                        $cap = $result['cap'] ?? '';
                                                        \WP_CLI::warning( "{$id} requires capability {$cap} not available to {$role} in preset {$key}" );
                                                        break;
                                        }
                                        $errors = true;
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
