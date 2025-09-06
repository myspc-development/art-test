<?php
namespace ArtPulse\Admin;

use ArtPulse\Core\OrgContext;
use ArtPulse\Core\MultiOrgRoles;

class OrgUserManager {

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'addMenu' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue' ) );
	}

	public static function addMenu(): void {
				add_submenu_page(
					'artpulse-settings',
					__( 'Org User Manager', 'artpulse' ),
					__( 'Org Users', 'artpulse' ),
					'manage_options',
					'ap-org-user-manager',
					array( self::class, 'render' )
				);
	}

	public static function enqueue( string $hook ): void {
		if ( $hook !== 'artpulse-settings_page_ap-org-user-manager' ) {
			return;
		}
		$path = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . 'assets/js/ap-org-user-manager.js';
		$url  = plugin_dir_url( ARTPULSE_PLUGIN_FILE ) . 'assets/js/ap-org-user-manager.js';
		if ( file_exists( $path ) ) {
			wp_enqueue_script(
				'ap-org-user-manager',
				$url,
				array( 'wp-api-fetch' ),
				filemtime( $path ),
				true
			);
			wp_localize_script(
				'ap-org-user-manager',
				'APOrgUserManager',
				array(
					'apiRoot' => esc_url_raw( rest_url() ),
					'nonce'   => wp_create_nonce( 'wp_rest' ),
					'orgId'   => self::get_current_org_id(),
				)
			);
		}
	}

	private static function get_current_org_id(): int {
		return OrgContext::get_current_org_id();
	}

	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'Insufficient permissions', 'artpulse' ) );
		}
		$org_id = self::get_current_org_id();
		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Organization Users', 'artpulse' ) . '</h1>';
		echo '<h2 class="ap-card__title">' . esc_html__( 'Invite Users', 'artpulse' ) . '</h2>';
		echo '<form id="ap-org-invite-form" method="post" enctype="multipart/form-data">';
		echo '<input type="file" id="ap-invite-csv" accept=".csv" />';
		echo '<textarea id="ap-invite-emails" rows="3" placeholder="email@example.com"></textarea>';
		$roles = \ArtPulse\Core\OrgRoleManager::get_roles( $org_id );
		echo '<select id="ap-invite-role">';
		foreach ( $roles as $key => $data ) {
			$label = $data['name'] ?? $key;
			echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</option>';
		}
		echo '</select> ';
		echo '<button class="button button-primary" type="submit">' . esc_html__( 'Send Invites', 'artpulse' ) . '</button>';
		echo '</form>';

		$rows  = \ArtPulse\Core\MultiOrgRoles::get_org_users( $org_id );
		$ids   = wp_list_pluck( $rows, 'user_id' );
		$users = $ids ? get_users( array( 'include' => $ids ) ) : array();

		echo '<h2 class="ap-card__title">' . esc_html__( 'Organization Members', 'artpulse' ) . '</h2>';
		echo '<form id="ap-org-user-list">';
		echo '<select id="ap-org-bulk-action">';
		echo '<option value="">' . esc_html__( 'Bulk Action', 'artpulse' ) . '</option>';
		echo '<option value="update">' . esc_html__( 'Update', 'artpulse' ) . '</option>';
		echo '<option value="suspend">' . esc_html__( 'Suspend', 'artpulse' ) . '</option>';
		echo '<option value="delete">' . esc_html__( 'Delete', 'artpulse' ) . '</option>';
		echo '</select> ';
		echo '<button class="button" type="submit">' . esc_html__( 'Apply', 'artpulse' ) . '</button>';
		echo '<table class="widefat striped">';
		echo '<thead><tr><th><input type="checkbox" id="ap-select-all" /></th><th>' . esc_html__( 'User', 'artpulse' ) . '</th><th>' . esc_html__( 'Email', 'artpulse' ) . '</th><th>' . esc_html__( 'Role', 'artpulse' ) . '</th></tr></thead><tbody>';
		foreach ( $users as $user ) {
			$roles = MultiOrgRoles::get_user_roles( $user->ID, $org_id );
			$label = $roles ? implode( ', ', array_map( 'ucfirst', $roles ) ) : 'viewer';
			echo '<tr><td><input type="checkbox" class="ap-user-select" value="' . esc_attr( $user->ID ) . '" /></td><td>' . esc_html( $user->display_name ?: $user->user_login ) . '</td><td>' . esc_html( $user->user_email ) . '</td><td>' . esc_html( $label ) . '</td></tr>';
		}
		if ( empty( $users ) ) {
			echo '<tr><td colspan="4">' . esc_html__( 'No users found.', 'artpulse' ) . '</td></tr>';
		}
		echo '</tbody></table></form></div>';
	}
}
