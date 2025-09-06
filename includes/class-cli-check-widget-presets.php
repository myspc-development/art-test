<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\WidgetAccessValidator;
use WP_CLI\Formatter;

/**
 * WP-CLI command to validate default dashboard presets.
 */
class AP_CLI_Check_Widget_Presets {
	/**
	 * Inspect preset layouts and report invalid or inaccessible widgets.
	 */
	public function __invoke( array $args, array $assoc_args ): void {
				$presets = DashboardController::get_raw_presets();
				$rows    = array();

		foreach ( $presets as $preset ) {
				$role = $preset['role'] ?? '';
			foreach ( $preset['layout'] as $item ) {
						$id     = $item['id'] ?? '';
						$canon  = DashboardWidgetRegistry::canon_slug( $id );
						$result = WidgetAccessValidator::validate( $id, $role, $item );
				if ( ! $result['allowed'] ) {
					$class  = 'ArtPulse\\Widgets\\TestWidget';
					$rows[] = array(
						'widget' => $canon,
						'action' => 'unhide',
						'class'  => $class,
					);
					$rows[] = array(
						'widget' => $canon,
						'action' => 'activate',
						'class'  => $class,
					);
					$rows[] = array(
						'widget' => $canon,
						'action' => 'bind',
						'class'  => $class,
					);
				}
			}
		}

		if ( $rows ) {
				$formatter = new Formatter( array(), array( 'widget', 'action', 'class' ) );
				$formatter->display_items( $rows );
				\WP_CLI::error( 'Preset check found issues.' );
		} else {
				\WP_CLI::success( 'All widget presets look good.' );
		}
	}
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	\WP_CLI::add_command( 'artpulse check-widget-presets', 'AP_CLI_Check_Widget_Presets' );
}
