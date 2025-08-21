<?php
namespace ArtPulse\Rest;

final class RestRoutes {
    public static function register_routes(): void {
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
        SystemStatusController::register(); // new
    }
}

// Ensure registration happens in tests and runtime
add_action('rest_api_init', [RestRoutes::class, 'register_routes']);
