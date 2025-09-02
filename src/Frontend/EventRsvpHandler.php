<?php // phpcs:ignoreFile WordPress.Files.FileName.NotHyphenatedLowercase,WordPress.Files.FileName.InvalidClassFileName -- Autoloader requires class-based filename.

namespace ArtPulse\Frontend;

/**
 * Handle RSVP submissions.
 *
 * @package ArtPulse
 */

/**
 * Event RSVP handler.
 */
class EventRsvpHandler {

		/**
		 * Register hooks.
		 */
	public static function register(): void {
			add_action( 'admin_post_ap_rsvp_event', array( self::class, 'handle' ) );
			add_action( 'admin_post_nopriv_ap_rsvp_event', array( self::class, 'handle' ) );
	}

		/**
		 * Process RSVP form submissions.
		 */
	public static function handle(): void {
		if ( ! is_user_logged_in() || ! current_user_can( 'read' ) ) {
				wp_die( esc_html__( 'Insufficient permissions', 'artpulse' ) );
		}

			check_admin_referer( 'ap_rsvp_event' );

			$event_id = isset( $_POST['event_id'] ) ? intval( $_POST['event_id'] ) : 0;
		if ( ! $event_id ) {
			return;
		}

			$enabled = get_post_meta( $event_id, 'event_rsvp_enabled', true );
		if ( ! $enabled ) {
			wp_safe_redirect( get_permalink( $event_id ) );
			exit;
		}

			$user_id  = get_current_user_id();
			$existing = get_post_meta( $event_id, 'event_rsvp_list', true );
		if ( ! is_array( $existing ) ) {
			$existing = array();
		}

		if ( ! in_array( $user_id, $existing, true ) ) {
			$existing[] = $user_id;
			update_post_meta( $event_id, 'event_rsvp_list', $existing );
			do_action( 'ap_event_rsvp_added', $event_id, get_current_user_id() );
		}

			wp_safe_redirect( get_permalink( $event_id ) );
			exit;
	}

		/**
		 * Get RSVP summary for a user.
		 *
		 * @param int $user_id User ID.
		 * @return array
		 */
        public static function get_rsvp_summary_for_user( $user_id ): array {
                $event_ids = get_user_meta( $user_id, 'ap_rsvp_events', true );
                if ( ! is_array( $event_ids ) ) {
                        $event_ids = $event_ids ? array( $event_ids ) : array();
                }

                $going      = 0;
                $interested = 0;
                $trend_map  = array();

                $today = current_time( 'timestamp' );

                foreach ( $event_ids as $eid ) {
                        $date = get_post_meta( $eid, '_ap_event_date', true );
                        $ts   = false;
                        if (
                                is_string( $date ) &&
                                preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) &&
                                ( $dt = date_create_from_format( 'Y-m-d', $date, wp_timezone() ) )
                        ) {
                                $ts = $dt instanceof \DateTime ? $dt->getTimestamp() : false;
                        }

                        if ( false !== $ts && $ts >= $today ) {
                                ++$going;
                        } else {
                                ++$interested;
                        }

                        $history = get_post_meta( $eid, 'event_rsvp_history', true );
                        if ( is_array( $history ) ) {
                                foreach ( $history as $day => $count ) {
                                        if ( ! isset( $trend_map[ $day ] ) ) {
                                                $trend_map[ $day ] = 0;
                                        }
                                        $trend_map[ $day ] += (int) $count;
                                }
                        }
                }

                ksort( $trend_map );
                $trend = array();
                foreach ( $trend_map as $day => $count ) {
                        $trend[] = array(
                                'date'  => $day,
                                'count' => $count,
                        );
                }

                return array(
                        'going'      => $going,
                        'interested' => $interested,
                        'trend'      => $trend,
                );
        }
}
