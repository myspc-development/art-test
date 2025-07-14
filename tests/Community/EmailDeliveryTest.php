<?php
namespace ArtPulse\Community\Tests;

use ArtPulse\Community\NotificationManager;
use WP_UnitTestCase;

class EmailDeliveryTest extends WP_UnitTestCase
{
    private int $user_id;
    private array $mails = [];
    private array $requests = [];
    private array $from = [];
    private array $names = [];

    public function set_up(): void
    {
        parent::set_up();
        NotificationManager::install_notifications_table();
        $this->user_id = self::factory()->user->create([
            'user_email'   => 'user@test.com',
            'display_name' => 'User',
        ]);
        add_filter('pre_wp_mail', [$this, 'capture_mail'], 10, 6);
        add_filter('pre_http_request', [$this, 'capture_request'], 10, 3);
        add_filter('wp_mail_from', [$this, 'capture_from'], 20);
        add_filter('wp_mail_from_name', [$this, 'capture_name'], 20);
    }

    public function tear_down(): void
    {
        remove_filter('pre_wp_mail', [$this, 'capture_mail'], 10);
        remove_filter('pre_http_request', [$this, 'capture_request'], 10);
        remove_filter('wp_mail_from', [$this, 'capture_from'], 20);
        remove_filter('wp_mail_from_name', [$this, 'capture_name'], 20);
        parent::tear_down();
    }

    public function capture_mail(): bool
    {
        $args = func_get_args();
        $this->mails[] = $args;
        return true;
    }

    public function capture_request($pre, $args, $url)
    {
        $this->requests[] = [$url, $args];
        return ['headers' => [], 'body' => '', 'response' => ['code' => 200]];
    }

    public function capture_from($from)
    {
        $this->from[] = $from;
        return $from;
    }

    public function capture_name($name)
    {
        $this->names[] = $name;
        return $name;
    }

    public function test_wp_mail_method_used(): void
    {
        update_option('artpulse_settings', [
            'email_method' => 'wp_mail',
            'email_from_name' => 'Admin',
            'email_from_address' => 'admin@test.com',
        ]);
        NotificationManager::add($this->user_id, 'comment', null, null, 'Hi');
        $this->assertCount(1, $this->mails);
        $this->assertSame('admin@test.com', end($this->from));
        $this->assertSame('Admin', end($this->names));
        $this->assertEmpty($this->requests);
    }

    public function test_mailgun_method_sends_request(): void
    {
        update_option('artpulse_settings', [
            'email_method' => 'mailgun',
            'mailgun_api_key' => 'key',
            'mailgun_domain' => 'mg.test.com',
            'email_from_name' => 'Admin',
            'email_from_address' => 'admin@test.com',
        ]);
        NotificationManager::add($this->user_id, 'comment', null, null, 'Hi');
        $this->assertCount(1, $this->requests);
        $this->assertStringContainsString('mg.test.com/messages', $this->requests[0][0]);
    }

    public function test_sendgrid_method_sends_request(): void
    {
        update_option('artpulse_settings', [
            'email_method' => 'sendgrid',
            'sendgrid_api_key' => 'key',
            'email_from_name' => 'Admin',
            'email_from_address' => 'admin@test.com',
        ]);
        NotificationManager::add($this->user_id, 'comment', null, null, 'Hi');
        $this->assertCount(1, $this->requests);
        $this->assertStringContainsString('sendgrid', $this->requests[0][0]);
    }
}
