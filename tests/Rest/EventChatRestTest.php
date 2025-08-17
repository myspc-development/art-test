<?php
namespace ArtPulse\Rest\Tests;

use WP_REST_Request;
use ArtPulse\DB\Chat;

/**
 * @group restapi
 */
class EventChatRestTest extends \WP_UnitTestCase
{
    private int $event;
    private int $user;

    public function set_up(): void
    {
        parent::set_up();
        Chat\install_tables();
        do_action('init');
        do_action('rest_api_init');

        $this->event = self::factory()->post->create([
            'post_type'   => 'artpulse_event',
            'post_status' => 'publish',
        ]);
        $this->user = self::factory()->user->create(['display_name' => 'Tester']);

        Chat\insert_message($this->event, $this->user, 'Hi there');

        wp_set_current_user($this->user);
    }

    public function test_get_event_chat_returns_messages(): void
    {
        $req = new WP_REST_Request('GET', '/artpulse/v1/event/' . $this->event . '/chat');
        $res = rest_get_server()->dispatch($req);

        $this->assertSame(200, $res->get_status());
        $data = $res->get_data();
        $this->assertCount(1, $data);
        $this->assertSame('Hi there', $data[0]['content']);
        $this->assertSame('Tester', $data[0]['author']);
        $this->assertSame($this->user, $data[0]['user_id']);
    }
}
