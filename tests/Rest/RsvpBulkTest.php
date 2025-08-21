<?php
namespace ArtPulse\Rest\Tests;

use WP_REST_Request;
use ArtPulse\Rest\RsvpDbController;

class RsvpBulkTest extends \WP_UnitTestCase {
    private int $org;
    private int $event;
    private int $r1;
    private int $r2;

    public function set_up(): void {
        parent::set_up();
        \ArtPulse\Core\Activator::activate();
        $this->org = self::factory()->user->create(['role' => 'administrator']);
        $this->event = wp_insert_post([
            'post_title' => 'Event',
            'post_type' => 'artpulse_event',
            'post_status' => 'publish',
            'post_author' => $this->org,
        ]);
        global $wpdb;
        $table = $wpdb->prefix . 'ap_rsvps';
        $wpdb->insert($table, [
            'event_id' => $this->event,
            'user_id' => 0,
            'name' => '=1+2',
            'email' => 'a@example.com',
            'status' => 'going',
            'created_at' => '2023-01-01 00:00:00',
        ]);
        $this->r1 = $wpdb->insert_id;
        $wpdb->insert($table, [
            'event_id' => $this->event,
            'user_id' => 0,
            'name' => '+SUM(A1)',
            'email' => 'b@example.com',
            'status' => 'going',
            'created_at' => '2023-01-02 00:00:00',
        ]);
        $this->r2 = $wpdb->insert_id;
        RsvpDbController::register();
        do_action('rest_api_init');
    }

    public function test_bulk_update_and_csv(): void {
        wp_set_current_user($this->org);
        $req = new WP_REST_Request('POST', '/ap/v1/rsvps/bulk');
        $req->set_body_params(['event_id' => $this->event, 'ids' => [$this->r1, $this->r2], 'status' => 'cancelled']);
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());
        $this->assertSame(['updated' => 2], $res->get_data());
        global $wpdb;
        $table = $wpdb->prefix . 'ap_rsvps';
        $statuses = $wpdb->get_col("SELECT status FROM $table WHERE id IN ($this->r1,$this->r2) ORDER BY id");
        $this->assertSame(['cancelled', 'cancelled'], $statuses);

        $req = new WP_REST_Request('GET', '/ap/v1/rsvps/export.csv');
        $req->set_param('event_id', $this->event);
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());
        $this->assertSame('text/csv; charset=utf-8', $res->get_headers()['Content-Type']);
        $lines = explode("\n", trim($res->get_data()));
        $this->assertStringContainsString("',=1+2", $lines[1]);
        $this->assertStringContainsString("',+SUM", $lines[2]);
    }

    public function test_permissions(): void {
        wp_set_current_user(0);
        $req = new WP_REST_Request('POST', '/ap/v1/rsvps/bulk');
        $req->set_body_params(['event_id' => $this->event, 'ids' => [$this->r1], 'status' => 'cancelled']);
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(401, $res->get_status());
        $req = new WP_REST_Request('GET', '/ap/v1/rsvps/export.csv');
        $req->set_param('event_id', $this->event);
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(401, $res->get_status());
    }
}
