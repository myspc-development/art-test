<?php
namespace ArtPulse\Reporting;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class SnapshotExportController
{
    public static function register(): void
    {
        if (did_action('rest_api_init')) {
            self::register_routes();
        } else {
            add_action('rest_api_init', [self::class, 'register_routes']);
        }
    }

    public static function register_routes(): void
    {
        $args = [
            'org_id' => [ 'type' => 'integer', 'required' => true ],
            'period' => [ 'type' => 'string',  'required' => true ],
        ];
        register_rest_route('artpulse/v1', '/reporting/snapshot', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'summary'],
            'permission_callback' => [self::class, 'can_export'],
            'args'                => $args,
        ]);
        register_rest_route('artpulse/v1', '/reporting/snapshot.csv', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'csv'],
            'permission_callback' => [self::class, 'can_export'],
            'args'                => $args,
        ]);
        register_rest_route('artpulse/v1', '/reporting/snapshot.pdf', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'pdf'],
            'permission_callback' => [self::class, 'can_export'],
            'args'                => $args,
        ]);
    }

    public static function can_export()
    {
        if (!current_user_can('manage_options')) {
            return new WP_Error('rest_forbidden', __('Unauthorized.', 'artpulse'), ['status' => 403]);
        }
        return true;
    }

    private static function get_data(int $org_id, string $period): array
    {
        return SnapshotBuilder::build($org_id, $period);
    }

    public static function summary(WP_REST_Request $req): WP_REST_Response|WP_Error
    {
        $org_id = absint($req['org_id']);
        $period = sanitize_text_field($req['period']);
        $data   = self::get_data($org_id, $period);
        return rest_ensure_response($data);
    }

    public static function csv(WP_REST_Request $req): WP_REST_Response|WP_Error
    {
        $org_id = absint($req['org_id']);
        $period = sanitize_text_field($req['period']);
        $data   = self::get_data($org_id, $period);
        $title  = sprintf('%s Snapshot', $period);

        $path = SnapshotBuilder::generate_csv([
            'title' => $title,
            'data'  => $data,
        ]);
        $csv = file_get_contents($path);
        unlink($path);
        return new WP_REST_Response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="snapshot.csv"',
        ]);
    }

    public static function pdf(WP_REST_Request $req): WP_REST_Response|WP_Error
    {
        $org_id = absint($req['org_id']);
        $period = sanitize_text_field($req['period']);
        $data   = self::get_data($org_id, $period);
        $title  = sprintf('%s Snapshot', $period);

        $path = SnapshotBuilder::generate_pdf([
            'title' => $title,
            'data'  => $data,
        ]);
        $pdf = file_get_contents($path);
        unlink($path);
        return new WP_REST_Response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="snapshot.pdf"',
        ]);
    }
}
