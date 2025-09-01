<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ArtPulse\Frontend\DashboardRoleRewrite;
use Brain\Monkey;
use Brain\Monkey\Functions;
use function Patchwork\redefine;
use function Patchwork\restore;

/**

 * @group PHPUNIT

 */

final class DashboardRoleRewritePreviewTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function test_renders_template_without_preview(): void {
        Functions\when('get_query_var')->alias(fn($v) => $v === 'ap_dashboard_role' ? 1 : null);
        Functions\when('current_user_can')->alias(fn() => false);
        Functions\when('wp_verify_nonce')->alias(fn() => false);
        Functions\when('ap_render_dashboard')->alias(function (array $roles = array()) {
            echo json_encode($roles) . '<section data-role="member"></section>';
        });
        $exit = redefine('exit', function (): void {
            throw new \RuntimeException('exit');
        });

        $this->expectOutputString('[]<section data-role="member"></section>');
        try {
            DashboardRoleRewrite::maybe_render();
            $this->fail('Expected exit to be called');
        } catch (\RuntimeException $e) {
            $this->assertSame('exit', $e->getMessage());
        }
        restore($exit);
    }

    public function test_preview_parameter_renders_preview_role(): void {
        Functions\when('get_query_var')->alias(fn($v) => $v === 'ap_dashboard_role' ? 1 : null);
        Functions\when('current_user_can')->alias(fn() => true);
        Functions\when('wp_verify_nonce')->alias(fn() => true);
        Functions\when('sanitize_key')->alias(fn($k) => strtolower(preg_replace('/[^a-z0-9_]/', '', $k)));
        Functions\when('ap_render_dashboard')->alias(function (array $roles = array()) {
            echo json_encode($roles) . '<section data-role="' . ($roles[0] ?? '') . '"></section>';
        });
        $_GET['ap_preview_role'] = 'artist';
        $_GET['ap_preview_nonce'] = 'nonce';
        $exit = redefine('exit', function (): void {
            throw new \RuntimeException('exit');
        });

        $this->expectOutputString('["artist"]<section data-role="artist"></section>');
        try {
            DashboardRoleRewrite::maybe_render();
            $this->fail('Expected exit to be called');
        } catch (\RuntimeException $e) {
            $this->assertSame('exit', $e->getMessage());
        }
        restore($exit);
        unset($_GET['ap_preview_role'], $_GET['ap_preview_nonce']);
    }
}
