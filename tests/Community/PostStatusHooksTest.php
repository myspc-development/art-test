<?php
namespace ArtPulse\Community\Tests;

use WP_UnitTestCase;

class PostStatusHooksTest extends WP_UnitTestCase
{
    private array $mails = [];
    private array $requests = [];

    public function set_up(): void
    {
        parent::set_up();
        add_filter('pre_wp_mail', [$this, 'capture_mail'], 10, 6);
        add_filter('pre_http_request', [$this, 'capture_request'], 10, 3);
    }

    public function tear_down(): void
    {
        remove_filter('pre_wp_mail', [$this, 'capture_mail'], 10);
        remove_filter('pre_http_request', [$this, 'capture_request'], 10);
        parent::tear_down();
    }

    public function capture_mail(): bool
    {
        $this->mails[] = func_get_args();
        return true;
    }

    public function capture_request($pre, $args, $url)
    {
        $this->requests[] = [$url, $args];
        return ['headers' => [], 'body' => '', 'response' => ['code' => 200]];
    }

    public function test_notify_sends_via_wp_mail_when_configured(): void
    {
        $uid = self::factory()->user->create(['user_email' => 'author@test.com']);
        $post = (object)[
            'ID' => 1,
            'post_author' => $uid,
            'post_title' => 'Post',
        ];
        update_option('artpulse_settings', [
            'email_method' => 'wp_mail',
            'email_from_name' => 'Admin',
            'email_from_address' => 'admin@test.com',
        ]);

        \ap_notify_author_on_rejection('rejected', 'pending', $post);
        $this->assertCount(1, $this->mails);
        $this->assertEmpty($this->requests);
    }

    public function test_notify_sends_via_sendgrid_when_configured(): void
    {
        $uid = self::factory()->user->create(['user_email' => 'author@test.com']);
        $post = (object)[
            'ID' => 2,
            'post_author' => $uid,
            'post_title' => 'Post',
        ];
        update_option('artpulse_settings', [
            'email_method' => 'sendgrid',
            'sendgrid_api_key' => 'key',
            'email_from_name' => 'Admin',
            'email_from_address' => 'admin@test.com',
        ]);

        \ap_notify_author_on_rejection('rejected', 'pending', $post);
        $this->assertCount(1, $this->requests);
        $this->assertEmpty($this->mails);
    }
}
