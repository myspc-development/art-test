<?php
namespace ArtPulse\Core;

class MultiOrgRoles {

	public static function register(): void {
		// Run very early so other init callbacks can assume the table exists.
		add_action( 'init', array( self::class, 'maybe_install_table' ), 0 );
	}

	public static function maybe_install_table(): void {
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_org_user_roles';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists !== $table ) {
			self::install_table();
		}
	}

	public static function install_table(): void {
		global $wpdb;
		$table   = $wpdb->prefix . 'ap_org_user_roles';
		$charset = $wpdb->get_charset_collate();

				$sql = "CREATE TABLE $table (
            id INT NOT NULL AUTO_INCREMENT,
            org_id INT NOT NULL,
            user_id BIGINT NOT NULL,
            role ENUM('admin','editor','curator','promoter') DEFAULT 'editor',
            status ENUM('active','pending','invited') DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY org_user (org_id, user_id),
            KEY user_id (user_id)
        ) $charset;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	public static function assign_roles( int $user_id, int $org_id, array $roles ): void {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_org_user_roles';

		$wpdb->delete(
			$table,
			array(
				'user_id' => $user_id,
				'org_id'  => $org_id,
			)
		);
		foreach ( $roles as $role ) {
			$wpdb->insert(
				$table,
				array(
					'user_id' => $user_id,
					'org_id'  => $org_id,
					'role'    => sanitize_key( $role ),
					'status'  => 'active',
				)
			);
		}
	}

	public static function get_user_roles( int $user_id, int $org_id ): array {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_org_user_roles';

		return (array) $wpdb->get_col(
			$wpdb->prepare(
				"SELECT role FROM $table WHERE user_id = %d AND org_id = %d",
				$user_id,
				$org_id
			)
		);
	}

	public static function remove_role( int $user_id, int $org_id ): void {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_org_user_roles';
		$wpdb->delete(
			$table,
			array(
				'user_id' => $user_id,
				'org_id'  => $org_id,
			)
		);
	}

	public static function get_user_orgs( int $user_id ): array {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_org_user_roles';
		return (array) $wpdb->get_results(
			$wpdb->prepare( "SELECT org_id, role, status FROM $table WHERE user_id = %d", $user_id ),
			ARRAY_A
		);
	}

	public static function get_org_users( int $org_id ): array {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_org_user_roles';
		return (array) $wpdb->get_results(
			$wpdb->prepare( "SELECT user_id, role FROM $table WHERE org_id = %d", $org_id ),
			ARRAY_A
		);
	}
}

function ap_user_has_org_role( int $user_id, int $org_id, ?string $role = null ): bool {
	global $wpdb;
	$table = $wpdb->prefix . 'ap_org_user_roles';
	$sql   = "SELECT COUNT(*) FROM $table WHERE user_id = %d AND org_id = %d";
	$args  = array( $user_id, $org_id );
	if ( $role ) {
		$sql   .= ' AND role = %s';
		$args[] = sanitize_key( $role );
	}

	$count = (int) $wpdb->get_var( $wpdb->prepare( $sql, ...$args ) );
	return $count > 0;
}

function ap_user_has_org_capability( int $user_id, int $org_id, string $capability ): bool {
	if ( user_can( $user_id, $capability ) ) {
		return true;
	}

	$map = array(
		'edit_events'     => array( 'admin', 'editor' ),
		'curator_threads' => array( 'curator', 'admin' ),
		'build_feed'      => array( 'admin', 'promoter' ),
		'view_analytics'  => array( 'admin' ),
	);

	$roles = MultiOrgRoles::get_user_roles( $user_id, $org_id );
	foreach ( $roles as $role ) {
		if ( isset( $map[ $capability ] ) && in_array( $role, $map[ $capability ], true ) ) {
			return true;
		}
	}

	return false;
}
