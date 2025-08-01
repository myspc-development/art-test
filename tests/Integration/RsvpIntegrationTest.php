<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Admin\MetaBoxesEvent;
use ArtPulse\Rest\RsvpRestController;
use WP_REST_Request;

class RsvpIntegrationTest extends \WP_UnitTestCase
{
    private int $event_id;
    private int $user1;
    private int $user2;
    private array $emails = [];

    public function set_up(): void
    {
        parent::set_up();
        do_action('init');
        add_filter('pre_wp_mail', [$this, 'capture_mail'], 10, 6);

        $this->user1 = self::factory()->user->create(['role' => 'subscriber']);
        $this->user2 = self::factory()->user->create(['role' => 'subscriber']);

        $this->event_id = wp_insert_post([
            'post_title'  => 'Integration Event',
            'post_type'   => 'artpulse_event',
            'post_status' => 'draft',
            'post_author' => $this->user1,
        ]);
        update_post_meta($this->event_id, 'event_rsvp_enabled', '1');
        update_post_meta($this->event_id, 'event_rsvp_limit', 1);
        update_post_meta($this->event_id, 'event_rsvp_list', []);
        update_post_meta($this->event_id, 'event_waitlist', []);

        RsvpRestController::register();
        do_action('rest_api_init');
    }

    public function tear_down(): void
    {
        remove_filter('pre_wp_mail', [$this, 'capture_mail'], 10);
        $_POST = [];
        parent::tear_down();
    }

    public function capture_mail(): bool
    {
        $args = func_get_args();
        $this->emails[] = $args;
        return true;
    }

    public function test_rsvp_meta_fields_saved(): void
    {
        $admin = self::factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($admin);
        $post_id = wp_insert_post([
            'post_title'  => 'Meta Event',
            'post_type'   => 'artpulse_event',
            'post_status' => 'draft',
            'post_author' => $admin,
        ]);
        $post = get_post($post_id);

        $_POST = [
            'ead_event_meta_nonce_field' => wp_create_nonce('ead_event_meta_nonce'),
            'event_rsvp_enabled'    => '1',
            'event_rsvp_limit'      => '5',
            'event_waitlist_enabled'=> '1',
            'event_rsvp_list'       => '10,11',
            'event_waitlist'        => '12',
        ];

        MetaBoxesEvent::save_event_meta($post_id, $post);

        $this->assertSame('1', get_post_meta($post_id, 'event_rsvp_enabled', true));
        $this->assertSame(5, (int) get_post_meta($post_id, 'event_rsvp_limit', true));
        $this->assertSame('1', get_post_meta($post_id, 'event_waitlist_enabled', true));
        $this->assertSame(['10','11'], get_post_meta($post_id, 'event_rsvp_list', true));
        $this->assertSame(['12'], get_post_meta($post_id, 'event_waitlist', true));
    }

    public function test_rest_join_cancel_updates_meta_and_promotes_waitlist(): void
    {
        // User1 joins first
        wp_set_current_user($this->user1);
        $req = new WP_REST_Request('POST', '/artpulse/v1/rsvp');
        $req->set_param('event_id', $this->event_id);
        rest_get_server()->dispatch($req);

        // User2 attempts to join, should be waitlisted
        wp_set_current_user($this->user2);
        $req = new WP_REST_Request('POST', '/artpulse/v1/rsvp');
        $req->set_param('event_id', $this->event_id);
        rest_get_server()->dispatch($req);

        $this->assertSame([$this->user1], get_post_meta($this->event_id, 'event_rsvp_list', true));
        $this->assertSame([$this->user2], get_post_meta($this->event_id, 'event_waitlist', true));

        // User1 cancels which should promote User2
        wp_set_current_user($this->user1);
        $req = new WP_REST_Request('POST', '/artpulse/v1/rsvp/cancel');
        $req->set_param('event_id', $this->event_id);
        rest_get_server()->dispatch($req);

        $this->assertSame([$this->user2], get_post_meta($this->event_id, 'event_rsvp_list', true));
        $this->assertEmpty(get_post_meta($this->event_id, 'event_waitlist', true));
    }
}
