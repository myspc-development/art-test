<?php
namespace ArtPulse\Crm;

use wpdb;
use ArtPulse\Install\Schema;

class DonationModel {

	public static function add( int $org_id, int $user_id, float $amount ): void {
		if ( $org_id <= 0 ) {
			throw new \InvalidArgumentException( 'Organization ID must be a positive integer.' );
		}

		if ( $user_id <= 0 ) {
			throw new \InvalidArgumentException( 'User ID must be a positive integer.' );
		}

		if ( $amount <= 0 ) {
			throw new \InvalidArgumentException( 'Amount must be greater than zero.' );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'ap_donations';
		$wpdb->insert(
			$table,
			array(
				'org_id'     => $org_id,
				'user_id'    => $user_id,
				'amount'     => $amount,
				'donated_at' => current_time( 'mysql' ),
			)
		);

		do_action( 'ap_donation_recorded', $org_id, $user_id, $amount );
	}

	public static function get_by_org( int $org_id ): array {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_donations';
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE org_id = %d ORDER BY donated_at DESC", $org_id ), ARRAY_A );
	}

	public static function get_summary( int $org_id ): array {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_donations';
		$row   = $wpdb->get_row( $wpdb->prepare( "SELECT COUNT(DISTINCT user_id) as donors, SUM(amount) as total FROM $table WHERE org_id = %d", $org_id ) );
		return array(
			'count' => intval( $row->donors ),
			'total' => floatval( $row->total ),
		);
	}

	public static function query( int $org_id ): array {
		global $wpdb; /** @var wpdb $wpdb */
		$table = $wpdb->prefix . 'ap_donations';
		if ( ! Schema::column_exists( $table, 'org_id' ) ) {
						\ArtPulse\Core\ap_debug_log( 'donations table missing org_id column; returning empty set' );
			return array();
		}
		$sql = "SELECT * FROM {$table} WHERE org_id = %d ORDER BY donated_at DESC";
		return $wpdb->get_results( $wpdb->prepare( $sql, $org_id ), ARRAY_A ) ?: array();
	}
}
