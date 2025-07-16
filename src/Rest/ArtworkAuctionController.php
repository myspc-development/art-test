<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class ArtworkAuctionController
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/artwork/(?P<id>\d+)/auction', [
            'methods'  => 'GET',
            'callback' => [self::class, 'status'],
            'permission_callback' => function () {
                if (!current_user_can('read')) {
                    return new WP_Error('rest_forbidden', __('Unauthorized.', 'artpulse'), ['status' => 403]);
                }
                return true;
            },
            'args' => ['id' => ['validate_callback' => 'is_numeric']],
        ]);
        register_rest_route('artpulse/v1', '/artwork/(?P<id>\d+)/bid', [
            'methods'  => 'POST',
            'callback' => [self::class, 'bid'],
            'permission_callback' => function () {
                if (!current_user_can('read')) {
                    return new WP_Error('rest_forbidden', __('Unauthorized.', 'artpulse'), ['status' => 403]);
                }
                return true;
            },
            'args' => [
                'id'     => ['validate_callback' => 'is_numeric'],
                'amount' => ['validate_callback' => 'is_numeric'],
            ],
        ]);
    }

    private static function validate(int $id): bool
    {
        return $id && get_post_type($id) === 'artpulse_artwork';
    }

    public static function status(WP_REST_Request $req): WP_REST_Response|WP_Error
    {
        $id = absint($req->get_param('id'));
        if (!self::validate($id)) {
            return new WP_Error('invalid_artwork', 'Invalid artwork', ['status' => 404]);
        }
        $enabled = get_post_meta($id, 'artwork_auction_enabled', true) === '1';
        $start   = get_post_meta($id, 'artwork_auction_start', true);
        $end     = get_post_meta($id, 'artwork_auction_end', true);
        $bids    = get_post_meta($id, 'artwork_bids', true);
        $highest = 0.0;
        if (is_array($bids)) {
            foreach ($bids as $b) {
                if (($b['amount'] ?? 0) > $highest) {
                    $highest = (float) $b['amount'];
                }
            }
        }
        return rest_ensure_response([
            'enabled'     => $enabled,
            'start'       => $start,
            'end'         => $end,
            'highest_bid' => $highest,
        ]);
    }

    public static function bid(WP_REST_Request $req): WP_REST_Response|WP_Error
    {
        $id     = absint($req->get_param('id'));
        $amount = (float) $req->get_param('amount');
        if (!self::validate($id)) {
            return new WP_Error('invalid_artwork', 'Invalid artwork', ['status' => 404]);
        }
        if (get_post_meta($id, 'artwork_auction_enabled', true) !== '1') {
            return new WP_Error('auction_disabled', 'Auction disabled', ['status' => 400]);
        }
        $now   = current_time('timestamp');
        $start = strtotime(get_post_meta($id, 'artwork_auction_start', true));
        $end   = strtotime(get_post_meta($id, 'artwork_auction_end', true));
        if (($start && $now < $start) || ($end && $now > $end)) {
            return new WP_Error('auction_closed', 'Auction closed', ['status' => 400]);
        }
        $bids = get_post_meta($id, 'artwork_bids', true);
        if (!is_array($bids)) {
            $bids = [];
        }
        $bids[] = [
            'user_id' => get_current_user_id(),
            'amount'  => $amount,
            'time'    => current_time('mysql'),
        ];
        update_post_meta($id, 'artwork_bids', $bids);
        return rest_ensure_response(['success' => true]);
    }
}
