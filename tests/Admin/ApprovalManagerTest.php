<?php
namespace ArtPulse\Admin;

// --- WordPress function stubs ---
if (!function_exists(__NAMESPACE__ . '\\current_user_can')) {
    function current_user_can($cap) { return \ArtPulse\Admin\Tests\ApprovalManagerTest::$can; }
}
if (!function_exists(__NAMESPACE__ . '\\wp_die')) {
    function wp_die($msg = '') { \ArtPulse\Admin\Tests\ApprovalManagerTest::$died = $msg ?: true; }
}
if (!function_exists(__NAMESPACE__ . '\\wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action) { return true; }
}
if (!function_exists(__NAMESPACE__ . '\\get_post')) {
    function get_post($post_id) { return \ArtPulse\Admin\Tests\ApprovalManagerTest::$post; }
}
if (!function_exists(__NAMESPACE__ . '\\wp_update_post')) {
    function wp_update_post($arr) { \ArtPulse\Admin\Tests\ApprovalManagerTest::$updated = $arr; }
}
if (!function_exists(__NAMESPACE__ . '\\admin_url')) {
    function admin_url($path = '') { return $path; }
}
if (!function_exists(__NAMESPACE__ . '\\wp_safe_redirect')) {
    function wp_safe_redirect($url) { \ArtPulse\Admin\Tests\ApprovalManagerTest::$redirect = $url; throw new \Exception('redirect'); }
}
if (!function_exists(__NAMESPACE__ . '\\update_user_meta')) {
    function update_user_meta($user_id, $key, $value) { \ArtPulse\Admin\Tests\ApprovalManagerTest::$meta[$user_id][$key] = $value; }
}
if (!function_exists(__NAMESPACE__ . '\\delete_user_meta')) {
    function delete_user_meta($user_id, $key) { \ArtPulse\Admin\Tests\ApprovalManagerTest::$deleted[$user_id][] = $key; }
}

namespace ArtPulse\Admin\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\ApprovalManager;

class ApprovalManagerTest extends TestCase
{
    public static bool $can = true;
    public static $post;
    public static array $updated = [];
    public static string $redirect = '';
    public static array $meta = [];
    public static array $deleted = [];
    public static $died = null;

    protected function setUp(): void
    {
        self::$can = true;
        self::$post = (object)[
            'ID'          => 7,
            'post_type'   => 'artpulse_org',
            'post_author' => 4,
        ];
        self::$updated = [];
        self::$redirect = '';
        self::$meta = [];
        self::$deleted = [];
        self::$died = null;
        $_POST['post_id'] = 7;
        $_POST['nonce']   = 'nonce';
    }

    protected function tearDown(): void
    {
        $_POST = [];
        self::$updated = [];
        self::$redirect = '';
        self::$meta = [];
        self::$deleted = [];
        self::$died = null;
        parent::tearDown();
    }

    public function test_handle_approval_sets_user_meta_for_org(): void
    {
        try {
            ApprovalManager::handleApproval();
        } catch (\Exception $e) {
            $this->assertSame('redirect', $e->getMessage());
        }

        $this->assertSame(['ID' => 7, 'post_status' => 'publish'], self::$updated);
        $this->assertSame(7, self::$meta[4]['ap_organization_id'] ?? null);
        $this->assertContains('ap_pending_organization_id', self::$deleted[4] ?? []);
    }
}

