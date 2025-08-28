<?php
namespace ArtPulse\Cli;

use WP_CLI; // phpcs:ignore
use ArtPulse\Core\DashboardWidgetRegistry;

/**
 * WP-CLI utilities for auditing dashboard widgets.
 */
class WidgetDoctor {
	/**
	 * List registered dashboard widgets.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - json
	 * ---
	 *
	 * @subcommand list
	 * @synopsis [--format=<format>]
	 */
	public function list_( $args, $assoc_args ) {
		$rows = array();
		foreach ( DashboardWidgetRegistry::all() as $id => $w ) {
			$roles  = implode( ',', $w['roles'] ?? array() );
			$rows[] = array(
				'id'    => $id,
				'roles' => $roles,
			);
		}
		$format = $assoc_args['format'] ?? 'table';
		\WP_CLI\Utils\format_items( $format, $rows, array( 'id', 'roles' ) );
	}

	/**
	 * Audit widgets for common issues.
	 */
	public function audit( $args, $assoc_args ) {
		$issues = array();
		foreach ( DashboardWidgetRegistry::all() as $id => $w ) {
			$status       = $w['status'] ?? 'active';
			$cb_ok        = is_callable( $w['callback'] ?? null );
			$hidden_roles = array();
			foreach ( array( 'member', 'artist', 'organization' ) as $r ) {
				$hidden = apply_filters( 'ap_dashboard_hidden_widgets', array(), $r );
				if ( in_array( $id, $hidden, true ) ) {
					$hidden_roles[] = $r;
				}
			}
			if ( ! $cb_ok || ( $status !== 'active' && ( ! defined( 'AP_STRICT_FLAGS' ) || ! AP_STRICT_FLAGS ) ) || $hidden_roles ) {
				$issues[] = array(
					'id'           => $id,
					'status'       => $status,
					'has_callback' => $cb_ok ? 'yes' : 'no',
					'hidden_for'   => implode( ',', $hidden_roles ),
				);
			}
		}
		if ( $issues ) {
			\WP_CLI\Utils\format_items( 'table', $issues, array( 'id', 'status', 'has_callback', 'hidden_for' ) );
			WP_CLI::error( 'Widget issues found.' );
		}
		WP_CLI::success( 'All widgets active with renderers.' );
	}

	/**
	 * Attempt to fix common widget issues.
	 *
	 * ## OPTIONS
	 *
	 * [--role=<role>]
	 * [--activate-all]
	 * [--unhide]
	 */
	public function fix( $args, $assoc_args ) {
		$role     = $assoc_args['role'] ?? '';
		$activate = isset( $assoc_args['activate-all'] );
		$unhide   = isset( $assoc_args['unhide'] );

		$flags = get_option( 'artpulse_widget_status', array() );
		if ( is_string( $flags ) ) {
			$decoded = json_decode( $flags, true );
			$flags   = is_array( $decoded ) ? $decoded : array();
		}

		foreach ( DashboardWidgetRegistry::all() as $id => $w ) {
			if ( ! is_callable( $w['callback'] ?? null ) ) {
				$base  = strpos( $id, 'widget_' ) === 0 ? substr( $id, 7 ) : $id;
				$class = 'ArtPulse\\Widgets\\' . str_replace( ' ', '', ucwords( str_replace( '_', ' ', $base ) ) ) . 'Widget';
				if ( class_exists( $class ) && method_exists( $class, 'render' ) ) {
					DashboardWidgetRegistry::update_widget(
						$id,
						array(
							'callback' => array( $class, 'render' ),
							'status'   => 'active',
						)
					);
				}
			}
			if ( $activate && in_array( $w['status'] ?? 'active', array( 'coming_soon', 'beta', 'inactive' ), true ) ) {
				$flags[ DashboardWidgetRegistry::canon_slug( $id ) ] = true;
				DashboardWidgetRegistry::update_widget( $id, array( 'status' => 'active' ) );
			}
			if ( $unhide && $role ) {
				$opt = get_option( 'artpulse_dashboard_hidden_' . $role, array() );
				if ( is_string( $opt ) ) {
					$decoded = json_decode( $opt, true );
					$opt     = is_array( $decoded ) ? $decoded : array();
				}
				$opt = array_values( array_diff( (array) $opt, array( $id ) ) );
				update_option( 'artpulse_dashboard_hidden_' . $role, $opt );
			}
		}
		update_option( 'artpulse_widget_status', $flags );
		WP_CLI::success( 'All widgets active with renderers; no placeholders will appear for ' . ( $role ?: 'all roles' ) . '.' );
	}
}
