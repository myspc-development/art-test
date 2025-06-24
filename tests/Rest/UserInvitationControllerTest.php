<?php
namespace ArtPulse\Rest;

// --- Stubs for WordPress functions used in the controller ---
function current_user_can(string $cap) {
    return \ArtPulse\Rest\Tests\Stub::$can;
}
function get_current_user_id() {
    return \ArtPulse\Rest\Tests\Stub::$current_user_id;
}
function get_user_meta(int $user_id, string $key, bool $single = false) {
    return \ArtPulse\Rest\Tests\Stub::$user_meta[$user_id][$key] ?? '';
}
function wp_mail(string $to, string $subject, string $message) {
    \ArtPulse\Rest\Tests\Stub::$sent_emails[] = [$to, $subject, $message];
    return true;
}
function update_user_meta(int $user_id, string $key, $value) {
    \ArtPulse\Rest\Tests\Stub::$user_meta[$user_id][$key] = $value;
    return true;
}
function wp_delete_user(int $user_id) {
    \ArtPulse\Rest\Tests\Stub::$deleted_users[] = $user_id;
    return true;
}
function rest_ensure_response($data) {
    return $data;
}
function sanitize_email($email) {
    return filter_var($email, FILTER_SANITIZE_EMAIL);
}
function is_email($email) {
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}
function sanitize_text_field($value) {
    return is_string($value) ? trim($value) : $value;
}
function sanitize_key($key) {
    return preg_replace('/[^a-z0-9_]/i', '', $key);
}
function absint($num) {
    return abs(intval($num));
}

// Minimal WP_Error stub
class WP_Error {
    public string $code;
    public string $message;
    public array $data;
    public function __construct(string $code = '', string $message = '', array $data = []) {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }
}

// Minimal WP_REST_Request stub
class WP_REST_Request {
    private array $params;
    private array $json;
    public function __construct(array $params = [], array $json = []) {
        $this->params = $params;
        $this->json = $json;
    }
    public function get_param(string $key) {
        return $this->params[$key] ?? null;
    }
    public function get_json_params() {
        return $this->json;
    }
}

namespace ArtPulse\Rest\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Rest\UserInvitationController;
use ArtPulse\Rest\WP_REST_Request;
use ArtPulse\Rest\WP_Error;

class Stub {
    public static bool $can = true;
    public static int $current_user_id = 1;
    public static array $user_meta = [];
    public static array $sent_emails = [];
    public static array $deleted_users = [];

    public static function reset(): void {
        self::$can = true;
        self::$current_user_id = 1;
        self::$user_meta = [];
        self::$sent_emails = [];
        self::$deleted_users = [];
    }
}

class UserInvitationControllerTest extends TestCase
{
    protected function setUp(): void
    {
        Stub::reset();
    }

    public function test_invite_success(): void
    {
        Stub::$user_meta[1]['ap_organization_id'] = 5;
        $req = new WP_REST_Request(['id' => 5], ['emails' => ['a@test.com', 'b@test.com']]);
        $res = UserInvitationController::invite($req);
        $this->assertSame(['invited' => ['a@test.com', 'b@test.com']], $res);
        $this->assertCount(2, Stub::$sent_emails);
    }

    public function test_invite_permission_failure(): void
    {
        Stub::$user_meta[1]['ap_organization_id'] = 3; // user not admin of org 5
        $req = new WP_REST_Request(['id' => 5], ['emails' => ['a@test.com']]);
        $this->assertFalse(UserInvitationController::check_permissions($req));
    }

    public function test_invite_invalid_email(): void
    {
        Stub::$user_meta[1]['ap_organization_id'] = 5;
        $req = new WP_REST_Request(['id' => 5], ['emails' => ['bad-email']]);
        $res = UserInvitationController::invite($req);
        $this->assertInstanceOf(WP_Error::class, $res);
    }

    public function test_batch_suspend_success(): void
    {
        Stub::$user_meta[1]['ap_organization_id'] = 5;
        $req = new WP_REST_Request(['id' => 5], ['action' => 'suspend', 'user_ids' => [7]]);
        $res = UserInvitationController::batch_users($req);
        $this->assertSame(['action' => 'suspend', 'processed' => [7]], $res);
        $this->assertSame(1, Stub::$user_meta[7]['ap_suspended']);
    }

    public function test_batch_invalid_action(): void
    {
        Stub::$user_meta[1]['ap_organization_id'] = 5;
        $req = new WP_REST_Request(['id' => 5], ['action' => 'foo', 'user_ids' => [2]]);
        $res = UserInvitationController::batch_users($req);
        $this->assertInstanceOf(WP_Error::class, $res);
    }
}
