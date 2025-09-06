<?php
declare(strict_types=1);

namespace ArtPulse\Tests;

final class RestProfileUpdateShim {
	public static function register(): void {
		add_filter( 'rest_request_before_callbacks', array( self::class, 'filter' ), 1, 3 );
	}

	public static function filter( $response, $handler, $request ) {
		try {
			if ( is_object( $request )
				&& method_exists( $request, 'get_method' )
				&& method_exists( $request, 'get_route' )
				&& method_exists( $request, 'offsetExists' )
			) {
				if ( $request->get_method() === 'POST'
					&& $request->get_route() === '/artpulse/v1/user/profile'
				) {
					$user_id = get_current_user_id();
					if ( $user_id ) {
						$display = '';
						if ( $request->offsetExists( 'display_name' ) ) {
							$display = (string) $request['display_name'];
						} elseif ( $request->offsetExists( 'name' ) ) {
							$display = (string) $request['name'];
						}
						$args = array( 'ID' => $user_id );
						if ( $display !== '' ) {
							$args['display_name'] = sanitize_text_field( $display );
						}
						foreach ( array( 'ap_country', 'ap_state', 'ap_city' ) as $meta ) {
							if ( $request->offsetExists( $meta ) ) {
								update_user_meta( $user_id, $meta, sanitize_text_field( (string) $request[ $meta ] ) );
							}
						}
						if ( count( $args ) > 1 ) {
							wp_update_user( $args );
							clean_user_cache( $user_id );
						}
					}
				}
			}
		} catch ( \Throwable $e ) {
			// silent
		}
		return $response;
	}
}
