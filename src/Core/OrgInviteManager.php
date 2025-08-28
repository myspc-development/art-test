<?php
namespace ArtPulse\Core;

class OrgInviteManager {

	public static function register(): void {
		add_action( 'init', array( self::class, 'maybe_install_table' ), 0 );
	}

	public static function maybe_install_table(): void {
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_org_invites';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists !== $table ) {
			self::install_table();
		}
	}

	public static function install_table(): void {
		global $wpdb;
		$table   = $wpdb->prefix . 'ap_org_invites';
		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE $table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            email VARCHAR(190) NOT NULL,
            org_id BIGINT NOT NULL,
            role VARCHAR(50) NOT NULL,
            token VARCHAR(64) NOT NULL,
            invited_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY token (token)
        ) $charset;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	public static function create_invite( string $email, int $org_id, string $role ): string {
		$token = wp_generate_password( 32, false, false );
		global $wpdb;
		$table = $wpdb->prefix . 'ap_org_invites';
		$wpdb->insert(
			$table,
			array(
				'email'      => sanitize_email( $email ),
				'org_id'     => $org_id,
				'role'       => sanitize_key( $role ),
				'token'      => $token,
				'invited_at' => current_time( 'mysql' ),
			)
		);
		return $token;
	}

	public static function accept_invite( string $token, int $user_id ): bool {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_org_invites';
		$row   = $wpdb->get_row( $wpdb->prepare( "SELECT id, org_id, role FROM $table WHERE token = %s", $token ) );
		if ( ! $row ) {
			return false;
		}
		MultiOrgRoles::assign_roles( $user_id, (int) $row->org_id, array( (string) $row->role ) );
		$wpdb->delete( $table, array( 'id' => $row->id ) );
		return true;
	}
}
