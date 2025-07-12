<?php
namespace ArtPulse\Admin;

// WordPress stubs for unit testing
function update_user_meta($uid, $key, $value){ \ArtPulse\Admin\Tests\WidgetManagerTest::$meta[$uid][$key] = $value; }
function get_user_meta($uid, $key, $single = false){ return \ArtPulse\Admin\Tests\WidgetManagerTest::$meta[$uid][$key] ?? ''; }
function delete_user_meta($uid, $key){ unset(\ArtPulse\Admin\Tests\WidgetManagerTest::$meta[$uid][$key]); }
function get_userdata($uid){ return \ArtPulse\Admin\Tests\WidgetManagerTest::$users[$uid] ?? null; }
function sanitize_key($key){ return preg_replace('/[^a-z0-9_]/i','', strtolower($key)); }

namespace ArtPulse\Admin\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\UserLayoutManager;
use ArtPulse\Core\DashboardWidgetRegistry;

class WidgetManagerTest extends TestCase
{
    public static array $meta = [];
    public static array $users = [];

    protected function setUp(): void
    {
        self::$meta = [];
        self::$users = [];
        DashboardWidgetRegistry::register('one','One','','', '__return_null');
        DashboardWidgetRegistry::register('two','Two','','', '__return_null');
    }

    public function test_save_user_layout_alias(): void
    {
        UserLayoutManager::save_user_layout(1, [['id' => 'two'], ['id' => 'one'], ['id' => 'two']]);
        $expected = [
            ['id' => 'two', 'visible' => true],
            ['id' => 'one', 'visible' => true],
        ];
        $this->assertSame($expected, self::$meta[1][UserLayoutManager::META_KEY]);
    }

    public function test_reset_user_layout_removes_meta(): void
    {
        self::$meta[1][UserLayoutManager::META_KEY] = [['id' => 'one']];
        self::$meta[1][UserLayoutManager::VIS_META_KEY] = ['one' => true];
        UserLayoutManager::reset_user_layout(1);
        $this->assertArrayNotHasKey(UserLayoutManager::META_KEY, self::$meta[1] ?? []);
        $this->assertArrayNotHasKey(UserLayoutManager::VIS_META_KEY, self::$meta[1] ?? []);
    }

    public function test_get_primary_role_falls_back_to_subscriber(): void
    {
        self::$users[5] = (object) ['roles' => []];
        $this->assertSame('subscriber', UserLayoutManager::get_primary_role(5));
    }
}
