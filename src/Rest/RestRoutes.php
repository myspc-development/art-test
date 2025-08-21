<?php
namespace ArtPulse\Rest;

final class RestRoutes {
    /**
     * Register all REST controllers with the rest_api_init hook.
     */
    public static function register_all(): void {
        // Register controllers that expose routes
        // (each controller internally calls register_rest_route)
        AnalyticsPilotController::register();
        DashboardLayoutController::register();
        DashboardConfigController::register();
        EventAnalyticsController::register();
        PortfolioRestController::register();
        RsvpDbController::register();
        CalendarFeedController::register();
        ProfileMetricsController::register();
        SystemStatusController::register();
    }
}

