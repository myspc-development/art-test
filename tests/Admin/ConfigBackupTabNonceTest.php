<?php
namespace ArtPulse\Admin\Tests;

use ArtPulse\Admin\ConfigBackupTab;

class ConfigBackupTabNonceTest extends \WP_UnitTestCase
{
    private int $admin;

    public function set_up()
    {
        parent::set_up();
        $this->admin = self::factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($this->admin);
        $_REQUEST = [];
    }

    public function tear_down()
    {
        $_REQUEST = [];
        parent::tear_down();
    }

    public function test_handle_export_missing_nonce_fails(): void
    {
        $this->expectException(\WPDieException::class);
        ConfigBackupTab::handle_export();
    }

    public function test_handle_export_invalid_nonce_fails(): void
    {
        $_REQUEST['_wpnonce'] = wp_create_nonce('invalid');
        $this->expectException(\WPDieException::class);
        ConfigBackupTab::handle_export();
    }
}
