<?php
namespace ArtPulse\Core;

use ArtPulse\Audit\AuditBus;
use ArtPulse\Support\WidgetIds;

class DashboardRenderer {

	/**
	 * Determine if a widget should be cached.
	 */
	public static function shouldCache( string $widget_id, int $user_id, array $widget ): bool {
		if ( ! $user_id ) {
			return false; // never cache for logged-out users
		}

		$flag = (bool) ( $widget['cache'] ?? false );

		/**
		 * Allow plugins to override widget caching.
		 */
		return (bool) apply_filters( 'ap_dashboard_widget_should_cache', $flag, $widget_id, $user_id, $widget );
	}

	/**
	 * Render a single widget with optional gating.
	 */
	public function renderWidget( string $widget_id, array $opts = array(), ?int $user_id = null ): string {
		$opts = array_merge(
			array(
				'gate_caps'  => true,
				'gate_flags' => true,
			),
			$opts
		);

		$widget_id = WidgetIds::canonicalize( $widget_id );
		$user_id   = $user_id ?? get_current_user_id();
		$role      = DashboardController::get_role( $user_id );
		$widget    = $opts['gate_caps'] ? DashboardWidgetRegistry::get_widget( $widget_id, $user_id ) : DashboardWidgetRegistry::get( $widget_id );

		if ( ! $widget ) {
                        ap_debug_log( "\xF0\x9F\x9A\xAB Widget '{$widget_id}' not found or hidden." );
			AuditBus::on_rendered( $widget_id, $role, 0, false, 'missing' );
			return '';
		}

		$status = $widget['status'] ?? 'active';
		if ( ! $opts['gate_flags'] ) {
			$status = 'active';
		}
		$preview     = apply_filters( 'ap_dashboard_preview_enabled', false );
		$hidden_list = $opts['gate_caps'] ? apply_filters( 'ap_dashboard_hidden_widgets', array(), $role ) : array();
		$hidden      = $opts['gate_caps'] ? in_array( $widget_id, $hidden_list, true ) : false;
		AuditBus::on_attempt(
			$widget_id,
			$role,
			array(
				'hidden'  => $hidden,
				'status'  => $status,
				'preview' => $preview,
			)
		);

		if ( $hidden && $opts['gate_caps'] && ! current_user_can( 'manage_options' ) ) {
			AuditBus::on_rendered( $widget_id, $role, 0, false, 'hidden' );
			return '';
		}

		if ( $status !== 'active' && $opts['gate_flags'] && ! current_user_can( 'manage_options' ) ) {
                        ap_debug_log( "\xF0\x9F\x9A\xAB Widget '{$widget_id}' inactive." );
			AuditBus::on_rendered( $widget_id, $role, 0, false, 'inactive' );
			return '';
		}

		$allowed_roles = array();
		$class         = $widget['class'] ?? '';
		if ( $opts['gate_caps'] ) {
			if ( $class && class_exists( $class ) && is_callable( array( $class, 'roles' ) ) ) {
				$allowed_roles = (array) $class::roles();
			} elseif ( ! empty( $widget['roles'] ) ) {
				$allowed_roles = (array) $widget['roles'];
			}

			if ( $allowed_roles && ! in_array( $role, $allowed_roles, true ) ) {
                                ap_debug_log( "\xF0\x9F\x9A\xAB Widget '{$widget_id}' not allowed for role '{$role}'." );
				AuditBus::on_rendered( $widget_id, $role, 0, false, 'forbidden' );
				return '';
			}
		}

		$cache_key = "ap_widget_{$widget_id}_{$user_id}";
		$output    = '';

		if ( self::shouldCache( $widget_id, $user_id, $widget ) ) {
			$cached = get_transient( $cache_key );
			if ( $cached !== false ) {
				return (string) $cached;
			}
		}

		$start = microtime( true );

		$ok     = true;
		$reason = '';
		try {
			if ( $class && class_exists( $class ) && is_callable( array( $class, 'render' ) ) ) {
				ob_start();
				$result = $class::render( $user_id );
				$buffer = ob_get_clean();
				$output = $buffer . ( is_string( $result ) ? $result : '' );
			} elseif ( has_action( "ap_render_dashboard_widget_{$widget_id}" ) ) {
				ob_start();
				do_action( "ap_render_dashboard_widget_{$widget_id}", $user_id );
				$output = ob_get_clean();
			} elseif ( isset( $widget['callback'] ) && is_callable( $widget['callback'] ) ) {
				ob_start();
				$result = call_user_func( $widget['callback'], $user_id );
				$buffer = ob_get_clean();
				$output = $buffer . ( is_string( $result ) ? $result : '' );
			} else {
				$ok     = false;
				$reason = 'no-callback';
                                ap_debug_log( "\xF0\x9F\x9A\xAB Invalid callback for widget '{$widget_id}'." );
			}
		} catch ( \Throwable $e ) {
			$ok     = false;
			$reason = 'exception';
                        ap_debug_log( 'widget ' . $widget_id . ' failed: ' . $e->getMessage() );
			$output = current_user_can( 'manage_options' ) ? "<div class='ap-widget-error'>This widget failed to load.</div>" : '';
		}

				$output = apply_filters( 'ap_render_dashboard_widget_output', $output, $widget_id, $user_id, $widget );

				// Optionally wrap output in developer mode for easier debugging.
		if ( defined( 'AP_DEV_MODE' ) && AP_DEV_MODE ) {
				$output = sprintf( '<!-- ap-widget:%s:start -->%s<!-- ap-widget:%s:end -->', $widget_id, $output, $widget_id );
		}

				// Allow filters on the fully rendered widget markup.
				$output = apply_filters( 'ap_dashboard_rendered_widget', $output, $widget_id, $user_id );

				// Sanitize final HTML to prevent XSS.
				$output = wp_kses_post( $output );

		$elapsed = microtime( true ) - $start;
                ap_debug_log( sprintf( '⏱️ Widget %s rendered in %.4fs', $widget_id, $elapsed ) );
		AuditBus::on_rendered( $widget_id, $role, (int) ( $elapsed * 1000 ), $ok, $reason );

		if ( self::shouldCache( $widget_id, $user_id, $widget ) ) {
			$ttl = (int) apply_filters( 'ap_dashboard_widget_cache_ttl', MINUTE_IN_SECONDS * 10, $widget_id, $user_id, $widget );
			if ( $ttl > 0 ) {
				set_transient( $cache_key, $output, $ttl );
			}
		}

		return $output;
	}

	/**
	 * Back-compat static wrapper.
	 */
	public static function render( string $widget_id, ?int $user_id = null ): string {
		$renderer = new self();
		return $renderer->renderWidget( $widget_id, array(), $user_id );
	}

	/**
	 * Render an exact list of widget IDs.
	 */
	public function renderIds( array $ids, array $opts = array() ): string {
		$ids = array_values(
			array_filter(
				array_map(
					static fn( $v ) => sanitize_key( (string) $v ),
					(array) $ids
				)
			)
		);

		$opts = array_merge(
			array(
				'context'    => 'builder_preview',
				'gate_caps'  => false,
				'gate_flags' => false,
			),
			$opts
		);

		$html = '';
		foreach ( $ids as $id ) {
			do_action(
				'artpulse/audit/widget_attempt',
				array(
					'id'      => $id,
					'context' => $opts['context'],
				)
			);

			$out = $this->renderWidget(
				$id,
				array(
					'gate_caps'  => $opts['gate_caps'],
					'gate_flags' => $opts['gate_flags'],
				)
			);

			if ( is_wp_error( $out ) || $out === null || $out === '' ) {
				do_action(
					'artpulse/audit/widget_result',
					array(
						'id'      => $id,
						'context' => $opts['context'],
						'status'  => 'not_rendered',
					)
				);
				continue;
			}

			$html .= sprintf( '<div data-widget-id="%s">%s</div>', esc_attr( $id ), $out );
			do_action(
				'artpulse/audit/widget_result',
				array(
					'id'      => $id,
					'context' => $opts['context'],
					'status'  => 'rendered',
				)
			);
		}
		return $html;
	}
}
