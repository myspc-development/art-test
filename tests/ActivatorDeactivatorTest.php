<?php
use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;

class ActivatorDeactivatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();

        if (!defined('ABSPATH')) {
            $tmp = sys_get_temp_dir() . '/wp/';
            define('ABSPATH', $tmp);
            if (!is_dir($tmp . 'wp-admin/includes')) {
                mkdir($tmp . 'wp-admin/includes', 0777, true);
            }
            if (!file_exists($tmp . 'wp-admin/includes/upgrade.php')) {
                file_put_contents($tmp . 'wp-admin/includes/upgrade.php', '<?php');
            }
        }

        Functions\when('__')->alias(fn($t) => $t);
        Functions\when('add_filter')->justReturn(true);
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function test_activate_creates_tables_roles_and_pages(): void
    {
        global $wpdb;
        $wpdb = new class {
            public $prefix = 'wp_';
            public function get_charset_collate() { return 'utf8mb4'; }
        };

        $capturedSql = '';
        Functions\when('dbDelta')->alias(function($sql) use (&$capturedSql) {
            $capturedSql = $sql;
        });

        $addedRoles = [];
        Functions\when('get_role')->alias(fn() => null);
        Functions\when('add_role')->alias(function($slug, $label, $caps) use (&$addedRoles) {
            $addedRoles[$slug] = $caps;
            return true;
        });

        $insertedPages = [];
        Functions\when('get_page_by_path')->justReturn(false);
        Functions\when('wp_insert_post')->alias(function($args) use (&$insertedPages) {
            $insertedPages[] = $args['post_name'];
            return rand(1, 100);
        });
        Functions\when('update_post_meta')->justReturn(true);
        Functions\when('flush_rewrite_rules')->justReturn(true);

        require_once __DIR__ . '/../includes/class-activator.php';

        ArtPulse_Activator::activate();

        $this->assertStringContainsString('CREATE TABLE', $capturedSql);
        $this->assertStringContainsString('wp_ap_placeholder', $capturedSql);

        $this->assertArrayHasKey('member', $addedRoles);
        $this->assertArrayHasKey('artist', $addedRoles);
        $this->assertArrayHasKey('organization', $addedRoles);

        foreach (['login', 'dashboard', 'events', 'artists', 'calendar'] as $slug) {
            $this->assertContains($slug, $insertedPages);
        }
    }

    public function test_deactivate_flushes_rewrite_rules(): void
    {
        Functions\expect('flush_rewrite_rules')->once()->andReturn(true);
        require_once __DIR__ . '/../includes/class-deactivator.php';
        ArtPulse_Deactivator::deactivate();
        $this->addToAssertionCount(1);
    }
}
