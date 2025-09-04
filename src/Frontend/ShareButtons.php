<?php
namespace ArtPulse\Frontend;

/**
 * Render social sharing buttons for any object.
 *
 * @param string $url         URL to share.
 * @param string $title       Title or text for the share.
 * @param string $object_type Optional type for filtering hooks.
 * @param int    $object_id   Optional object ID for analytics.
 * @return string HTML markup for share buttons.
 */
if ( ! function_exists( __NAMESPACE__ . '\ap_share_buttons' ) ) {
function ap_share_buttons( string $url, string $title, string $object_type = '', int $object_id = 0 ): string {
        if ( ! $url || ! $title ) {
                return '';
        }

	$networks = array(
                'facebook' => sprintf( 'https://www.facebook.com/sharer/sharer.php?u=%1$s', urlencode( $url ) ),
                'twitter'  => sprintf( 'https://twitter.com/share?url=%1$s&text=%2$s', urlencode( $url ), rawurlencode( $title ) ),
                'whatsapp' => sprintf( 'https://wa.me/?text=%1$s%%20%2$s', rawurlencode( $title ), urlencode( $url ) ),
                'email'    => sprintf( 'mailto:?subject=%1$s&body=%2$s%%20%3$s', rawurlencode( $title ), rawurlencode( esc_html__( 'Check this out:', 'artpulse' ) ), urlencode( $url ) ),
        );

	/**
	 * Filter the share network links for all types.
	 *
	 * @param array  $networks    Key/value pairs of network => URL.
	 * @param string $object_type Type of object being shared.
	 * @param string $url         URL being shared.
	 * @param string $title       Title or text being shared.
	 */
	$networks = apply_filters( 'ap_share_networks', $networks, $object_type, $url, $title );

	if ( $object_type ) {
		/** This filter is documented above as 'ap_event_share_networks'. */
		$networks = apply_filters( "ap_{$object_type}_share_networks", $networks, $url, $title );
	}

	ob_start();
	echo '<div class="ap-share-buttons" role="group">';
	foreach ( $networks as $name => $link ) {
		$label = ucfirst( $name );
		printf(
			'<a class="ap-share-%1$s" href="%2$s" target="_blank" rel="noopener noreferrer" aria-label="%3$s" data-object-id="%4$d" data-object-type="%5$s">%3$s</a>',
			esc_attr( $name ),
			esc_url( $link ),
			esc_html( $label ),
			$object_id,
			esc_attr( $object_type )
		);
	}
        echo '</div>';
        return trim( ob_get_clean() );
}
}

/**
 * Helper to render social sharing buttons for an event.
 *
 * @param int $event_id Event post ID.
 * @return string HTML markup for share buttons.
 */
if ( ! function_exists( __NAMESPACE__ . '\ap_event_share_buttons' ) ) {
function ap_event_share_buttons( int $event_id ): string {
        $url   = get_permalink( $event_id );
        $title = get_the_title( $event_id );

        return ap_share_buttons( $url ?: '', $title ?: '', 'artpulse_event', $event_id );
}
}

/**
 * Render calendar links for an event.
 *
 * @param int $event_id Event ID.
 * @return string HTML links.
 */
if ( ! function_exists( __NAMESPACE__ . '\ap_event_calendar_links' ) ) {
function ap_event_calendar_links( int $event_id ): string {
	$event = get_post( $event_id );
	if ( ! $event || $event->post_type !== 'artpulse_event' ) {
		return '';
	}

	$start     = get_post_meta( $event_id, 'event_start_date', true );
	$end       = get_post_meta( $event_id, 'event_end_date', true ) ?: $start;
	$start_str = gmdate( 'Ymd\THis\Z', strtotime( $start ) );
	$end_str   = gmdate( 'Ymd\THis\Z', strtotime( $end ) );

	$google = sprintf(
		'https://calendar.google.com/calendar/render?action=TEMPLATE&text=%s&dates=%s/%s&details=%s',
		rawurlencode( $event->post_title ),
		$start_str,
		$end_str,
		rawurlencode( get_permalink( $event_id ) )
	);

	$ics = home_url( '/events/' . $event_id . '/export.ics' );

	ob_start();
	?>
	<div class="ap-calendar-links" role="group">
		<a class="ap-calendar-google" href="<?php echo esc_url( $google ); ?>" target="_blank" rel="noopener noreferrer">Google</a>
		<a class="ap-calendar-outlook" href="<?php echo esc_url( $ics ); ?>">Outlook</a>
		<a class="ap-calendar-ics" href="<?php echo esc_url( $ics ); ?>">Apple (.ics)</a>
	</div>
	<?php
        return trim( ob_get_clean() );
}
}
