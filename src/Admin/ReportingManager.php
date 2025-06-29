<?php
namespace ArtPulse\Admin;

/**
 * Handles CSV exports and reporting endpoints.
 */
class ReportingManager
{
    /**
     * Hook into WordPress actions.
     */
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    /**
     * Register REST API routes for admin exports.
     */
    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/admin/export', [
            'methods'  => 'GET',
            'callback' => [self::class, 'handle_export'],
            'permission_callback' => [self::class, 'can_export'],
        ]);
    }

    /**
     * Check current user capability.
     */
    public static function can_export(): bool
    {
        return current_user_can('manage_options');
    }

    /**
     * Export data as CSV. Implementation intentionally minimal.
     */
    public static function handle_export(\WP_REST_Request $request)
    {
        // In a full implementation this would stream data via fputcsv().
        return rest_ensure_response(['status' => 'export placeholder']);
    }
}
