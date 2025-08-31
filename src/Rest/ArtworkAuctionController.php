<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Rest\Util\Auth;

class ArtworkAuctionController {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
               if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/artwork/(?P<artwork_id>\d+)/auction' ) ) {
                        register_rest_route(
                                ARTPULSE_API_NAMESPACE,
                                '/artwork/(?P<artwork_id>\d+)/auction',
                                array(
                                        'methods'             => 'GET',
                                        'callback'            => array( self::class, 'status' ),
                                        'permission_callback' => array( Auth::class, 'guard_read' ),
                                        'args'                => array(
                                                'artwork_id' => array(
                                                        'type'     => 'integer',
                                                        'required' => true,
                                                ),
                                        ),
                                )
                        );
                }
               if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/artwork/(?P<artwork_id>\d+)/bid' ) ) {
                        register_rest_route(
                                ARTPULSE_API_NAMESPACE,
                                '/artwork/(?P<artwork_id>\d+)/bid',
                                array(
                                        'methods'             => 'POST',
                                        'callback'            => array( self::class, 'bid' ),
                                        'permission_callback' => array( Auth::class, 'guard_read' ),
                                        'args'                => array(
                                                'artwork_id' => array(
                                                        'type'     => 'integer',
                                                        'required' => true,
                                                ),
                                                'amount'     => array( 'type' => 'number', 'minimum' => 0, 'required' => true ),
                                        ),
                                )
                        );
                }
        }

       private static function validate( int $artwork_id ): bool {
               return $artwork_id && get_post_type( $artwork_id ) === 'artpulse_artwork';
        }

       private static function ok( $data, int $status = 200 ): WP_REST_Response {
               return new WP_REST_Response( $data, $status );
       }

        public static function status( WP_REST_Request $req ): WP_REST_Response|WP_Error {
               $artwork_id = absint( $req['artwork_id'] );
               if ( ! self::validate( $artwork_id ) ) {
                        return new WP_Error( 'rest_invalid_param', 'Invalid artwork.', array( 'status' => 400 ) );
                }
               $enabled = get_post_meta( $artwork_id, 'artwork_auction_enabled', true ) === '1';
               $start   = get_post_meta( $artwork_id, 'artwork_auction_start', true );
               $end     = get_post_meta( $artwork_id, 'artwork_auction_end', true );
               $bids    = get_post_meta( $artwork_id, 'artwork_bids', true );
               $highest = 0.0;
               if ( is_array( $bids ) ) {
                       foreach ( $bids as $b ) {
                               if ( ( $b['amount'] ?? 0 ) > $highest ) {
                                       $highest = (float) $b['amount'];
                               }
                       }
               }
               return self::ok(
                        array(
                                'enabled'     => $enabled,
                                'start'       => $start,
                                'end'         => $end,
                                'highest_bid' => $highest,
                        )
               );
       }

        public static function bid( WP_REST_Request $req ): WP_REST_Response|WP_Error {
               $artwork_id = absint( $req['artwork_id'] );
               $amount     = (float) $req->get_param( 'amount' );
               if ( ! self::validate( $artwork_id ) ) {
                        return new WP_Error( 'rest_invalid_param', 'Invalid artwork.', array( 'status' => 400 ) );
                }
               if ( get_post_meta( $artwork_id, 'artwork_auction_enabled', true ) !== '1' ) {
                        return new WP_Error( 'rest_invalid_param', 'Auction disabled.', array( 'status' => 400 ) );
                }
               $now   = current_time( 'timestamp' );
               $start = strtotime( get_post_meta( $artwork_id, 'artwork_auction_start', true ) );
               $end   = strtotime( get_post_meta( $artwork_id, 'artwork_auction_end', true ) );
               if ( ( $start && $now < $start ) || ( $end && $now > $end ) ) {
                        return new WP_Error( 'rest_invalid_param', 'Auction closed.', array( 'status' => 400 ) );
                }
               $bids = get_post_meta( $artwork_id, 'artwork_bids', true );
               if ( ! is_array( $bids ) ) {
                       $bids = array();
               }
               $bids[] = array(
                        'user_id' => get_current_user_id(),
                        'amount'  => $amount,
                        'time'    => current_time( 'mysql' ),
               );
               update_post_meta( $artwork_id, 'artwork_bids', $bids );
               return self::ok( array( 'success' => true ) );
       }
}
