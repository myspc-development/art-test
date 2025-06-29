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

$fav_count  = intval( get_post_meta( $event_id, 'ap_favorite_count', true ) );
$rsvp_count = intval( get_post_meta( $event_id, 'ap_rsvp_count', true ) );
$user_id    = get_current_user_id();
$favorited  = $user_id && function_exists('ap_user_has_favorited') ? ap_user_has_favorited( $user_id, $event_id ) : false;
?>
<article class="ap-event-card ap-widget" id="post-<?php echo esc_attr( $event_id ); ?>">
    <?php if ( $image ) : ?>
        <a href="<?php echo esc_url( $permalink ); ?>" class="ap-event-thumb">
            <?php echo $image; ?>
        </a>
    <?php endif; ?>
    <div class="ap-event-card-content">
        <h3 class="ap-event-title"><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a></h3>
        <?php if ( $venue ) : ?><p class="ap-event-venue"><?php echo esc_html( $venue ); ?></p><?php endif; ?>
        <?php if ( $address ) : ?><p class="ap-event-address"><?php echo nl2br( esc_html( $address ) ); ?></p><?php endif; ?>
        <?php if ( $start ) : ?><p class="ap-event-start"><?php echo esc_html( $start ); ?></p><?php endif; ?>
        <?php if ( $end ) : ?><p class="ap-event-end"><?php echo esc_html( $end ); ?></p><?php endif; ?>
        <?php if ( $excerpt ) : ?><div class="ap-event-excerpt"><?php echo wp_kses_post( wpautop( $excerpt ) ); ?></div><?php endif; ?>
        <div class="ap-event-actions">
            <?php echo \ArtPulse\Frontend\ap_render_rsvp_button( $event_id ); ?>
            <?php echo \ArtPulse\Frontend\ap_render_favorite_button( $event_id ); ?>
        </div>
    </div>
</article>
