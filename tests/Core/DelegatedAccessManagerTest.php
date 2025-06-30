<?php
namespace ArtPulse\Core\Tests;

use WP_UnitTestCase;
use ArtPulse\Core\DelegatedAccessManager;

class DelegatedAccessManagerTest extends WP_UnitTestCase
{
    public function set_up(): void
    {
        parent::set_up();
        DelegatedAccessManager::install_table();
    }

    public function test_accept_and_expire(): void
    {
        global $wpdb;
        $future = date('Y-m-d', strtotime('+1 day'));
        $token = DelegatedAccessManager::invite(5, 'test@example.com', ['viewer'], $future);
        DelegatedAccessManager::accept_invitation($token, 2);
        $table = $wpdb->prefix . 'ap_delegated_access';
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE invitation_token = %s", $token));
        $this->assertSame('active', $row->status);
        $this->assertSame('2', $row->user_id);
        $wpdb->update($table, ['expiry_date' => date('Y-m-d', strtotime('-1 day'))], ['id' => $row->id]);
        DelegatedAccessManager::expire_access();
        $status = $wpdb->get_var($wpdb->prepare("SELECT status FROM $table WHERE id = %d", $row->id));
        $this->assertSame('expired', $status);
    }
}
