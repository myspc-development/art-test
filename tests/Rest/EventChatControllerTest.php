<?php
namespace ArtPulse\Rest\Tests;

use WP_REST_Request;
use ArtPulse\DB\Chat;
use ArtPulse\Rest\EventChatPostController;
use ArtPulse\Rest\EventChatController;

/**
 * @group restapi
 */
class EventChatControllerTest extends \WP_UnitTestCase
{
    private int $event;
    private int $user;

    public function set_up(): void
    {
        parent::set_up();
        Chat\install_tables();
        EventChatController::register();
        EventChatPostController::register();
        do_action('rest_api_init');

        $this->event = self::factory()->post->create([
            'post_type'   => 'artpulse_event',
            'post_status' => 'publish'
        ]);
        $this->user = self::factory()->user->create();
        update_post_meta($this->event, 'event_rsvp_list', [$this->user]);
        wp_set_current_user($this->user);
    }

    public function test_post_and_get_chat(): void
    {
        $post = new WP_REST_Request('POST', '/artpulse/v1/event/' . $this->event . '/chat');
        $post->set_body_params(['content' => 'Hello']);
        $res = rest_get_server()->dispatch($post);
        $this->assertSame(200, $res->get_status());
        $msg = $res->get_data();

        $get = new WP_REST_Request('GET', '/artpulse/v1/event/' . $this->event . '/chat');
        $res = rest_get_server()->dispatch($get);
        $this->assertSame(200, $res->get_status());
        $data = $res->get_data();
        $this->assertCount(1, $data);
        $this->assertSame('Hello', $data[0]['content']);

        $react = new WP_REST_Request('POST', '/artpulse/v1/chat/' . $msg['id'] . '/reaction');
        $react->set_body_params(['emoji' => '❤️']);
        $res = rest_get_server()->dispatch($react);
        $this->assertSame(200, $res->get_status());

        $res = rest_get_server()->dispatch($get);
        $data = $res->get_data();
        $this->assertSame(1, $data[0]['reactions']['❤️']);
    }
}
