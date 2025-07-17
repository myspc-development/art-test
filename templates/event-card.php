<?php
/**
 * Event card layout.
 *
 * Variables: $event_id (int) of the event post.
 */
if ( ! isset( $event_id ) ) {
    return;
}

$event   = get_post( $event_id );
if ( ! $event || 'artpulse_event' !== $event->post_type ) {
    return;
}

$title     = get_the_title( $event );
$permalink = get_permalink( $event );
$image     = get_the_post_thumbnail( $event_id, 'medium', [ 'alt' => $title ] );
$venue     = get_post_meta( $event_id, 'venue_name', true );
$start     = get_post_meta( $event_id, 'event_start_date', true );
$end       = get_post_meta( $event_id, 'event_end_date', true );
$excerpt   = get_the_excerpt( $event );

$addr_parts = [];
$street  = get_post_meta( $event_id, 'event_street_address', true );
$city    = get_post_meta( $event_id, 'event_city', true );
$state   = get_post_meta( $event_id, 'event_state', true );
$postcode = get_post_meta( $event_id, 'event_postcode', true );
$country = get_post_meta( $event_id, 'event_country', true );
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
$address = implode( "\n", $addr_parts );

$types    = get_the_terms( $event_id, 'event_type' );
$org_name  = get_post_meta( $event_id, 'event_organizer_name', true );
$org_email = get_post_meta( $event_id, 'event_organizer_email', true );

$fav_count  = intval( get_post_meta( $event_id, 'ap_favorite_count', true ) );
$rsvps      = get_post_meta( $event_id, 'event_rsvp_list', true );
$rsvp_count = is_array( $rsvps ) ? count( $rsvps ) : 0;
$user_id    = get_current_user_id();
$favorited  = $user_id && function_exists('ap_user_has_favorited') ? ap_user_has_favorited( $user_id, $event_id ) : false;
$rsvped     = false;
if ( $user_id ) {
    $rsvp_ids = get_user_meta( $user_id, 'ap_rsvp_events', true );
    if ( is_array( $rsvp_ids ) ) {
        $rsvped = in_array( $event_id, $rsvp_ids, true );
    }
}
?>
<div class="ap-event-card nectar-box ap-widget" id="post-<?php echo esc_attr( $event_id ); ?>">
    <a href="<?php echo esc_url( $permalink ); ?>" class="ap-event-thumb">
        <?php if ( $image ) : ?>
            <?php echo $image; ?>
        <?php endif; ?>
        <h3 class="ap-event-title"><?php echo esc_html( $title ); ?></h3>
    </a>
    <?php if ( \ArtPulse\Monetization\EventBoostManager::is_boosted( $event_id ) ) : ?>
        <span class="badge-boosted">🔥 Boosted</span>
    <?php endif; ?>
    <div class="ap-event-card-content">
        <div class="ap-event-meta">
            <?php if ( $venue ) : ?>
                <div><strong><?php esc_html_e( 'Venue:', 'artpulse' ); ?></strong> <?php echo esc_html( $venue ); ?></div>
            <?php endif; ?>
            <?php if ( $address ) : ?>
                <div><strong><?php esc_html_e( 'Address:', 'artpulse' ); ?></strong> <?php echo nl2br( esc_html( $address ) ); ?></div>
            <?php endif; ?>
            <?php if ( $start ) : ?>
                <div><strong><?php esc_html_e( 'Starts:', 'artpulse' ); ?></strong> <?php echo esc_html( date_i18n( 'M d, Y H:i', strtotime( $start ) ) ); ?></div>
            <?php endif; ?>
            <?php if ( $end ) : ?>
                <div><strong><?php esc_html_e( 'Ends:', 'artpulse' ); ?></strong> <?php echo esc_html( date_i18n( 'M d, Y H:i', strtotime( $end ) ) ); ?></div>
            <?php endif; ?>
            <?php if ( $types ) : ?>
                <div><strong><?php esc_html_e( 'Type:', 'artpulse' ); ?></strong> <?php echo esc_html( implode( ', ', wp_list_pluck( $types, 'name' ) ) ); ?></div>
            <?php endif; ?>
            <?php if ( $org_name || $org_email ) : ?>
                <div><strong><?php esc_html_e( 'Organizer:', 'artpulse' ); ?></strong> <?php echo esc_html( $org_name ); ?><?php if ( $org_email ) : ?> (<?php echo esc_html( antispambot( $org_email ) ); ?>)<?php endif; ?></div>
            <?php endif; ?>
        </div>
        <?php
            $labels = [];
            if ( $rsvped ) {
                $labels[] = __( 'RSVP\'d', 'artpulse' );
            }
            if ( $favorited ) {
                $labels[] = __( 'Favorited', 'artpulse' );
            }
            if ( $labels ) : ?>
        <div class="ap-event-labels">
            <?php foreach ( $labels as $lbl ) : ?>
                <span class="ap-event-label"><?php echo esc_html( $lbl ); ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php if ( $excerpt ) : ?><div class="ap-event-excerpt"><?php echo wp_kses_post( wpautop( $excerpt ) ); ?></div><?php endif; ?>
        <div class="ap-event-actions">
            <?php echo \ArtPulse\Frontend\ap_render_rsvp_button( $event_id ); ?>
            <?php echo \ArtPulse\Frontend\ap_render_favorite_button( $event_id, 'artpulse_event' ); ?>
        </div>
        <div class="ap-event-stats">
            <span class="ap-rsvp-count"><?php echo esc_html( $rsvp_count ); ?></span>
            <span class="ap-fav-count"><?php echo esc_html( $fav_count ); ?></span>
        </div>
        <?php echo \ArtPulse\Frontend\ap_event_share_buttons( $event_id ); ?>
        <?php echo \ArtPulse\Frontend\ap_event_calendar_links( $event_id ); ?>
    </div>
</div>
