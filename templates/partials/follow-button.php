<?php
/**
 * Follow button snippet for artists and events.
 *
 * @var int    $object_id
 * @var string $object_type
 */
if ( ! isset( $object_id, $object_type ) ) {
	return;
}
$is_following = false;
if ( is_user_logged_in() ) {
	$meta_key     = $object_type === 'artpulse_event' ? 'followed_events' : 'followed_artists';
	$list         = get_user_meta( get_current_user_id(), $meta_key, true );
	$is_following = is_array( $list ) && in_array( $object_id, $list, true );
}
?>
<button class="ap-follow-btn<?php echo $is_following ? ' ap-following' : ''; ?>"
		data-id="<?php echo esc_attr( $object_id ); ?>"
		data-type="<?php echo esc_attr( $object_type ); ?>">
	<?php echo $is_following ? esc_html__( 'Unfollow', 'artpulse' ) : esc_html__( 'Follow', 'artpulse' ); ?>
</button>
