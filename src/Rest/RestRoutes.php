<?php
namespace ArtPulse\Rest;

final class RestRoutes {
	/**
	 * Register all REST controllers.
	 */
	public static function register_all(): void {
		foreach ( array(
			AnalyticsPilotController::class,
			DashboardConfigController::class,
			EventAnalyticsController::class,
			PortfolioRestController::class,
			RsvpDbController::class,
			CalendarFeedController::class,
			ProfileMetricsController::class,
			RouteAudit::class,
			SystemStatusController::class,
			UserAccountRestController::class,
			DashboardPreviewController::class,
			DirectoryController::class,
			\ArtPulse\Reporting\BudgetExportController::class,
		) as $ctrl ) {
			try {
				$ctrl::register();
			} catch ( \Throwable $e ) {
				error_log( $e->getMessage() );
				throw $e;
			}
		}
	}

	/**
	 * Backward-compatibility wrapper.
	 */
	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_all' ) );
	}
}
