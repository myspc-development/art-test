<?php
namespace ArtPulse\Rest\Tests;

use WP_REST_Request;
use ArtPulse\Community\DirectMessages;

/**
 * @group restapi
 */
class DirectMessagesTest extends \WP_UnitTestCase
{
    private int $user1;
    private int $user2;
    private array $mails = [];
    private string $nonce;

    public function set_up(): void
    {
        parent::set_up();
        DirectMessages::install_table();
        DirectMessages::install_flags_table();
        DirectMessages::register();
        do_action('rest_api_init');

        $this->user1 = self::factory()->user->create(['user_email' => 'u1@test.com']);
        $this->user2 = self::factory()->user->create(['user_email' => 'u2@test.com']);

        wp_set_current_user($this->user1);
        $user = new \WP_User($this->user1);
        $user->add_cap('ap_send_messages');
        $this->nonce = wp_create_nonce('ap_messages_nonce');
        add_filter('pre_wp_mail', [$this, 'capture_mail'], 10, 6);
    }

    public function tear_down(): void
    {
        remove_filter('pre_wp_mail', [$this, 'capture_mail'], 10);
        parent::tear_down();
    }

    public function capture_mail(): bool
    {
        $this->mails[] = func_get_args();
        return true;
    }

    public function test_send_and_fetch_message(): void
    {
        $post = new WP_REST_Request('POST', '/artpulse/v1/messages');
        $post->set_param('recipient_id', $this->user2);
        $post->set_param('content', 'Hello');
        $post->set_param('nonce', $this->nonce);
        $res = rest_get_server()->dispatch($post);
        $this->assertSame(200, $res->get_status());

        global $wpdb;
        $table = $wpdb->prefix . 'ap_messages';
        $row   = $wpdb->get_row("SELECT * FROM $table", ARRAY_A);
        $this->assertSame($this->user1, (int) $row['sender_id']);
        $this->assertSame($this->user2, (int) $row['recipient_id']);
        $this->assertSame('Hello', $row['content']);
        $this->assertSame('0', $row['is_read']);

        $this->assertCount(1, $this->mails);

        $get = new WP_REST_Request('GET', '/artpulse/v1/messages');
        $get->set_param('with', $this->user2);
        $get->set_param('nonce', $this->nonce);
        $res = rest_get_server()->dispatch($get);
        $this->assertSame(200, $res->get_status());
        $data = $res->get_data();
        $this->assertCount(1, $data);
        $this->assertSame('Hello', $data[0]['content']);
    }

    public function test_list_conversations_and_mark_read(): void
    {
        $post = new WP_REST_Request('POST', '/artpulse/v1/messages');
        $post->set_param('recipient_id', $this->user2);
        $post->set_param('content', 'Hi there');
        $post->set_param('nonce', $this->nonce);
        $res = rest_get_server()->dispatch($post);
        $this->assertSame(200, $res->get_status());

        global $wpdb;
        $table = $wpdb->prefix . 'ap_messages';
        $row   = $wpdb->get_row("SELECT * FROM $table", ARRAY_A);
        $msg_id = (int) $row['id'];

        $convos = new WP_REST_Request('GET', '/artpulse/v1/conversations');
        $convos->set_param('nonce', $this->nonce);
        $res = rest_get_server()->dispatch($convos);
        $this->assertSame(200, $res->get_status());
        $this->assertSame([
            ['user_id' => $this->user2, 'unread' => 1]
        ], $res->get_data());

        wp_set_current_user($this->user2);
        $read = new WP_REST_Request('POST', '/artpulse/v1/message/read');
        $read->set_param('ids', [$msg_id]);
        $read->set_param('nonce', $this->nonce);
        $res = rest_get_server()->dispatch($read);
        $this->assertSame(200, $res->get_status());

        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $msg_id), ARRAY_A);
        $this->assertSame('1', $row['is_read']);
    }
}
