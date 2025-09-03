<?php
/**
 * Modern event card layout.
 *
 * Expects $event_id (int) of the event post.
 */
if ( ! isset( $event_id ) ) {
    return;
}

$event = get_post( $event_id );
if ( ! $event || 'artpulse_event' !== $event->post_type ) {
    return;
}

$title     = get_the_title( $event );
$permalink = get_permalink( $event );
$image     = get_the_post_thumbnail( $event_id, 'medium', [ 'alt' => $title, 'loading' => 'lazy' ] );
$venue     = get_post_meta( $event_id, 'venue_name', true );
$start     = get_post_meta( $event_id, 'event_start_date', true );
$end       = get_post_meta( $event_id, 'event_end_date', true );

$addr_parts = [];
$street   = get_post_meta( $event_id, 'event_street_address', true );
$city     = get_post_meta( $event_id, 'event_city', true );
$state    = get_post_meta( $event_id, 'event_state', true );
$postcode = get_post_meta( $event_id, 'event_postcode', true );
$country  = get_post_meta( $event_id, 'event_country', true );
if ( $street ) {
    $addr_parts[] = $street;
}
$city_state = trim( implode( ', ', array_filter( [ $city, $state ] ) ) );
if ( $city_state ) {
    $addr_parts[] = trim( $city_state . ( $postcode ? ' ' . $postcode : '' ) );
} elseif ( $postcode ) {
    $addr_parts[] = $postcode;
}
if ( $country ) {
    $addr_parts[] = $country;
}
$address = implode( ', ', $addr_parts );

$types = get_the_terms( $event_id, 'event_type' );
if ( is_wp_error( $types ) ) {
    $types = array();
} else {
    $types = wp_list_pluck( $types, 'name' );
}
$org_name  = get_post_meta( $event_id, 'event_organizer_name', true );
$org_email = sanitize_email( get_post_meta( $event_id, 'event_organizer_email', true ) );

$now = current_time( 'timestamp' );
$status = '';
if ( $start && strtotime( $start ) > $now ) {
    $status = __( 'Upcoming', 'artpulse' );
} elseif ( $start && $end && $now >= strtotime( $start ) && $now <= strtotime( $end ) ) {
    $status = __( 'Ongoing', 'artpulse' );
} elseif ( $end && strtotime( $end ) < $now ) {
    $status = __( 'Past', 'artpulse' );
}
?>
<div class="ap-event-card" id="post-<?php echo esc_attr( $event_id ); ?>">
    <div class="ap-event-image">
        <?php if ( $image ) : ?>
            <?php echo $image; ?>
        <?php else : ?>
            <img src="https://via.placeholder.com/300x200?text=Event" alt="" />
        <?php endif; ?>
        <?php if ( $status ) : ?>
            <span class="ap-event-status ap-event-status-<?php echo esc_attr( strtolower( $status ) ); ?>">
                <?php echo esc_html( $status ); ?>
            </span>
        <?php endif; ?>
    </div>
    <div class="ap-event-info">
        <h3 class="ap-event-title"><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a></h3>
        <?php if ( $start ) : ?>
            <p class="ap-event-date">
                <?php echo esc_html( date_i18n( 'M j, Y g:i a', strtotime( $start ) ) ); ?>
                <?php if ( $end ) : ?> – <?php echo esc_html( date_i18n( 'M j, Y g:i a', strtotime( $end ) ) ); ?><?php endif; ?>
            </p>
        <?php endif; ?>
        <?php if ( $venue || $address ) : ?>
            <p class="ap-event-location">
                <?php echo esc_html( $venue ); ?><?php echo $venue && $address ? ', ' : ''; ?><?php echo esc_html( $address ); ?>
            </p>
        <?php endif; ?>
        <?php if ( ! empty( $types ) ) : ?>
            <span class="screen-reader-text"><?php esc_html_e( 'Type:', 'artpulse' ); ?> <?php echo esc_html( implode( ', ', $types ) ); ?></span>
        <?php endif; ?>
        <?php if ( $org_name || $org_email ) : ?>
            <span class="screen-reader-text"><?php esc_html_e( 'Organizer:', 'artpulse' ); ?> <?php echo esc_html( $org_name ); ?><?php if ( $org_email ) : ?> (<?php echo esc_html( \ArtPulse\Util\ap_obfuscate_email( $org_email ) ); ?>)<?php endif; ?></span>
        <?php endif; ?>
        <?php
            $rsvps      = get_post_meta( $event_id, 'event_rsvp_list', true );
            $rsvp_count = is_array( $rsvps ) ? count( $rsvps ) : 0;
            $fav_count  = intval( get_post_meta( $event_id, 'ap_favorite_count', true ) );
        ?>
        <div class="ap-event-actions">
            <?php echo \ArtPulse\Frontend\ap_render_favorite_button( $event_id, 'artpulse_event' ); ?>
            <span class="ap-fav-count" aria-label="<?php esc_attr_e( 'Interested count', 'artpulse' ); ?>">
                <?php echo esc_html( $fav_count ); ?>
            </span>
            <?php echo \ArtPulse\Frontend\ap_render_rsvp_button( $event_id ); ?>
            <span class="ap-rsvp-count" aria-label="<?php esc_attr_e( 'RSVP count', 'artpulse' ); ?>">
                <?php echo esc_html( $rsvp_count ); ?>
            </span>
            <a href="<?php echo esc_url( $permalink ); ?>" class="ap-btn ap-event-details"><?php esc_html_e( 'Details', 'artpulse' ); ?></a>
        </div>
    </div>
</div>
