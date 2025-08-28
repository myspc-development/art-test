<?php
namespace ArtPulse\Admin;

class TicketTiersPage {

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'addMenu' ) );
	}

	public static function addMenu(): void {
		add_submenu_page(
			'artpulse-settings',
			__( 'Ticket Tiers', 'artpulse' ),
			__( 'Ticket Tiers', 'artpulse' ),
			'manage_options',
			'ap-ticket-tiers',
			array( self::class, 'render' )
		);
	}

	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Insufficient permissions', 'artpulse' ) );
		}

		$event_id = absint( $_GET['event_id'] ?? 0 );

		if ( isset( $_POST['ap_add_tier'] ) && check_admin_referer( 'ap_add_tier' ) ) {
			global $wpdb;
			$table = $wpdb->prefix . 'ap_event_tickets';
			$wpdb->insert(
				$table,
				array(
					'event_id'  => $event_id,
					'name'      => sanitize_text_field( $_POST['tier_name'] ),
					'price'     => floatval( $_POST['tier_price'] ),
					'inventory' => intval( $_POST['tier_inventory'] ),
				)
			);
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Tier added.', 'artpulse' ) . '</p></div>';
		}

		if ( isset( $_GET['delete'] ) && check_admin_referer( 'ap_del_tier' ) ) {
			global $wpdb;
			$table = $wpdb->prefix . 'ap_event_tickets';
			$wpdb->delete( $table, array( 'id' => absint( $_GET['delete'] ) ) );
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Tier deleted.', 'artpulse' ) . '</p></div>';
		}

		$events = get_posts(
			array(
				'post_type'   => 'artpulse_event',
				'numberposts' => -1,
				'post_status' => 'publish',
			)
		);
		echo '<div class="wrap"><h1>' . esc_html__( 'Ticket Tiers', 'artpulse' ) . '</h1>';
		echo '<form method="get"><input type="hidden" name="page" value="ap-ticket-tiers" />';
		echo '<select name="event_id"><option value="0">' . esc_html__( 'Select Event', 'artpulse' ) . '</option>';
		foreach ( $events as $ev ) {
			echo '<option value="' . esc_attr( $ev->ID ) . '" ' . selected( $event_id, $ev->ID, false ) . '>' . esc_html( $ev->post_title ) . '</option>';
		}
		echo '</select> <button class="button" type="submit">' . esc_html__( 'Load', 'artpulse' ) . '</button></form>';

		if ( $event_id ) {
			global $wpdb;
			$table = $wpdb->prefix . 'ap_event_tickets';
			$tiers = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE event_id = %d", $event_id ) );
			echo '<h2>' . esc_html( get_the_title( $event_id ) ) . '</h2>';
			echo '<table class="widefat"><thead><tr><th>' . esc_html__( 'Name', 'artpulse' ) . '</th><th>' . esc_html__( 'Price', 'artpulse' ) . '</th><th>' . esc_html__( 'Inventory', 'artpulse' ) . '</th><th>' . esc_html__( 'Actions', 'artpulse' ) . '</th></tr></thead><tbody>';
			foreach ( $tiers as $t ) {
				$url = wp_nonce_url( admin_url( 'admin.php?page=ap-ticket-tiers&event_id=' . $event_id . '&delete=' . $t->id ), 'ap_del_tier' );
				echo '<tr><td>' . esc_html( $t->name ) . '</td><td>' . esc_html( $t->price ) . '</td><td>' . esc_html( $t->inventory ) . '</td><td><a href="' . esc_url( $url ) . '">' . esc_html__( 'Delete', 'artpulse' ) . '</a></td></tr>';
			}
			if ( empty( $tiers ) ) {
				echo '<tr><td colspan="4">' . esc_html__( 'No tiers found.', 'artpulse' ) . '</td></tr>';
			}
			echo '</tbody></table>';
			echo '<h3>' . esc_html__( 'Add Tier', 'artpulse' ) . '</h3>';
			echo '<form method="post">';
			wp_nonce_field( 'ap_add_tier' );
			echo '<input type="hidden" name="event_id" value="' . esc_attr( $event_id ) . '" />';
			echo '<input type="text" name="tier_name" placeholder="' . esc_attr__( 'Name', 'artpulse' ) . '" required /> ';
			echo '<input type="number" step="0.01" name="tier_price" placeholder="' . esc_attr__( 'Price', 'artpulse' ) . '" required /> ';
			echo '<input type="number" name="tier_inventory" placeholder="' . esc_attr__( 'Inventory', 'artpulse' ) . '" required /> ';
			echo '<input type="submit" name="ap_add_tier" class="button button-primary" value="' . esc_attr__( 'Add', 'artpulse' ) . '" />';
			echo '</form>';
		}
		echo '</div>';
	}
}
