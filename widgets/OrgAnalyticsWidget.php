<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
namespace ArtPulse\Widgets;

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\DashboardController;

if ( ! defined( 'ABSPATH' ) ) {
        exit; }

/**
 * Simple dashboard widget showing basic organization analytics.
 */

class OrgAnalyticsWidget {
        private static function is_preview(): bool {
                if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) && IS_DASHBOARD_BUILDER_PREVIEW ) {
                        return true;
                }
                return function_exists( 'apply_filters' ) && (bool) apply_filters( 'ap_is_builder_preview', false );
        }

        public static function can_view( int $user_id ): bool {
                if ( self::is_preview() ) {
                        return false;
                }
                $role = DashboardController::get_role( $user_id );
                return $role === 'organization' && user_can( $user_id, 'view_analytics' );
        }

	public static function register(): void {
		DashboardWidgetRegistry::register(
			self::get_id(),
			self::get_title(),
			'chart-bar',
			esc_html__( 'Basic traffic and engagement metrics.', 'artpulse' ),
			array( self::class, 'render' ),
			array(
				'roles'      => array( 'organization' ),
				'capability' => 'view_analytics',
				'section'    => self::get_section(),
			)
		);
	}

	public static function get_id(): string {
		return 'artpulse_analytics_widget'; }
	public static function get_title(): string {
		return esc_html__( 'Organization Analytics', 'artpulse' ); }
	public static function get_section(): string {
		return 'insights'; }

        public static function render( int $user_id = 0 ): string {
                if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) && IS_DASHBOARD_BUILDER_PREVIEW ) {
                        return '';
                }
                if ( defined( 'AP_TEST_FORCE_PREVIEW' ) && AP_TEST_FORCE_PREVIEW ) {
                        return '';
                }
                if ( self::is_preview() ) {
                        return '';
                }

                $user_id = $user_id ?: get_current_user_id();
                if ( ! self::can_view( $user_id ) ) {
                        return '<div class="ap-org-analytics-widget" data-widget-id="' . esc_attr( self::get_id() ) . '"><div class="notice notice-error"><p>' . esc_html__( 'You do not have access to view this widget.', 'artpulse' ) . '</p></div></div>';
                }
                return '<div class="ap-org-analytics-widget" data-widget-id="' . esc_attr( self::get_id() ) . '"><p>' . esc_html__( 'Basic traffic and engagement metrics will appear here.', 'artpulse' ) . '</p></div>';
        }
}

OrgAnalyticsWidget::register();
