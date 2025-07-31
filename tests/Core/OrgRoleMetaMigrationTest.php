<?php
namespace {
    if (!function_exists('sanitize_key')) {
        function sanitize_key($key) { return preg_replace('/[^a-z0-9_]/i', '', strtolower($key)); }
    }
    function get_option($key, $default = false) { return \ArtPulse\Core\Tests\OrgRoleMetaMigrationTest::$options[$key] ?? $default; }
    function update_option($key, $value) { \ArtPulse\Core\Tests\OrgRoleMetaMigrationTest::$options[$key] = $value; }
    function get_users($args = []) { return \ArtPulse\Core\Tests\OrgRoleMetaMigrationTest::$users; }
    function get_user_meta($uid, $key, $single = false) { return \ArtPulse\Core\Tests\OrgRoleMetaMigrationTest::$meta[$uid][$key] ?? false; }
    function delete_user_meta($uid, $key) { unset(\ArtPulse\Core\Tests\OrgRoleMetaMigrationTest::$meta[$uid][$key]); }
    function absint($n) { return (int)$n; }
}

namespace ArtPulse\Core\Tests {

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\OrgRoleMetaMigration;

class OrgRoleMetaMigrationTest extends TestCase
{
    public static array $options = [];
    public static array $users = [];
    public static array $meta = [];
    private $old_wpdb;

    protected function setUp(): void
    {
        global $wpdb;
        self::$options = [];
        self::$users = [];
        self::$meta = [];
        $this->old_wpdb = $wpdb;
        $wpdb = new class {
            public $prefix = 'wp_';
            public array $rows = [];
            public function prepare($query, ...$args) { return vsprintf($query, $args); }
            public function get_charset_collate() { return ''; }
            public function delete($table, $where) {
                $this->rows = array_filter($this->rows, function ($row) use ($where) {
                    foreach ($where as $k => $v) {
                        if ($row[$k] !== $v) { return true; }
                    }
                    return false;
                });
            }
            public function insert($table, $data) { $this->rows[] = $data; }
        };
    }

    protected function tearDown(): void
    {
        global $wpdb;
        $wpdb = $this->old_wpdb;
    }

    public function test_migrates_single_role_and_cleans_meta(): void
    {
        self::$users = [1];
        self::$meta[1]['ap_organization_id'] = 10;
        self::$meta[1]['ap_org_role'] = 'admin';

        OrgRoleMetaMigration::maybe_migrate();

        global $wpdb;
        $this->assertSame([['user_id' => 1, 'org_id' => 10, 'role' => 'admin', 'status' => 'active']], $wpdb->rows);
        $this->assertArrayNotHasKey('ap_org_role', self::$meta[1]);
        $this->assertSame(1, self::$options['ap_org_roles_table_migrated']);
    }

    public function test_migrates_roles_array_and_string(): void
    {
        self::$users = [2,3];
        self::$meta[2]['ap_organization_id'] = 20;
        self::$meta[2]['ap_org_roles'] = ['editor','curator'];
        self::$meta[3]['ap_organization_id'] = 30;
        self::$meta[3]['ap_org_roles'] = 'promoter';

        OrgRoleMetaMigration::maybe_migrate();

        global $wpdb;
        $rows = $wpdb->rows;
        $this->assertContains(['user_id'=>2,'org_id'=>20,'role'=>'editor','status'=>'active'], $rows);
        $this->assertContains(['user_id'=>2,'org_id'=>20,'role'=>'curator','status'=>'active'], $rows);
        $this->assertContains(['user_id'=>3,'org_id'=>30,'role'=>'promoter','status'=>'active'], $rows);
        $this->assertArrayNotHasKey('ap_org_roles', self::$meta[2]);
        $this->assertArrayNotHasKey('ap_org_roles', self::$meta[3]);
    }
}

}
