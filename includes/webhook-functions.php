<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trigger all active webhooks for an organization event.
 *
 * @param string $event  Event name like 'ticket_sold'.
 * @param int    $org_id Organization ID the event relates to.
 * @param array  $data   Payload data.
 */
function ap_trigger_webhooks( string $event, int $org_id, array $data ): void {
	global $wpdb;
	$table = $wpdb->prefix . 'ap_webhooks';
	$hooks = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM $table WHERE org_id = %d AND active = 1 AND FIND_IN_SET(%s, events)",
			$org_id,
			$event
		)
	);

	foreach ( $hooks as $hook ) {
		ap_send_webhook_request( $hook, $event, $data );
	}
}

/**
 * Send a single webhook and log the response.
 */
function ap_send_webhook_request( object $hook, string $event, array $data ): void {
	global $wpdb;
	$payload = array(
		'event'     => $event,
		'timestamp' => current_time( 'mysql' ),
		'data'      => $data,
	);
	$json    = wp_json_encode( $payload );
	$sig     = hash_hmac( 'sha256', $json, $hook->secret );
	$args    = array(
		'body'    => $json,
		'headers' => array(
			'Content-Type'         => 'application/json',
			'X-ArtPulse-Signature' => 'sha256=' . $sig,
		),
		'timeout' => 5,
	);

	$status = 0;
	$body   = '';
	for ( $i = 0; $i < 3; $i++ ) {
		$res = wp_remote_post( $hook->url, $args );
		if ( is_wp_error( $res ) ) {
			$status = 0;
			$body   = $res->get_error_message();
		} else {
			$status = (int) wp_remote_retrieve_response_code( $res );
			$body   = wp_remote_retrieve_body( $res );
		}

		$wpdb->insert(
			$wpdb->prefix . 'ap_webhook_logs',
			array(
				'subscription_id' => $hook->id,
				'status_code'     => $status ?: null,
				'response_body'   => $body,
				'timestamp'       => current_time( 'mysql' ),
			)
		);

		if ( $status >= 200 && $status < 300 ) {
			break;
		}
		if ( $status < 500 ) {
			break;
		}
	}

	$wpdb->update(
		$wpdb->prefix . 'ap_webhooks',
		array(
			'last_status' => $status ? (string) $status : 'error',
			'last_sent'   => current_time( 'mysql' ),
		),
		array( 'id' => $hook->id )
	);
}
