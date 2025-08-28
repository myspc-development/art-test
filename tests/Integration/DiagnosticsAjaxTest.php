<?php
namespace ArtPulse\Integration\Tests;

use WP_Ajax_UnitTestCase;

class DiagnosticsAjaxTest extends WP_Ajax_UnitTestCase
{
    public function test_fails_without_nonce(): void
    {
        $user_id = self::factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($user_id);

        try {
            $this->_handleAjax('ap_ajax_test');
            $this->fail('Expected missing nonce failure');
        } catch (\WPAjaxDieStopException $e) {
            $this->assertSame('-1', $e->getMessage());
        }
    }

    public function test_fails_without_capability(): void
    {
        $user_id = self::factory()->user->create(['role' => 'subscriber']);
        wp_set_current_user($user_id);
        $_POST['nonce'] = wp_create_nonce('ap_diagnostics_test');

        try {
            $this->_handleAjax('ap_ajax_test');
        } catch (\WPAjaxDieStopException $e) {
            $resp = json_decode($this->_last_response, true);
            $this->assertFalse($resp['success']);
            $this->assertSame('Forbidden', $resp['data']['message']);
        }
    }

    public function test_succeeds_with_nonce_and_capability(): void
    {
        $user_id = self::factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($user_id);
        $_POST['nonce'] = wp_create_nonce('ap_diagnostics_test');

        $this->_handleAjax('ap_ajax_test');
        $resp = json_decode($this->_last_response, true);
        $this->assertTrue($resp['success']);
    }
}
