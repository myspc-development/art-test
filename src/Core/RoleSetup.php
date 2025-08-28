<?php

namespace ArtPulse\Core;

/**
 * Sets up custom roles and capabilities for ArtPulse.
 */
class RoleSetup {

	/**
	 * Cached hierarchy loaded from the database.
	 *
	 * @var array<string, array{parent:?string,display:string}>
	 */
	private static array $cache = array();

	/**
	 * Default hierarchy used when populating the database.
	 *
	 * @var array<string, array{parent:?string,display:string}>
	 */
	private const DEFAULT_HIERARCHY = array(
		'administrator' => array(
			'parent'  => null,
			'display' => 'Administrator',
		),
		'editor'        => array(
			'parent'  => 'administrator',
			'display' => 'Editor',
		),
		'author'        => array(
			'parent'  => 'editor',
			'display' => 'Author',
		),
		'contributor'   => array(
			'parent'  => 'author',
			'display' => 'Contributor',
		),
		'subscriber'    => array(
			'parent'  => 'contributor',
			'display' => 'Subscriber',
		),
		'member'        => array(
			'parent'  => 'subscriber',
			'display' => 'Member',
		),
		'artist'        => array(
			'parent'  => 'member',
			'display' => 'Artist',
		),
		'organization'  => array(
			'parent'  => 'member',
			'display' => 'Organization',
		),
	);
	/**
	 * Run this during plugin activation.
	 */
	public static function install(): void {
		self::add_roles();
		self::assign_capabilities();
		self::install_roles_table();
		self::populate_roles_table();
	}

	private static function add_roles(): void {
		if ( ! get_role( 'member' ) ) {
			add_role( 'member', 'Member', array( 'read' => true ) );
		}

		if ( ! get_role( 'artist' ) ) {
			add_role( 'artist', 'Artist', array( 'read' => true ) );
		}

		if ( ! get_role( 'organization' ) ) {
			add_role( 'organization', 'Organization', array( 'read' => true ) );
		}
	}

	public static function assign_capabilities(): void {
		$cpt_caps = array(
			'artpulse_event',
			'artpulse_artist',
			'artpulse_artwork',
			'artpulse_org',
			'ap_profile_link_req',
			'ap_profile_link',
		);

		$roles_caps = array(
			'member'        => array(
				'read',
				'create_artpulse_events',
				'upload_files',
				// Members need access to the custom dashboard. In previous
				// versions this capability was missing, preventing the widget
				// system from rendering. Adding it here restores the intended
				// behavior without overwriting other caps. For existing sites
				// run `wp cap add member view_artpulse_dashboard` to patch
				// the role if needed.
				'view_artpulse_dashboard',
			),
			'artist'        => array(
				'read',
				'edit_ap_collections',
				'create_ap_collections',
				'create_artpulse_artists',
				'edit_artpulse_artist',
				'read_artpulse_artist',
				'delete_artpulse_artist',
				'edit_artpulse_artists',
				'edit_others_artpulse_artists',
				'publish_artpulse_artists',
				'read_private_artpulse_artists',
				'delete_artpulse_artists',
				'delete_private_artpulse_artists',
				'delete_published_artpulse_artists',
				'delete_others_artpulse_artists',
				'edit_private_artpulse_artists',
				'edit_published_artpulse_artists',
			),
			'organization'  => array(
				'read',
				'edit_ap_collections',
				'create_ap_collections',
				'create_artpulse_orgs',
				'edit_artpulse_org',
				'read_artpulse_org',
				'delete_artpulse_org',
				'edit_artpulse_orgs',
				'edit_others_artpulse_orgs',
				'publish_artpulse_orgs',
				'read_private_artpulse_orgs',
				'delete_artpulse_orgs',
				'delete_private_artpulse_orgs',
				'delete_published_artpulse_orgs',
				'delete_others_artpulse_orgs',
				'edit_private_artpulse_orgs',
				'edit_published_artpulse_orgs',
				'view_artpulse_dashboard',
				'view_analytics',
				'upload_files',
			),
			'curator'       => array(
				'edit_ap_collections',
				'create_ap_collections',
			),
			'administrator' => array(
				'edit_ap_collections',
				'create_ap_collections',
				'view_analytics',
			),
		);

		// Add full capabilities for each CPT to administrator
		foreach ( $cpt_caps as $cpt ) {
			$plural                      = $cpt . 's';
			$roles_caps['administrator'] = array_merge(
				$roles_caps['administrator'],
				array(
					"create_{$plural}",
					"edit_{$cpt}",
					"read_{$cpt}",
					"delete_{$cpt}",
					"edit_{$plural}",
					"edit_others_{$plural}",
					"publish_{$plural}",
					"read_private_{$plural}",
					"delete_{$plural}",
					"delete_private_{$plural}",
					"delete_published_{$plural}",
					"delete_others_{$plural}",
					"edit_private_{$plural}",
					"edit_published_{$plural}",
				)
			);
		}

		// Shared general capabilities
		$shared_caps = array(
			'moderate_link_requests',
			'view_artpulse_dashboard',
			'manage_artpulse_settings',
			'ap_premium_member',
		);

		foreach ( array( 'administrator', 'editor' ) as $admin_role ) {
			$roles_caps[ $admin_role ] = array_merge(
				$roles_caps[ $admin_role ] ?? array(),
				$shared_caps
			);
		}

		// Apply capabilities to each role
		foreach ( $roles_caps as $role_key => $capabilities ) {
			$role = get_role( $role_key );
			if ( ! $role ) {
				continue;
			}
			foreach ( array_unique( $capabilities ) as $cap ) {
				// Only add capabilities that do not already exist to avoid
				// resetting or wiping out existing caps on core roles.
				if ( ! $role->has_cap( $cap ) ) {
					$role->add_cap( $cap );
				}
			}
		}
	}

	/**
	 * Create the role hierarchy table if needed.
	 */
	public static function install_roles_table(): void {
		global $wpdb;
		$table   = $wpdb->prefix . 'ap_roles';
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table (
            role_key varchar(191) NOT NULL,
            parent_role_key varchar(191) NULL,
            display_name varchar(191) NOT NULL,
            PRIMARY KEY  (role_key)
        ) $charset;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( $sql ); }
		dbDelta( $sql );
	}

	/**
	 * Populate the hierarchy table with default relationships.
	 */
	public static function populate_roles_table(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_roles';

		foreach ( self::DEFAULT_HIERARCHY as $role => $data ) {
			$wpdb->replace(
				$table,
				array(
					'role_key'        => $role,
					'parent_role_key' => $data['parent'],
					'display_name'    => $data['display'],
				)
			);
			self::$cache[ $role ] = array(
				'parent'  => $data['parent'],
				'display' => $data['display'],
			);
		}
	}

	/**
	 * Ensure the hierarchy table exists. Useful for upgrades.
	 */
	public static function maybe_install_table(): void {
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_roles';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists !== $table ) {
			self::install_roles_table();
			self::populate_roles_table();
		}
	}

	public static function get_parent_role( string $role ): ?string {
		self::ensure_cache();
		return self::$cache[ $role ]['parent'] ?? null;
	}

	public static function get_child_roles( string $role ): array {
		self::ensure_cache();
		$children = array();
		foreach ( self::$cache as $key => $data ) {
			if ( $data['parent'] === $role ) {
				$children[] = $key;
			}
		}
		return $children;
	}

	private static function ensure_cache(): void {
		if ( ! empty( self::$cache ) ) {
			return;
		}

		global $wpdb;
		$table  = $wpdb->prefix . 'ap_roles';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists === $table ) {
			$rows = $wpdb->get_results( "SELECT role_key, parent_role_key, display_name FROM $table", ARRAY_A );
			foreach ( $rows as $row ) {
				self::$cache[ $row['role_key'] ] = array(
					'parent'  => $row['parent_role_key'] ?: null,
					'display' => $row['display_name'],
				);
			}
		}
	}
}
