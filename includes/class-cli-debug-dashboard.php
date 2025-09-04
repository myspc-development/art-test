<?php
namespace ArtPulse\CLI;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Widgets\Placeholder\ApPlaceholderWidget;
use WP_CLI;
use function WP_CLI\Utils\format_items;
use WP_REST_Request;

/**
 * WP-CLI command to inspect dashboard layouts and widget registration.
 */
class DebugDashboardCommand {
	/**
	 * Debug dashboard layout for a user and role.
	 *
	 * ## OPTIONS
	 *
	 * [--user=<id>]
	 * : User ID to inspect. Defaults to the current user.
	 *
	 * [--role=<slug>]
	 * : Override role resolution for preview/debug.
	 *
	 * ## EXAMPLES
	 *
	 *     wp artpulse debug-dashboard
	 *     wp artpulse debug-dashboard --user=123
	 *     wp artpulse debug-dashboard --user=123 --role=artist
	 */
	public function __invoke( array $args, array $assoc_args ): void {
		$user_id = isset( $assoc_args['user'] ) ? (int) $assoc_args['user'] : get_current_user_id();
		if ( ! $user_id ) {
			WP_CLI::error( 'Unable to determine user ID. Use --user to specify one.' );
		}

		$role = isset( $assoc_args['role'] )
			? sanitize_key( $assoc_args['role'] )
			: DashboardController::get_role( $user_id );

                WP_CLI::log( sprintf( 'User ID: %1$d', $user_id ) );
                WP_CLI::log( sprintf( 'Role: %1$s', $role ) );

		$raw_layout = DashboardController::get_user_dashboard_layout( $user_id );

		// Retrieve preset layout for role.
		$presets       = DashboardController::get_default_presets();
		$preset_layout = array();
		foreach ( $presets as $preset ) {
			if ( ( $preset['role'] ?? '' ) === $role ) {
				$preset_layout = $preset['layout'];
				break;
			}
		}

		// Filter the raw layout using the controller's helper.
		$filtered_layout = $raw_layout;
		try {
			$method = new \ReflectionMethod( DashboardController::class, 'filter_accessible_layout' );
			$method->setAccessible( true );
			/** @var array $filtered_layout */
			$filtered_layout = $method->invoke( null, $raw_layout, $role );
		} catch ( \ReflectionException $e ) {
			// If reflection fails, fall back to raw layout.
		}

		$formatter = static function ( array $layout ): array {
			return array_map(
				static function ( $row ) {
					return array( 'id' => $row['id'] ?? '' );
				},
				$layout
			);
		};

		WP_CLI::log( 'Raw Layout:' );
		format_items( 'table', $formatter( $raw_layout ), array( 'id' ) );

		WP_CLI::log( 'Filtered Layout:' );
		format_items( 'table', $formatter( $filtered_layout ), array( 'id' ) );

		WP_CLI::log( 'Preset Layout:' );
		format_items( 'table', $formatter( $preset_layout ), array( 'id' ) );

		$registered   = DashboardWidgetRegistry::get_all();
		$raw_ids      = array_map( static fn( $r ) => $r['id'] ?? '', $raw_layout );
		$filtered_ids = array_map( static fn( $r ) => $r['id'] ?? '', $filtered_layout );
		$skipped      = array_diff( $raw_ids, $filtered_ids );

		if ( $skipped ) {
			foreach ( $skipped as $id ) {
				$reason = ! isset( $registered[ $id ] ) ? 'unregistered' : 'capability';
				WP_CLI::warning( "Skipped: {$id} ({$reason})" );
			}
		}

		$missing = array();
		$rows    = array();
		foreach ( $filtered_layout as $item ) {
			$id = $item['id'] ?? '';
			if ( ! $id ) {
				continue;
			}
			if ( ! isset( $registered[ $id ] ) ) {
				$missing[] = $id;
			}
			ob_start();
			ap_render_widget( $id, $user_id );
			$html        = trim( ob_get_clean() );
			$def         = $registered[ $id ] ?? array();
			$placeholder = isset( $def['class'] ) && $def['class'] === ApPlaceholderWidget::class;
			if ( $placeholder && $html === '' ) {
				WP_CLI::warning( "Placeholder widget: {$id}" );
			}
			$rows[] = array(
				'id'          => $id,
				'rendered'    => $html !== '' ? 'yes' : 'no',
				'placeholder' => $placeholder ? 'yes' : 'no',
			);
		}

		if ( $missing ) {
			foreach ( array_unique( $missing ) as $id ) {
				WP_CLI::warning( "Widget not registered: {$id}" );
			}
		} else {
			WP_CLI::success( 'All widgets are valid.' );
		}

		WP_CLI::log( 'Render Check:' );
		format_items( 'table', $rows, array( 'id', 'rendered', 'placeholder' ) );

		$req1 = new WP_REST_Request( 'GET', '/artpulse/v1/role-widget-map' );
		$res1 = rest_do_request( $req1 );
		WP_CLI::log( 'REST /role-widget-map: ' . wp_json_encode( $res1->get_data() ) );

		$req2 = new WP_REST_Request( 'GET', '/artpulse/v1/ap_dashboard_layout' );
		$req2->set_param( 'user_id', $user_id );
		$res2 = rest_do_request( $req2 );
		WP_CLI::log( 'REST /ap_dashboard_layout: ' . wp_json_encode( $res2->get_data() ) );
	}
}
