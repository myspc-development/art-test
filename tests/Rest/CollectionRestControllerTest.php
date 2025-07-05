<?php
namespace ArtPulse\Rest\Tests;

use WP_REST_Request;
use ArtPulse\Rest\CollectionRestController;

/**
 * @group restapi
 */
class CollectionRestControllerTest extends \WP_UnitTestCase
{
    private int $event_id;
    private int $artwork_id;
    private int $collection_id;

    public function set_up(): void
    {
        parent::set_up();

        $this->event_id = self::factory()->post->create([
            'post_type'   => 'artpulse_event',
            'post_title'  => 'Event',
            'post_status' => 'publish',
        ]);

        $this->artwork_id = self::factory()->post->create([
            'post_type'   => 'artpulse_artwork',
            'post_title'  => 'Artwork',
            'post_status' => 'publish',
        ]);

        $this->collection_id = self::factory()->post->create([
            'post_type'   => 'ap_collection',
            'post_title'  => 'Collection',
            'post_status' => 'publish',
            'meta_input'  => [
                'ap_collection_items' => [ $this->event_id, $this->artwork_id ],
            ],
        ]);

        CollectionRestController::register();
        do_action('rest_api_init');
    }

    public function test_get_collections(): void
    {
        $req = new WP_REST_Request('GET', '/artpulse/v1/collections');
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());
        $data = $res->get_data();
        $this->assertCount(1, $data);
        $this->assertSame($this->collection_id, $data[0]['id']);
        $this->assertEquals([$this->event_id, $this->artwork_id], $data[0]['items']);
    }

    public function test_get_single_collection(): void
    {
        $req = new WP_REST_Request('GET', '/artpulse/v1/collection/' . $this->collection_id);
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());
        $data = $res->get_data();
        $this->assertSame($this->collection_id, $data['id']);
        $this->assertEquals([$this->event_id, $this->artwork_id], $data['items']);
    }

    public function test_create_collection(): void
    {
        $user = self::factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($user);

        $req = new WP_REST_Request('POST', '/artpulse/v1/collections');
        $req->set_param('title', 'New Collection');
        $req->set_param('items', [ $this->event_id ]);
        $res = rest_get_server()->dispatch($req);

        $this->assertSame(200, $res->get_status());
        $id = $res->get_data()['id'];
        $this->assertNotEmpty($id);
        $this->assertEquals([ $this->event_id ], get_post_meta($id, 'ap_collection_items', true));
    }
}
