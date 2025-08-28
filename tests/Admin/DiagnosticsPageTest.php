<?php
namespace ArtPulse\Admin\Tests;

use WP_Ajax_UnitTestCase;

class DiagnosticsPageTest extends WP_Ajax_UnitTestCase
{
    public function set_up()
    {
        parent::set_up();
        // Load plugin to register admin page and AJAX handler.
        if (!defined('ARTPULSE_PLUGIN_FILE')) {
            define('ARTPULSE_PLUGIN_FILE', dirname(__DIR__, 2) . '/artpulse.php');
        }
        require_once ARTPULSE_PLUGIN_FILE;
        // Ensure plugin file is loaded only once.
    }

    public function test_registers_diagnostics_admin_page(): void
    {
        // Trigger the admin_menu hook to register menu pages.
        do_action('admin_menu');

        // Ensure the helper function is available.
        if (!function_exists('get_admin_page_parent')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $parent = get_admin_page_parent('ap-diagnostics');
        $this->assertSame('admin.php', $parent);
    }

    public function test_ajax_diagnostics_endpoint(): void
    {
        // Authenticate as an administrator.
        $user_id = self::factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($user_id);

        // Provide a valid nonce for check_ajax_referer.
        $_POST['nonce'] = wp_create_nonce('ap_diagnostics_test');

        // Execute the AJAX action.
        $this->_handleAjax('ap_ajax_test');

        $response = json_decode($this->_last_response, true);
        $this->assertTrue($response['success']);
        $this->assertSame(
            'AJAX is working, nonce is valid, and you are authenticated.',
            $response['data']['message'] ?? ''
        );
    }
}
