<?php
namespace ArtPulse\Rest;

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Rest\Util\Auth;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class WidgetSettingsRestController {

	public static function register(): void {
		if ( did_action( 'rest_api_init' ) ) {
			self::register_routes();
		} else {
			add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
		}
	}

        public static function register_routes(): void {
                if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/widget-settings/(?P<id>[a-z0-9_-]+)' ) ) {
                        register_rest_route(
                                ARTPULSE_API_NAMESPACE,
                                '/widget-settings/(?P<id>[a-z0-9_-]+)',
                                array(
                                        array(
                                                'methods'             => 'GET',
                                                'callback'            => array( self::class, 'get_settings' ),
                                                'permission_callback' => array( Auth::class, 'guard_read' ),
                                        ),
                                        array(
                                                'methods'             => 'POST',
                                                'callback'            => array( self::class, 'save_settings' ),
                                                'permission_callback' => array( self::class, 'permissions_save_settings' ),
                                                'args'               => array(
                                                        'settings' => array(
                                                                'type'              => 'object',
                                                                'required'          => false,
                                                                'validate_callback' => function ( $value ) {
                                                                        return is_array( $value );
                                                                },
                                                                'sanitize_callback' => array( self::class, 'sanitize_settings' ),
                                                        ),
                                                ),
                                        ),
                                )
                        );
                }
        }

	public static function get_settings( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$id     = sanitize_key( $request['id'] );
		$global = (bool) $request->get_param( 'global' );
		$schema = DashboardWidgetRegistry::get_widget_schema( $id );

		if ( empty( $schema ) ) {
			return new WP_Error( 'invalid_widget', __( 'Unknown widget.', 'artpulse' ), array( 'status' => 404 ) );
		}

		$settings = $global
			? (array) get_option( 'ap_widget_settings_' . $id, array() )
			: (array) get_user_meta( get_current_user_id(), 'ap_widget_settings_' . $id, true );

		$result = array();
		foreach ( $schema as $field ) {
			if ( ! isset( $field['key'] ) ) {
				continue;
			}
			$key            = $field['key'];
			$result[ $key ] = $settings[ $key ] ?? ( $field['default'] ?? '' );
		}

		return \rest_ensure_response(
			array(
				'schema'   => $schema,
				'settings' => $result,
			)
		);
        }

        public static function save_settings( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$id     = sanitize_key( $request['id'] );
		$global = (bool) $request->get_param( 'global' );
		$schema = DashboardWidgetRegistry::get_widget_schema( $id );

		if ( empty( $schema ) ) {
			return new WP_Error( 'invalid_widget', __( 'Unknown widget.', 'artpulse' ), array( 'status' => 404 ) );
		}

                $check = Auth::verify_nonce( $request );
                if ( is_wp_error( $check ) ) {
                        return $check;
                }
		$perm = $global ? Auth::guard_manage( $request ) : Auth::guard_read( $request );
		if ( is_wp_error( $perm ) ) {
			return $perm;
		}

                $raw       = $request->get_param( 'settings' );
                if ( $raw === null ) {
                        $raw = $request->get_json_params();
                }
                $sanitized = is_array( $raw ) ? self::sanitize_settings( $raw, $request ) : array();

                if ( $global ) {
                        update_option( 'ap_widget_settings_' . $id, $sanitized );
                } else {
                        update_user_meta( get_current_user_id(), 'ap_widget_settings_' . $id, $sanitized );
                }

                return \rest_ensure_response(
                        array(
                                'id'       => $id,
                                'settings' => $sanitized,
                                'saved'    => true,
                        )
                );
        }

        public static function permissions_save_settings( WP_REST_Request $req ): bool|WP_Error {
                return $req->get_param( 'global' )
                        ? Auth::guard_manage( $req )
                        : Auth::guard_read( $req );
        }

        /**
         * Sanitize incoming settings based on the widget schema. Unknown keys are ignored.
         *
         * @param mixed            $value    Raw value from the request.
         * @param WP_REST_Request $request  The current request object.
         * @return array                      Sanitized settings.
         */
        public static function sanitize_settings( $value, WP_REST_Request $request ): array {
                if ( ! is_array( $value ) ) {
                        return array();
                }

                $id     = sanitize_key( $request['id'] );
                $schema = DashboardWidgetRegistry::get_widget_schema( $id );

                $sanitized = array();
                foreach ( $schema as $field ) {
                        if ( ! isset( $field['key'] ) ) {
                                continue;
                        }
                        $key = $field['key'];
                        if ( ! array_key_exists( $key, $value ) ) {
                                continue;
                        }

                        $raw = $value[ $key ];

                        if ( is_array( $raw ) ) {
                                $sanitized[ $key ] = self::deep_sanitize_array( $raw );
                                continue;
                        }

                        $type = $field['type'] ?? 'string';
                        switch ( $type ) {
                                case 'boolean':
                                case 'checkbox':
                                case 'bool':
                                        $sanitized[ $key ] = rest_sanitize_boolean( $raw );
                                        break;
                                case 'number':
                                case 'int':
                                case 'integer':
                                        $sanitized[ $key ] = (int) $raw;
                                        break;
                                case 'float':
                                case 'double':
                                        $sanitized[ $key ] = (float) $raw;
                                        break;
                                default:
                                        $sanitized[ $key ] = sanitize_text_field( (string) $raw );
                        }
                }

                return $sanitized;
        }

        private static function deep_sanitize_array( array $arr ): array {
                $out = array();
                foreach ( $arr as $k => $v ) {
                        if ( is_array( $v ) ) {
                                $out[ sanitize_key( (string) $k ) ] = self::deep_sanitize_array( $v );
                        } elseif ( is_scalar( $v ) || $v === null ) {
                                $out[ sanitize_key( (string) $k ) ] = sanitize_text_field( (string) $v );
                        }
                }
                return $out;
        }
}
