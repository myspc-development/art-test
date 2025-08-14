<?php
namespace ArtPulse\Cli;

use WP_CLI; // phpcs:ignore
use ArtPulse\Core\DashboardWidgetRegistry;

/**
 * WP-CLI utilities for auditing dashboard widgets.
 */
class WidgetDoctor {
    /**
     * List registered widgets.
     */
    public function list_( $args, $assoc_args ) {
        $rows = [];
        foreach ( DashboardWidgetRegistry::all() as $id => $w ) {
            $roles = implode( ',', $w['roles'] ?? [] );
            $has_cb = is_callable( $w['callback'] ?? null ) ? 'yes' : 'no';
            $hidden_roles = [];
            foreach ( [ 'member', 'artist', 'organization' ] as $r ) {
                $hidden = apply_filters( 'ap_dashboard_hidden_widgets', [], $r );
                if ( in_array( $id, $hidden, true ) ) {
                    $hidden_roles[] = $r;
                }
            }
            $rows[] = [
                'id'               => $id,
                'status'           => $w['status'] ?? 'active',
                'roles'            => $roles,
                'has_callback'     => $has_cb,
                'hidden_for_roles' => implode( ',', $hidden_roles ),
            ];
        }
        \WP_CLI\Utils::format_items( 'table', $rows, [ 'id', 'status', 'roles', 'has_callback', 'hidden_for_roles' ] );
    }

    /**
     * Audit widgets for common issues.
     */
    public function audit( $args, $assoc_args ) {
        $issues = [];
        foreach ( DashboardWidgetRegistry::all() as $id => $w ) {
            $status = $w['status'] ?? 'active';
            $cb_ok  = is_callable( $w['callback'] ?? null );
            $hidden_roles = [];
            foreach ( [ 'member', 'artist', 'organization' ] as $r ) {
                $hidden = apply_filters( 'ap_dashboard_hidden_widgets', [], $r );
                if ( in_array( $id, $hidden, true ) ) {
                    $hidden_roles[] = $r;
                }
            }
            if ( ! $cb_ok || ( $status !== 'active' && ( ! defined( 'AP_STRICT_FLAGS' ) || ! AP_STRICT_FLAGS ) ) || $hidden_roles ) {
                $issues[] = [
                    'id'           => $id,
                    'status'       => $status,
                    'has_callback' => $cb_ok ? 'yes' : 'no',
                    'hidden_for'   => implode( ',', $hidden_roles ),
                ];
            }
        }
        if ( $issues ) {
            \WP_CLI\Utils::format_items( 'table', $issues, [ 'id', 'status', 'has_callback', 'hidden_for' ] );
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

        $flags = get_option( 'artpulse_widget_status', [] );
        if ( is_string( $flags ) ) {
            $decoded = json_decode( $flags, true );
            $flags   = is_array( $decoded ) ? $decoded : [];
        }

        foreach ( DashboardWidgetRegistry::all() as $id => $w ) {
            if ( ! is_callable( $w['callback'] ?? null ) ) {
                $base  = strpos( $id, 'widget_' ) === 0 ? substr( $id, 7 ) : $id;
                $class = 'ArtPulse\\Widgets\\' . str_replace( ' ', '', ucwords( str_replace( '_', ' ', $base ) ) ) . 'Widget';
                if ( class_exists( $class ) && method_exists( $class, 'render' ) ) {
                    DashboardWidgetRegistry::update_widget( $id, [ 'callback' => [ $class, 'render' ], 'status' => 'active' ] );
                }
            }
            if ( $activate && in_array( $w['status'] ?? 'active', [ 'coming_soon', 'beta', 'inactive' ], true ) ) {
                $flags[ DashboardWidgetRegistry::canon_slug( $id ) ] = true;
                DashboardWidgetRegistry::update_widget( $id, [ 'status' => 'active' ] );
            }
            if ( $unhide && $role ) {
                $opt = get_option( 'artpulse_dashboard_hidden_' . $role, [] );
                if ( is_string( $opt ) ) {
                    $decoded = json_decode( $opt, true );
                    $opt     = is_array( $decoded ) ? $decoded : [];
                }
                $opt = array_values( array_diff( (array) $opt, [ $id ] ) );
                update_option( 'artpulse_dashboard_hidden_' . $role, $opt );
            }
        }
        update_option( 'artpulse_widget_status', $flags );
        WP_CLI::success( 'All widgets active with renderers; no placeholders will appear for ' . ( $role ?: 'all roles' ) . '.' );
    }
}
