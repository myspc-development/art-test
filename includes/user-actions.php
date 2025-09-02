<?php
/**
 * AJAX handlers for following and unfollowing content.
 *
 * @package ArtPulse
 */

if ( ! defined( 'ABSPATH' ) ) {
		exit;
}

use ArtPulse\Community\FollowManager;

add_action( 'wp_ajax_ap_follow_post', 'ap_follow_post' );
add_action( 'wp_ajax_nopriv_ap_follow_post', 'ap_follow_post' );
add_action( 'wp_ajax_ap_unfollow_post', 'ap_unfollow_post' );
add_action( 'wp_ajax_nopriv_ap_unfollow_post', 'ap_unfollow_post' );

add_action( 'wp_ajax_ap_follow_toggle', 'ap_follow_toggle' );
add_action( 'wp_ajax_nopriv_ap_follow_toggle', 'ap_follow_toggle' );

/**
 * Handle a follow request.
 */
function ap_follow_post(): void {
		check_ajax_referer( 'wp_rest' );
	if ( ! is_user_logged_in() || ! current_user_can( 'read' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Login required', 'artpulse' ) ), 403 );
	}
		$post_id   = absint( $_POST['post_id'] ?? 0 );
		$post_type = sanitize_key( $_POST['post_type'] ?? '' );
	if ( ! $post_id || ! $post_type ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid data', 'artpulse' ) ) );
	}
		FollowManager::add_follow( get_current_user_id(), $post_id, $post_type );
		wp_send_json_success( array( 'status' => 'followed' ) );
}

/**
 * Handle an unfollow request.
 */
function ap_unfollow_post(): void {
		check_ajax_referer( 'wp_rest' );
	if ( ! is_user_logged_in() || ! current_user_can( 'read' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Login required', 'artpulse' ) ), 403 );
	}
		$post_id   = absint( $_POST['post_id'] ?? 0 );
		$post_type = sanitize_key( $_POST['post_type'] ?? '' );
	if ( ! $post_id || ! $post_type ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid data', 'artpulse' ) ) );
	}
		FollowManager::remove_follow( get_current_user_id(), $post_id, $post_type );
		wp_send_json_success( array( 'status' => 'unfollowed' ) );
}

/**
 * Toggle follow for artists or events via AJAX.
 */
function ap_follow_toggle(): void {
		check_ajax_referer( 'wp_rest' );

	if ( ! is_user_logged_in() || ! current_user_can( 'read' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Login required', 'artpulse' ) ), 403 );
	}

	$object_id   = absint( $_POST['object_id'] ?? 0 );
	$object_type = sanitize_key( $_POST['object_type'] ?? '' );
	if ( ! $object_id || ! $object_type ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid data', 'artpulse' ) ) );
	}

		$user_id  = get_current_user_id();
		$meta_key = 'artpulse_event' === $object_type ? 'followed_events' : 'followed_artists';
	$list         = get_user_meta( $user_id, $meta_key, true );
	$list         = is_array( $list ) ? $list : array();

	if ( in_array( $object_id, $list, true ) ) {
		$list = array_values( array_diff( $list, array( $object_id ) ) );
		update_user_meta( $user_id, $meta_key, $list );
		wp_send_json_success( array( 'state' => 'unfollowed' ) );
	}

	$list[] = $object_id;
	update_user_meta( $user_id, $meta_key, $list );
	wp_send_json_success( array( 'state' => 'following' ) );
}
