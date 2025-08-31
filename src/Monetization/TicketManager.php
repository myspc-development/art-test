<?php
namespace ArtPulse\Monetization;

use ArtPulse\Support\FileSystem;

/**
 * Manages paid tickets and tiers.
 */
class TicketManager {

	/**
	 * Register actions.
	 */
	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
		add_action( 'init', array( self::class, 'maybe_install_tables' ) );
		add_action( 'artpulse_ticket_purchased', array( self::class, 'send_private_link_email' ), 10, 4 );
	}

	/**
	 * REST endpoints for ticket operations.
	 */
	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/event/(?P<id>\\d+)/tickets' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/event/(?P<id>\\d+)/tickets',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'list_tickets' ),
					'permission_callback' => array( self::class, 'check_logged_in' ),
					'args'                => array( 'id' => array( 'validate_callback' => 'absint' ) ),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/event/(?P<id>\\d+)/buy-ticket' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/event/(?P<id>\\d+)/buy-ticket',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'buy_ticket' ),
					'permission_callback' => array( self::class, 'check_logged_in' ),
					'args'                => array( 'id' => array( 'validate_callback' => 'absint' ) ),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/event/(?P<id>\\d+)/ticket-tier' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/event/(?P<id>\\d+)/ticket-tier',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'create_ticket_tier' ),
					'permission_callback' => array( self::class, 'check_manage' ),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/ticket-tier/(?P<tier_id>\\d+)' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/ticket-tier/(?P<tier_id>\\d+)',
				array(
					array(
						'methods'             => 'PUT',
						'callback'            => array( self::class, 'update_ticket_tier' ),
						'permission_callback' => array( self::class, 'check_manage' ),
					),
					array(
						'methods'             => 'DELETE',
						'callback'            => array( self::class, 'delete_ticket_tier' ),
						'permission_callback' => array( self::class, 'check_manage' ),
					),
				)
			);
		}
	}

	public static function check_logged_in() {
		if ( ! current_user_can( 'read' ) ) {
			return new \WP_Error( 'rest_forbidden', __( 'Unauthorized.', 'artpulse' ), array( 'status' => 403 ) );
		}
		return true;
	}

	public static function check_manage() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error( 'rest_forbidden', __( 'Unauthorized.', 'artpulse' ), array( 'status' => 403 ) );
		}
		return true;
	}

	/**
	 * Ensure DB tables exist.
	 */
	public static function maybe_install_tables(): void {
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_event_tickets';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists !== $table ) {
			self::install_tickets_table();
		}

		$table  = $wpdb->prefix . 'ap_tickets';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists !== $table ) {
			self::install_purchases_table();
		}
	}

	public static function install_tickets_table(): void {
		global $wpdb;
		$table   = $wpdb->prefix . 'ap_event_tickets';
		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE $table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (id),
            event_id BIGINT NOT NULL,
            name VARCHAR(100) NOT NULL,
            price DECIMAL(10,2) NOT NULL DEFAULT 0,
            inventory INT NOT NULL DEFAULT 0,
            max_per_user INT NOT NULL DEFAULT 0,
            sold INT NOT NULL DEFAULT 0,
            start_date DATETIME NULL,
            end_date DATETIME NULL,
            product_id BIGINT NULL,
            stripe_price_id VARCHAR(255) NULL,
            tier_order INT NOT NULL DEFAULT 0,
            KEY event_id (event_id)
        ) $charset;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( $sql ); }
		dbDelta( $sql );
		file_put_contents(
			plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . 'install.log',
			'[' . current_time( 'mysql' ) . "] Created table $table\n",
			FILE_APPEND
		);
	}

	public static function install_purchases_table(): void {
		global $wpdb;
		$table   = $wpdb->prefix . 'ap_tickets';
		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE $table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (id),
            user_id BIGINT NOT NULL,
            event_id BIGINT NOT NULL,
            ticket_tier_id BIGINT NOT NULL,
            code VARCHAR(64) NOT NULL,
            purchase_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            UNIQUE KEY code (code),
            KEY user_id (user_id),
            KEY event_id (event_id)
        ) $charset;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( $sql ); }
		dbDelta( $sql );
		file_put_contents(
			plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . 'install.log',
			'[' . current_time( 'mysql' ) . "] Created table $table\n",
			FILE_APPEND
		);
	}

	public static function list_tickets( \WP_REST_Request $req ) {
		$event_id = absint( $req->get_param( 'id' ) );
		if ( ! $event_id ) {
			return new \WP_Error( 'invalid_event', 'Invalid event.', array( 'status' => 400 ) );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'ap_event_tickets';
		$now   = current_time( 'mysql' );

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table WHERE event_id = %d AND (start_date IS NULL OR start_date <= %s) AND (end_date IS NULL OR end_date >= %s) AND (inventory = 0 OR sold < inventory) ORDER BY tier_order ASC",
				$event_id,
				$now,
				$now
			),
			ARRAY_A
		);

		return \rest_ensure_response( $rows );
	}

	public static function buy_ticket( \WP_REST_Request $req ) {
		$event_id  = absint( $req->get_param( 'id' ) );
		$ticket_id = absint( $req->get_param( 'ticket_id' ) );
		$qty       = max( 1, intval( $req->get_param( 'quantity' ) ) );
		$user_id   = get_current_user_id();

		if ( ! $event_id || ! $ticket_id ) {
			return new \WP_Error( 'invalid_params', 'Invalid parameters.', array( 'status' => 400 ) );
		}

		global $wpdb;
		$table  = $wpdb->prefix . 'ap_event_tickets';
		$ticket = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $ticket_id ), ARRAY_A );
		if ( ! $ticket || intval( $ticket['event_id'] ) !== $event_id ) {
			return new \WP_Error( 'invalid_ticket', 'Invalid ticket.', array( 'status' => 404 ) );
		}

		$now = current_time( 'mysql' );
		if ( ( $ticket['start_date'] && $now < $ticket['start_date'] ) || ( $ticket['end_date'] && $now > $ticket['end_date'] ) ) {
			return new \WP_Error( 'sale_closed', 'Ticket sales closed.', array( 'status' => 400 ) );
		}

		if ( $ticket['inventory'] > 0 && ( $ticket['sold'] + $qty ) > $ticket['inventory'] ) {
			return new \WP_Error( 'sold_out', 'Not enough inventory.', array( 'status' => 409 ) );
		}

		$ticket_table = $wpdb->prefix . 'ap_tickets';
		if ( intval( $ticket['max_per_user'] ) > 0 ) {
			$count = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM $ticket_table WHERE user_id = %d AND ticket_tier_id = %d",
					$user_id,
					$ticket_id
				)
			);
			if ( ( $count + $qty ) > intval( $ticket['max_per_user'] ) ) {
				return new \WP_Error( 'limit_reached', 'Ticket limit reached.', array( 'status' => 409 ) );
			}
		}

		$wpdb->query( 'START TRANSACTION' );
		$updated = $wpdb->query(
			$wpdb->prepare(
				"UPDATE $table SET sold = sold + %d WHERE id = %d AND (inventory = 0 OR sold + %d <= inventory)",
				$qty,
				$ticket_id,
				$qty
			)
		);

		if ( ! $updated ) {
			$wpdb->query( 'ROLLBACK' );
			return new \WP_Error( 'sold_out', 'Unable to reserve tickets.', array( 'status' => 409 ) );
		}

		$code = wp_generate_password( 12, false, false );
		$wpdb->insert(
			$ticket_table,
			array(
				'user_id'        => $user_id,
				'event_id'       => $event_id,
				'ticket_tier_id' => $ticket_id,
				'code'           => $code,
				'purchase_date'  => current_time( 'mysql' ),
				'status'         => 'active',
			),
			array( '%d', '%d', '%d', '%s', '%s', '%s' )
		);
		$wpdb->query( 'COMMIT' );

		$user = wp_get_current_user();
		if ( $user && is_email( $user->user_email ) ) {
			$pdf = \ArtPulse\Core\DocumentGenerator::generate_ticket_pdf(
				array(
					'event_title' => get_the_title( $event_id ),
					'ticket_code' => $code,
				)
			);

			$body    = sprintf( __( 'Your ticket code is %s', 'artpulse' ), $code );
			$virtual = get_post_meta( $event_id, '_ap_virtual_event_url', true );
			$enabled = get_post_meta( $event_id, '_ap_virtual_access_enabled', true );
			if ( $enabled && $virtual ) {
				$body .= '<br/><br/>' . sprintf( __( 'Join here: %s', 'artpulse' ), esc_url( $virtual ) );
			}
			$message = \ArtPulse\Core\EmailTemplateManager::render(
				$body,
				array(
					'username'    => $user->user_login,
					'event_title' => get_the_title( $event_id ),
				)
			);
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
			\ArtPulse\Core\EmailService::send(
				$user->user_email,
				sprintf( __( 'Ticket for %s', 'artpulse' ), get_the_title( $event_id ) ),
				$message,
				$headers,
				$pdf ? array( $pdf ) : array()
			);
			if ( $pdf ) {
				FileSystem::safe_unlink( $pdf );
			}
		}

		do_action( 'artpulse_ticket_purchased', $user_id, $event_id, $ticket_id, $qty );

		return \rest_ensure_response( array( 'ticket_code' => $code ) );
	}

	/**
	 * Handle a completed WooCommerce order.
	 */
	public static function handle_completed_order( int $user_id, int $ticket_id, int $qty ): void {
		do_action( 'artpulse_ticket_purchased', $user_id, 0, $ticket_id, $qty );
	}

	/**
	 * Send a virtual access link when a ticket purchase is confirmed.
	 */
	public static function send_private_link_email( int $user_id, int $event_id, int $ticket_id, int $qty ): void {
		// avoid duplicate emails when REST purchase already handled
		if ( $event_id ) {
			return;
		}

		global $wpdb;
		$table    = $wpdb->prefix . 'ap_tickets';
		$event_id = intval( $wpdb->get_var( $wpdb->prepare( "SELECT event_id FROM $table WHERE id = %d", $ticket_id ) ) );

		$virtual = get_post_meta( $event_id, '_ap_virtual_event_url', true );
		$enabled = get_post_meta( $event_id, '_ap_virtual_access_enabled', true );
		$user    = get_user_by( 'id', $user_id );
		if ( ! $user || ! $enabled || ! $virtual || ! is_email( $user->user_email ) ) {
			return;
		}

		$body    = sprintf( __( 'Access your event here: %s', 'artpulse' ), esc_url( $virtual ) );
		$message = \ArtPulse\Core\EmailTemplateManager::render(
			$body,
			array(
				'username'    => $user->user_login,
				'event_title' => get_the_title( $event_id ),
			)
		);
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		\ArtPulse\Core\EmailService::send(
			$user->user_email,
			sprintf( __( 'Access link for %s', 'artpulse' ), get_the_title( $event_id ) ),
			$message,
			$headers
		);
	}

	/**
	 * Check if a user has a valid ticket for an event.
	 */
	public static function user_has_ticket( int $user_id, int $event_id ): bool {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_tickets';
		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $table WHERE user_id = %d AND event_id = %d AND status = 'active'",
				$user_id,
				$event_id
			)
		);
		return intval( $count ) > 0;
	}

	public static function create_ticket_tier( \WP_REST_Request $req ) {
		$event_id = absint( $req->get_param( 'id' ) );
		$name     = sanitize_text_field( $req->get_param( 'name' ) );
		$price    = floatval( $req->get_param( 'price' ) );
		$inv      = intval( $req->get_param( 'inventory' ) );
		$limit    = intval( $req->get_param( 'max_per_user' ) );

		if ( ! $event_id || ! $name ) {
			return new \WP_Error( 'invalid_params', 'Invalid parameters', array( 'status' => 400 ) );
		}
		global $wpdb;
		$table = $wpdb->prefix . 'ap_event_tickets';
		$wpdb->insert(
			$table,
			array(
				'event_id'     => $event_id,
				'name'         => $name,
				'price'        => $price,
				'inventory'    => $inv,
				'max_per_user' => $limit,
			)
		);
		return \rest_ensure_response( array( 'id' => $wpdb->insert_id ) );
	}

	public static function update_ticket_tier( \WP_REST_Request $req ) {
		$tier_id = absint( $req['tier_id'] );
		$data    = array();
		if ( $req->get_param( 'name' ) !== null ) {
			$data['name'] = sanitize_text_field( $req->get_param( 'name' ) );
		}
		if ( $req->get_param( 'price' ) !== null ) {
			$data['price'] = floatval( $req->get_param( 'price' ) );
		}
		if ( $req->get_param( 'inventory' ) !== null ) {
			$data['inventory'] = intval( $req->get_param( 'inventory' ) );
		}
		if ( $req->get_param( 'max_per_user' ) !== null ) {
			$data['max_per_user'] = intval( $req->get_param( 'max_per_user' ) );
		}
		if ( empty( $data ) ) {
			return new \WP_Error( 'no_data', 'No data provided', array( 'status' => 400 ) );
		}
		global $wpdb;
		$table = $wpdb->prefix . 'ap_event_tickets';
		$wpdb->update( $table, $data, array( 'id' => $tier_id ) );
		return \rest_ensure_response( array( 'updated' => true ) );
	}

	public static function delete_ticket_tier( \WP_REST_Request $req ) {
		$tier_id = absint( $req['tier_id'] );
		global $wpdb;
		$table = $wpdb->prefix . 'ap_event_tickets';
		$wpdb->delete( $table, array( 'id' => $tier_id ) );
		return \rest_ensure_response( array( 'deleted' => true ) );
	}
}
