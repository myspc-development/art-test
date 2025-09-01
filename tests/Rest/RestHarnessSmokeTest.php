<?php
declare(strict_types=1);

namespace ArtPulse\Rest\Tests;

use WP_REST_Server;

/**
 * Basic smoke tests to verify the WP REST testing harness is wired up.
 *
 * @group restapi
 */
class RestHarnessSmokeTest extends \WP_UnitTestCase
{
    public function test_wordpress_bootstrap(): void
    {
        $this->assertTrue(defined('ABSPATH'), 'ABSPATH should be defined');
        $this->assertFileExists(ABSPATH . 'wp-settings.php');
    }

    public function test_rest_server_and_url(): void
    {
        $server = rest_get_server();
        $this->assertInstanceOf(WP_REST_Server::class, $server);
        $url = wp_rest_url();
        $this->assertIsString($url);
        $this->assertNotEmpty($url);
    }

    public function test_user_not_logged_in_by_default(): void
    {
        $this->assertFalse(is_user_logged_in(), 'No user should be logged in by default');

        $this->loginAsAdmin();
        $this->assertTrue(is_user_logged_in(), 'Helper should authenticate a user');
    }

    /**
     * Helper to create and authenticate a user.
     */
    protected function loginAsAdmin(): void
    {
        $userId = self::factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($userId);
    }
}
