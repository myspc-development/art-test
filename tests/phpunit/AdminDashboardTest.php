<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;
use ArtPulse\Core\AdminDashboard;

/**

 * @group PHPUNIT

 */

final class AdminDashboardTest extends TestCase {
    private array $scripts = array();
    private array $styles = array();
    private array $localized = array();

    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();

        if ( ! defined( 'ARTPULSE_PLUGIN_FILE' ) ) {
            define( 'ARTPULSE_PLUGIN_FILE', __FILE__ );
        }

        Functions\when( 'plugin_dir_url' )->alias( fn( $f ) => 'https://example.test/p/' );
        Functions\when( 'admin_url' )->alias( fn( $p = '' ) => 'https://example.test/wp-admin/' . ltrim( $p, '/' ) );
        Functions\when( 'wp_create_nonce' )->alias( fn( $a ) => 'nonce' );

        Functions\when( 'wp_enqueue_script' )->alias(
            function ( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {
                $this->scripts[ $handle ] = array(
                    'handle'    => $handle,
                    'src'       => $src,
                    'deps'      => $deps,
                    'ver'       => $ver,
                    'in_footer' => $in_footer,
                );
            }
        );

        Functions\when( 'wp_enqueue_style' )->alias(
            function ( $handle, $src = '', $deps = array(), $ver = false, $media = 'all' ) {
                $this->styles[ $handle ] = array(
                    'handle' => $handle,
                    'src'    => $src,
                    'deps'   => $deps,
                    'ver'    => $ver,
                    'media'  => $media,
                );
            }
        );

        Functions\when( 'wp_localize_script' )->alias(
            function ( $handle, $name, $data ) {
                $this->localized[ $handle ] = $data;
            }
        );
    }

    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function test_enqueue_enqueues_dashboard_scripts(): void {
        AdminDashboard::enqueue( 'toplevel_page_artpulse-dashboard' );

        $sortable = $this->scripts['sortablejs'] ?? null;
        $role     = $this->scripts['role-dashboard'] ?? null;

        $this->assertNotNull( $sortable );
        $this->assertNotNull( $role );
        $this->assertContains( 'jquery', $role['deps'] );
        $this->assertContains( 'sortablejs', $role['deps'] );
        $this->assertSame( 'https://example.test/p/assets/js/role-dashboard.js', $role['src'] );

        $this->assertArrayHasKey( 'role-dashboard', $this->localized );
        $data = $this->localized['role-dashboard'];
        $this->assertSame( 'https://example.test/wp-admin/admin-ajax.php', $data['ajax_url'] );
        $this->assertSame( 'nonce', $data['nonce'] );

        $this->assertEmpty( $this->styles );
    }

    public function test_enqueue_ignores_other_pages(): void {
        AdminDashboard::enqueue( 'dashboard' );
        $this->assertEmpty( $this->scripts );
        $this->assertEmpty( $this->styles );
        $this->assertEmpty( $this->localized );
    }
}
