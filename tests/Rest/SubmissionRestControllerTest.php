<?php
namespace ArtPulse\Rest;

// --- Stubs for WordPress functions used in the controller ---
if (!function_exists(__NAMESPACE__ . '\current_user_can')) {
function current_user_can(string $cap) {
    return \ArtPulse\Rest\Tests\SubmissionStub::$can;
}
}

if (!function_exists(__NAMESPACE__ . '\wp_verify_nonce')) {
function wp_verify_nonce($nonce, $action) {
    return \ArtPulse\Rest\Tests\SubmissionStub::$nonce_valid && $nonce === 'good' && $action === 'wp_rest';
}
}

class WP_REST_Request {
    private array $params;
    private array $json;
    private array $headers;

    public function __construct(array $params = [], array $json = [], array $headers = []) {
        $this->params  = $params;
        $this->json    = $json;
        $this->headers = array_change_key_case($headers, CASE_LOWER);
    }

    public function get_param(string $key) {
        return $this->params[$key] ?? null;
    }

    public function get_json_params() {
        return $this->json;
    }

    public function get_header(string $key) {
        $key = strtolower($key);
        return $this->headers[$key] ?? '';
    }
}

namespace ArtPulse\Rest\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Rest\SubmissionRestController;

class SubmissionRestControllerTest extends TestCase
{
    public function test_meta_fields_include_names(): void
    {
        $ref = new \ReflectionClass(SubmissionRestController::class);
        $method = $ref->getMethod('get_meta_fields_for');
        $method->setAccessible(true);

        $artist = $method->invoke(null, 'artpulse_artist');
        $org    = $method->invoke(null, 'artpulse_org');

        $this->assertArrayHasKey('artist_name', $artist);
        $this->assertSame('artist_name', $artist['artist_name']);
        $this->assertArrayHasKey('ead_org_name', $org);
        $this->assertSame('ead_org_name', $org['ead_org_name']);
    }

    public function test_meta_fields_include_sale_and_event_fields(): void
    {
        $ref = new \ReflectionClass(SubmissionRestController::class);
        $method = $ref->getMethod('get_meta_fields_for');
        $method->setAccessible(true);

        $event   = $method->invoke(null, 'artpulse_event');
        $artwork = $method->invoke(null, 'artpulse_artwork');

        $this->assertSame('event_start_date', $event['event_start_date']);
        $this->assertSame('event_end_date', $event['event_end_date']);

        $this->assertSame('for_sale', $artwork['for_sale']);
        $this->assertSame('price', $artwork['price']);
        $this->assertSame('buy_link', $artwork['buy_link']);
    }

    public function test_endpoint_args_include_names(): void
    {
        $args = SubmissionRestController::get_endpoint_args();
        $this->assertArrayHasKey('artist_name', $args);
        $this->assertSame('string', $args['artist_name']['type']);
        $this->assertArrayHasKey('ead_org_name', $args);
        $this->assertSame('string', $args['ead_org_name']['type']);
        // Newly added fields
        $this->assertArrayHasKey('event_start_date', $args);
        $this->assertSame('string', $args['event_start_date']['type']);
        $this->assertArrayHasKey('event_end_date', $args);
        $this->assertSame('string', $args['event_end_date']['type']);
        $this->assertArrayHasKey('for_sale', $args);
        $this->assertSame('boolean', $args['for_sale']['type']);
        $this->assertArrayHasKey('price', $args);
        $this->assertSame('string', $args['price']['type']);
        $this->assertArrayHasKey('buy_link', $args);
        $this->assertSame('string', $args['buy_link']['type']);
        $this->assertArrayHasKey('event_rsvp_enabled', $args);
        $this->assertSame('boolean', $args['event_rsvp_enabled']['type']);
        $this->assertArrayHasKey('event_rsvp_limit', $args);
        $this->assertSame('integer', $args['event_rsvp_limit']['type']);
        $this->assertArrayHasKey('event_waitlist_enabled', $args);
        $this->assertSame('boolean', $args['event_waitlist_enabled']['type']);
    }

    public function test_check_permissions_valid_nonce_and_capability(): void
    {
        SubmissionStub::reset();
        $req = new \ArtPulse\Rest\WP_REST_Request([], [], ['X-WP-Nonce' => 'good']);
        $ref = new \ReflectionMethod(SubmissionRestController::class, 'check_permissions');
        $ref->setAccessible(true);
        $this->assertTrue($ref->invoke(null, $req));
    }

    public function test_check_permissions_fails_with_invalid_nonce(): void
    {
        SubmissionStub::reset();
        SubmissionStub::$nonce_valid = false;
        $req = new \ArtPulse\Rest\WP_REST_Request([], [], ['X-WP-Nonce' => 'bad']);
        $ref = new \ReflectionMethod(SubmissionRestController::class, 'check_permissions');
        $ref->setAccessible(true);
        $this->assertFalse($ref->invoke(null, $req));
    }

    public function test_check_permissions_fails_without_capability(): void
    {
        SubmissionStub::reset();
        SubmissionStub::$can = false;
        $req = new \ArtPulse\Rest\WP_REST_Request([], [], ['X-WP-Nonce' => 'good']);
        $ref = new \ReflectionMethod(SubmissionRestController::class, 'check_permissions');
        $ref->setAccessible(true);
        $this->assertFalse($ref->invoke(null, $req));
    }
}

class SubmissionStub
{
    public static bool $can = true;
    public static bool $nonce_valid = true;

    public static function reset(): void
    {
        self::$can = true;
        self::$nonce_valid = true;
    }
}
