<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use ArtPulse\Crm\DonationModel;
use ArtPulse\Crm\ContactModel;

class OrgCrmController
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/org/(?P<id>\d+)/donors', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_donors'],
            'permission_callback' => function () { return current_user_can('read'); },
            'args'                => [
                'id'   => ['validate_callback' => 'is_numeric'],
                'from' => ['validate_callback' => 'is_string'],
                'to'   => ['validate_callback' => 'is_string'],
            ],
        ]);

        register_rest_route('artpulse/v1', '/org/(?P<id>\d+)/audience', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_audience'],
            'permission_callback' => function () { return current_user_can('read'); },
            'args'                => [
                'id'  => ['validate_callback' => 'is_numeric'],
                'tag' => ['validate_callback' => 'is_string'],
            ],
        ]);
    }

    public static function get_donors(WP_REST_Request $req): WP_REST_Response
    {
        $org_id = absint($req['id']);
        $from   = sanitize_text_field($req->get_param('from'));
        $to     = sanitize_text_field($req->get_param('to'));
        $donors = DonationModel::query($org_id, $from, $to);
        return rest_ensure_response($donors);
    }

    public static function get_audience(WP_REST_Request $req): WP_REST_Response
    {
        $org_id = absint($req['id']);
        $tag    = sanitize_text_field($req->get_param('tag') ?? '');
        $audience = ContactModel::get_all($org_id, $tag);
        return rest_ensure_response($audience);
    }
}
