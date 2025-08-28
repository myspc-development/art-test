<?php
namespace ArtPulse\Integration\Tests;

use WP_Ajax_UnitTestCase;

class SaveUserLayoutAjaxTest extends WP_Ajax_UnitTestCase
{
    public function test_fails_without_nonce(): void
    {
        $user_id = self::factory()->user->create(['role' => 'subscriber']);
        wp_set_current_user($user_id);

        try {
            $this->_handleAjax('ap_save_user_layout');
            $this->fail('Expected failure for missing nonce');
        } catch (\WPAjaxDieStopException $e) {
            $this->assertSame('-1', $e->getMessage());
        }
    }

    public function test_fails_without_capability(): void
    {
        $user_id = self::factory()->user->create(['role' => 'subscriber']);
        $user = get_user_by('ID', $user_id);
        $user->remove_cap('read');
        wp_set_current_user($user_id);

        $_POST['nonce'] = wp_create_nonce('ap_save_user_layout');

        try {
            $this->_handleAjax('ap_save_user_layout');
        } catch (\WPAjaxDieStopException $e) {
            $resp = json_decode($this->_last_response, true);
            $this->assertFalse($resp['success']);
            $this->assertSame('Forbidden', $resp['data']['message']);
        }
    }

    public function test_succeeds_with_nonce_and_capability(): void
    {
        $user_id = self::factory()->user->create(['role' => 'subscriber']);
        wp_set_current_user($user_id);

        $_POST['nonce']  = wp_create_nonce('ap_save_user_layout');
        $_POST['layout'] = wp_json_encode([['id' => 'widget', 'visible' => true]]);

        $this->_handleAjax('ap_save_user_layout');
        $resp = json_decode($this->_last_response, true);
        $this->assertTrue($resp['success']);
    }
}
