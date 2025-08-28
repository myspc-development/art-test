<?php
namespace ArtPulse\Crm;

class ContactModel {

	public static function get_all( int $org_id, string $tag = '' ): array {
		global $wpdb;
		$org_id = absint( $org_id );
		$table  = $wpdb->prefix . 'ap_crm_contacts';
		if ( $tag !== '' ) {
			return $wpdb->get_results(
				$wpdb->prepare(
					'SELECT id, org_id, user_id, email, name, tags, first_seen, last_active FROM %i WHERE org_id = %d AND tags LIKE %s ORDER BY last_active DESC',
					$table,
					$org_id,
					ap_db_like( $tag )
				),
				ARRAY_A
			);
		}
		return $wpdb->get_results(
			$wpdb->prepare(
				'SELECT id, org_id, user_id, email, name, tags, first_seen, last_active FROM %i WHERE org_id = %d ORDER BY last_active DESC',
				$table,
				$org_id
			),
			ARRAY_A
		);
	}

	public static function add_or_update( int $org_id, string $email, string $name = '', array $tags = array() ): void {
		global $wpdb;
		$org_id = absint( $org_id );
		$table  = $wpdb->prefix . 'ap_crm_contacts';
		$row    = $wpdb->get_row(
			$wpdb->prepare( 'SELECT id, name, tags FROM %i WHERE org_id = %d AND email = %s', $table, $org_id, $email )
		);
		$now    = current_time( 'mysql' );
		if ( $row ) {
			$existing = $row->tags ? json_decode( $row->tags, true ) : array();
			$tags     = array_unique( array_merge( $existing, $tags ) );
			$wpdb->update(
				$table,
				array(
					'name'        => $name ?: $row->name,
					'tags'        => wp_json_encode( $tags ),
					'last_active' => $now,
				),
				array( 'id' => $row->id )
			);
		} else {
			$wpdb->insert(
				$table,
				array(
					'org_id'      => $org_id,
					'user_id'     => 0,
					'email'       => sanitize_email( $email ),
					'name'        => sanitize_text_field( $name ),
					'tags'        => wp_json_encode( $tags ),
					'first_seen'  => $now,
					'last_active' => $now,
				)
			);
		}
	}

	public static function add_tag( int $org_id, string $email, string $tag ): void {
		self::add_or_update( $org_id, $email, '', array( $tag ) );
	}
}
