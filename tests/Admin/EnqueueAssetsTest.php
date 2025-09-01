<?php
namespace ArtPulse\Admin\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\EnqueueAssets;
use Brain\Monkey;
use Brain\Monkey\Functions;

/**

 * @group admin

 */

class EnqueueAssetsTest extends TestCase {
    public static array $localized      = array();
    public static array $user_meta      = array();
    public static array $posts          = array();
    public static array $terms          = array();
    public static array $scripts        = array();
    public static array $localize_calls = array();
    public static array $options        = array();
    public static $current_screen       = null;

    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();
        Functions\when('admin_url')->alias(fn($path = '') => $path);
        Functions\when('wp_enqueue_script')->alias(fn(...$args) => self::$scripts[] = $args);
        Functions\when('wp_enqueue_style')->justReturn();
        Functions\when('get_current_screen')->alias(fn() => self::$current_screen);
        Functions\when('plugin_dir_url')->alias(fn($file) => '/');
        Functions\when('file_exists')->alias(fn($path) => true);
        Functions\when('rest_url')->alias(fn($path = '') => $path);
        Functions\when('wp_create_nonce')->alias(fn($action = '') => 'nonce');
        Functions\when('is_user_logged_in')->alias(fn() => true);
        Functions\when('get_current_user_id')->alias(fn() => 1);
        Functions\when('get_user_meta')->alias(fn($uid, $key, $single = false) => self::$user_meta[$uid][$key] ?? '');
        Functions\when('ArtPulse\\Admin\\get_posts')->alias(fn($args = array()) => self::$posts);
        Functions\when('ArtPulse\\Admin\\get_the_terms')->alias(fn($post_id, $tax) => self::$terms[$post_id] ?? false);
        Functions\when('wp_localize_script')->alias(function($handle, $name, $data) {
            self::$localized        = $data;
            self::$localize_calls[] = array($handle, $name, $data);
        });
        Functions\when('get_option')->alias(fn($key, $default = array()) => self::$options[$key] ?? $default);
        Functions\when('update_option')->alias(fn($key, $value) => self::$options[$key] = $value);
        Functions\when('wp_roles')->alias(fn() => (object) array('roles' => array('administrator' => array(), 'subscriber' => array())));
        Functions\when('wp_script_is')->alias(fn($h, $list) => false);
        Functions\when('ArtPulse\\Core\\get_posts')->alias(fn($args = array()) => array((object) array('ID' => 1)));
        Functions\when('ArtPulse\\Core\\get_permalink')->alias(fn($id) => '/submit');
        Functions\when('ArtPulse\\Core\\home_url')->alias(fn($path = '/') => '/');

        self::$localized      = array();
        self::$user_meta      = array();
        self::$posts          = array();
        self::$terms          = array();
        self::$scripts        = array();
        self::$localize_calls = array();
        self::$options        = array( 'artpulse_settings' => array( 'disable_styles' => true ) );
        self::$current_screen = null;
    }

    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }

    public static function add_post( int $id, string $title, string $stage_slug, string $stage_name ): void {
        self::$posts[]      = (object) array(
            'ID'         => $id,
            'post_title' => $title,
        );
        self::$terms[ $id ] = array(
            (object) array(
                'slug' => $stage_slug,
                'name' => $stage_name,
            ),
        );
    }

    public function test_localizes_stage_groups(): void {
        self::$user_meta[1]['ap_organization_id'] = 5;
        self::add_post( 1, 'Art One', 'stage-1', 'Stage 1' );
        EnqueueAssets::enqueue_frontend();
        $this->assertArrayHasKey( 'projectStages', self::$localized );
        $this->assertSame( 'Stage 1', self::$localized['projectStages'][0]['name'] );
    }

    public function test_block_editor_scripts_enqueue_when_screen_available(): void {
        self::$current_screen = (object) array(
            'id'              => 'edit-artpulse_event',
            'is_block_editor' => true,
        );
        EnqueueAssets::enqueue_block_editor_assets();
        $handles = array_map( fn( $a ) => $a[0] ?? '', self::$scripts );
        $this->assertContains( 'ap-event-gallery', $handles );
    }

    public function test_exits_gracefully_without_screen(): void {
        self::$current_screen = null;
        EnqueueAssets::enqueue_block_editor_assets();
        $this->assertSame( array(), self::$scripts );
    }
}
