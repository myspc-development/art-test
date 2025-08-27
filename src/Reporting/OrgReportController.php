<?php
namespace ArtPulse\Reporting;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Support\FileSystem;

class OrgReportController
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
        if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/orgs/(?P<id>\d+)/report')) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/orgs/(?P<id>\d+)/report', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'download'],
            'permission_callback' => [self::class, 'can_download'],
            'args'                => [
                'id'     => [ 'type' => 'integer' ],
                'type'   => [
                    'required'          => true,
                    'validate_callback' => fn($t) => in_array($t, ['engagement','donors','grant'], true),
                ],
                'format' => [
                    'default'           => 'csv',
                    'validate_callback' => fn($f) => in_array($f, ['csv','pdf'], true),
                ],
            ],
        ]);
        }
    }

    public static function can_download()
    {
        if (!current_user_can('manage_options')) {
            return new WP_Error('rest_forbidden', __('Unauthorized.', 'artpulse'), ['status' => 403]);
        }
        return true;
    }

    public static function download(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $org_id = absint($request['id']);
        $type   = sanitize_text_field($request['type']);
        $format = sanitize_text_field($request['format']);

        $data = [
            'Org ID' => $org_id,
            'Type'   => $type,
            'From'   => $request['from'] ?? '',
            'To'     => $request['to'] ?? '',
        ];

        if ($format === 'csv') {
            $path = SnapshotBuilder::generate_csv([
                'title' => 'Org Report',
                'data'  => $data,
            ]);
            $content = file_get_contents($path);
            FileSystem::safe_unlink($path);
            return new WP_REST_Response($content, 200, [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename="org-report.csv"',
            ]);
        }

        $path = SnapshotBuilder::generate_pdf([
            'title' => 'Org Report',
            'data'  => $data,
        ]);
        $content = file_get_contents($path);
        FileSystem::safe_unlink($path);
        return new WP_REST_Response($content, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="org-report.pdf"',
        ]);
    }
}
