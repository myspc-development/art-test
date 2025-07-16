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
    public static function can_export()
    {
        if (!current_user_can('manage_options')) {
            return new \WP_Error('rest_forbidden', __('Unauthorized.', 'artpulse'), ['status' => 403]);
        }
        return true;
    }

    /**
     * Export data as CSV. Implementation intentionally minimal.
     */
    public static function handle_export(\WP_REST_Request $request)
    {
        $type     = sanitize_text_field($request->get_param('type'));
        $event_id = absint($request->get_param('event_id'));

        if ($type !== 'attendance') {
            return new \WP_Error('invalid_type', 'Unsupported export type.', ['status' => 400]);
        }
        if (!$event_id) {
            return new \WP_Error('invalid_event', 'Invalid event.', ['status' => 400]);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'ap_tickets';
        $rows  = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE event_id = %d", $event_id), ARRAY_A);

        $stream = fopen('php://temp', 'w');
        fputcsv($stream, ['ticket_id', 'user_id', 'code', 'status', 'purchase_date']);
        foreach ($rows as $row) {
            fputcsv($stream, [$row['id'], $row['user_id'], $row['code'], $row['status'], $row['purchase_date']]);
        }
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return new \WP_REST_Response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="export.csv"',
        ]);
    }
}
