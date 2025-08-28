<?php
namespace ArtPulse\Integration\Tests;

use WP_Ajax_UnitTestCase;

class ReleaseNotesAjaxTest extends WP_Ajax_UnitTestCase
{
    public function test_dismiss_fails_without_nonce(): void
    {
        $user_id = self::factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($user_id);

        try {
            $this->_handleAjax('ap_dismiss_release_notes');
            $this->fail('Expected missing nonce failure');
        } catch (\WPAjaxDieStopException $e) {
            $this->assertSame('-1', $e->getMessage());
        }
    }

    public function test_dismiss_fails_without_capability(): void
    {
        $user_id = self::factory()->user->create(['role' => 'subscriber']);
        wp_set_current_user($user_id);
        $_POST['nonce'] = wp_create_nonce('ap_release_notes');

        try {
            $this->_handleAjax('ap_dismiss_release_notes');
        } catch (\WPAjaxDieStopException $e) {
            $resp = json_decode($this->_last_response, true);
            $this->assertFalse($resp['success']);
            $this->assertSame('Forbidden', $resp['data']['message']);
        }
    }

    public function test_dismiss_succeeds_with_nonce_and_capability(): void
    {
        $user_id = self::factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($user_id);
        $_POST['nonce'] = wp_create_nonce('ap_release_notes');

        $this->_handleAjax('ap_dismiss_release_notes');
        $resp = json_decode($this->_last_response, true);
        $this->assertTrue($resp['success']);
    }
}
