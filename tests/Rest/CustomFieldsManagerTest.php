<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Admin\CustomFieldsManager;


/**
 * @group restapi
 */
class CustomFieldsManagerTest extends \WP_UnitTestCase
{
    private int $event_id;
    private int $user_id;

    public function set_up(): void
    {
        parent::set_up();
        $this->user_id = self::factory()->user->create();
        $this->event_id = wp_insert_post([
            'post_title' => 'Event',
            'post_type'  => 'artpulse_event',
            'post_status'=> 'draft',
            'post_author'=> $this->user_id,
        ]);
        wp_set_current_user($this->user_id);
        CustomFieldsManager::register();
        do_action('rest_api_init');
    }

    public function test_get_returns_saved_fields(): void
    {
        update_post_meta($this->event_id, 'ap_rsvp_custom_fields', ['foo' => 'Foo']);
        $req = new \WP_REST_Request('GET', "/artpulse/v1/event/{$this->event_id}/rsvp/custom-fields");
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());
        $this->assertSame(['foo' => 'Foo'], $res->get_data());
    }

    public function test_post_saves_fields(): void
    {
        $req = new \WP_REST_Request('POST', "/artpulse/v1/event/{$this->event_id}/rsvp/custom-fields");
        $req->set_param('fields', ['bar' => 'Bar']);
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());
        $this->assertSame(['bar' => 'Bar'], get_post_meta($this->event_id, 'ap_rsvp_custom_fields', true));
    }
}
