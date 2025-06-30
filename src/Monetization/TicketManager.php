<?php
namespace ArtPulse\Monetization;

/**
 * Manages paid tickets and tiers.
 */
class TicketManager
{
    /**
     * Register actions.
     */
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
        add_action('init', [self::class, 'maybe_install_tables']);
    }

    /**
     * REST endpoints for ticket operations.
     */
    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/event/(?P<id>\\d+)/tickets', [
            'methods'  => 'GET',
            'callback' => [self::class, 'list_tickets'],
            'permission_callback' => '__return_true',
            'args' => ['id' => ['validate_callback' => 'absint']],
        ]);

        register_rest_route('artpulse/v1', '/event/(?P<id>\\d+)/buy-ticket', [
            'methods'  => 'POST',
            'callback' => [self::class, 'buy_ticket'],
            'permission_callback' => [self::class, 'check_logged_in'],
            'args' => ['id' => ['validate_callback' => 'absint']],
        ]);
    }

    public static function check_logged_in(): bool
    {
        return is_user_logged_in();
    }

    /**
     * Ensure DB tables exist.
     */
    public static function maybe_install_tables(): void
    {
        global $wpdb;
        $table  = $wpdb->prefix . 'ap_event_tickets';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            self::install_tickets_table();
        }

        $table  = $wpdb->prefix . 'ap_tickets';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            self::install_purchases_table();
        }
    }

    public static function install_tickets_table(): void
    {
        global $wpdb;
        $table   = $wpdb->prefix . 'ap_event_tickets';
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id BIGINT AUTO_INCREMENT,
            PRIMARY KEY (id),
            event_id BIGINT NOT NULL,
            name VARCHAR(100) NOT NULL,
            price DECIMAL(10,2) NOT NULL DEFAULT 0,
            inventory INT NOT NULL DEFAULT 0,
            sold INT NOT NULL DEFAULT 0,
            start_date DATETIME NULL,
            end_date DATETIME NULL,
            product_id BIGINT NULL,
            stripe_price_id VARCHAR(255) NULL,
            tier_order INT NOT NULL DEFAULT 0,
            KEY event_id (event_id)
        ) $charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function install_purchases_table(): void
    {
        global $wpdb;
        $table   = $wpdb->prefix . 'ap_tickets';
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id BIGINT AUTO_INCREMENT,
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
        dbDelta($sql);
    }

    public static function list_tickets(\WP_REST_Request $req)
    {
        $event_id = absint($req->get_param('id'));
        if (!$event_id) {
            return new \WP_Error('invalid_event', 'Invalid event.', ['status' => 400]);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'ap_event_tickets';
        $now   = current_time('mysql');

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE event_id = %d AND (start_date IS NULL OR start_date <= %s) AND (end_date IS NULL OR end_date >= %s) AND (inventory = 0 OR sold < inventory) ORDER BY tier_order ASC",
                $event_id,
                $now,
                $now
            ),
            ARRAY_A
        );

        return rest_ensure_response($rows);
    }

    public static function buy_ticket(\WP_REST_Request $req)
    {
        $event_id  = absint($req->get_param('id'));
        $ticket_id = absint($req->get_param('ticket_id'));
        $qty       = max(1, intval($req->get_param('quantity')));
        $user_id   = get_current_user_id();

        if (!$event_id || !$ticket_id) {
            return new \WP_Error('invalid_params', 'Invalid parameters.', ['status' => 400]);
        }

        global $wpdb;
        $table  = $wpdb->prefix . 'ap_event_tickets';
        $ticket = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $ticket_id), ARRAY_A);
        if (!$ticket || intval($ticket['event_id']) !== $event_id) {
            return new \WP_Error('invalid_ticket', 'Invalid ticket.', ['status' => 404]);
        }

        $now = current_time('mysql');
        if (($ticket['start_date'] && $now < $ticket['start_date']) || ($ticket['end_date'] && $now > $ticket['end_date'])) {
            return new \WP_Error('sale_closed', 'Ticket sales closed.', ['status' => 400]);
        }

        if ($ticket['inventory'] > 0 && ($ticket['sold'] + $qty) > $ticket['inventory']) {
            return new \WP_Error('sold_out', 'Not enough inventory.', ['status' => 409]);
        }

        $wpdb->query('START TRANSACTION');
        $updated = $wpdb->query(
            $wpdb->prepare(
                "UPDATE $table SET sold = sold + %d WHERE id = %d AND (inventory = 0 OR sold + %d <= inventory)",
                $qty,
                $ticket_id,
                $qty
            )
        );

        if (!$updated) {
            $wpdb->query('ROLLBACK');
            return new \WP_Error('sold_out', 'Unable to reserve tickets.', ['status' => 409]);
        }

        $ticket_table = $wpdb->prefix . 'ap_tickets';
        $code         = wp_generate_password(12, false, false);
        $wpdb->insert(
            $ticket_table,
            [
                'user_id'        => $user_id,
                'event_id'       => $event_id,
                'ticket_tier_id' => $ticket_id,
                'code'           => $code,
                'purchase_date'  => current_time('mysql'),
                'status'         => 'active',
            ],
            [ '%d', '%d', '%d', '%s', '%s', '%s' ]
        );
        $wpdb->query('COMMIT');

        $user = wp_get_current_user();
        if ($user && is_email($user->user_email)) {
            $pdf = \ArtPulse\Core\DocumentGenerator::generate_ticket_pdf([
                'event_title' => get_the_title($event_id),
                'ticket_code' => $code,
            ]);

            $body    = sprintf(__('Your ticket code is %s', 'artpulse'), $code);
            $message = \ArtPulse\Core\EmailTemplateManager::render($body, [
                'username'    => $user->user_login,
                'event_title' => get_the_title($event_id),
            ]);
            $headers = ['Content-Type: text/html; charset=UTF-8'];
            wp_mail(
                $user->user_email,
                sprintf(__('Ticket for %s', 'artpulse'), get_the_title($event_id)),
                $message,
                $headers,
                $pdf ? [$pdf] : []
            );
            if ($pdf) {
                unlink($pdf);
            }
        }

        do_action('artpulse_ticket_purchased', $user_id, $event_id, $ticket_id, $qty);

        return rest_ensure_response(['ticket_code' => $code]);
    }
}
