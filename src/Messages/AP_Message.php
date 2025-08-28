<?php
namespace ArtPulse\Messages;

defined( 'ABSPATH' ) || exit;

class AP_Message {
	public static function send( $sender_id, $receiver_id, $content ) {
		global $wpdb;
		$wpdb->insert(
			"{$wpdb->prefix}ap_messages",
			array(
				'sender_id'   => $sender_id,
				'receiver_id' => $receiver_id,
				'content'     => wp_kses_post( $content ),
			)
		);
		do_action( 'ap_message_sent', $wpdb->insert_id );
		return $wpdb->insert_id;
	}

	public static function get_inbox( $user_id ) {
		global $wpdb;
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}ap_messages WHERE receiver_id = %d ORDER BY created_at DESC",
				$user_id
			)
		);
	}

	public static function mark_read( $id, $user_id ) {
		global $wpdb;
		$wpdb->update(
			"{$wpdb->prefix}ap_messages",
			array( 'is_read' => 1 ),
			array(
				'id'          => $id,
				'receiver_id' => $user_id,
			)
		);
		do_action( 'ap_message_read', $id );
	}
}
