<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use ArtPulse\Crm\ContactModel;
use ArtPulse\Crm\DonationModel;

class GrantReportController
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/orgs/(?P<id>\\d+)/grant-report')) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/orgs/(?P<id>\\d+)/grant-report', [
            'methods'  => 'GET',
            'callback' => [self::class, 'export'],
            'permission_callback' => function () { return current_user_can('read'); },
            'args'     => [
                'id'     => ['validate_callback' => 'absint'],
                'format' => ['default' => 'csv'],
            ],
        ]);
        }
    }

    public static function export(WP_REST_Request $req)
    {
        $org_id = absint($req['id']);
        $donations = DonationModel::get_by_org($org_id);
        $stream = fopen('php://temp', 'w');
        fputcsv($stream, ['email', 'amount', 'date']);
        foreach ($donations as $d) {
            fputcsv($stream, [$d['user_id'], $d['amount'], $d['donated_at']]);
        }
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);
        return new WP_REST_Response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="grant-report.csv"',
        ]);
    }
}
