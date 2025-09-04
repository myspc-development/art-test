<?php
namespace ArtPulse\Frontend;

class AjaxLoginTestStubs {
        public static bool $nonce_valid    = true;
        public static $signon_return       = null;
        public static bool $signon_called  = false;
}

function check_ajax_referer( $action, $name = false ) {
        if ( ! AjaxLoginTestStubs::$nonce_valid ) {
                wp_send_json( array( 'ok' => false, 'code' => 'INVALID_NONCE' ) );
        }
}

function wp_signon( $creds, $secure = false ) {
        AjaxLoginTestStubs::$signon_called = true;
        return AjaxLoginTestStubs::$signon_return;
}

function wp_send_json( $data ) {
        if ( $data['ok'] ?? false ) {
                StubState::$json = $data;
        } else {
                StubState::$json_error = $data;
        }
        throw new \RuntimeException( 'json' );
}

require_once __DIR__ . '/../TestHelpers/FrontendFunctionStubs.php';

namespace ArtPulse\Frontend\Tests;

use ArtPulse\Frontend\AjaxLoginTestStubs;
use ArtPulse\Frontend\LoginShortcode;
use ArtPulse\Frontend\StubState;
use WP_Error;

class LoginShortcodeTest extends \WP_UnitTestCase {
        public function set_up(): void {
                parent::set_up();
                StubState::reset();
                AjaxLoginTestStubs::$nonce_valid   = true;
                AjaxLoginTestStubs::$signon_return = null;
                AjaxLoginTestStubs::$signon_called = false;
                $_POST                              = array();
                $_SERVER['REMOTE_ADDR']             = '127.0.0.1';
        }

        public function test_maybe_redirect_uses_stubs(): void {
                StubState::$function_exists_map['wp_safe_redirect'] = true;
                StubState::$function_exists_map['wp_get_referer']   = true;

                $this->assertTrue( \ArtPulse\Frontend\function_exists( '\\wp_safe_redirect' ) );
                $this->assertTrue( \ArtPulse\Frontend\function_exists( 'ArtPulse\\Frontend\\wp_get_referer' ) );

                $ref    = new \ReflectionClass( LoginShortcode::class );
                $method = $ref->getMethod( 'maybe_redirect' );
                $method->setAccessible( true );

                try {
                        $method->invoke( null );
                        $this->fail( 'Expected redirect' );
                } catch ( \RuntimeException $e ) {
                        $this->assertSame( 'redirect', $e->getMessage() );
                }

                $this->assertSame( '/referer', StubState::$page );
        }

        public function test_ajax_login_csrf_failure(): void {
                AjaxLoginTestStubs::$nonce_valid = false;

                try {
                        LoginShortcode::ajax_login();
                        $this->fail( 'Expected json exception' );
                } catch ( \RuntimeException $e ) {
                        $this->assertSame( 'json', $e->getMessage() );
                }

                $this->assertSame( 'INVALID_NONCE', StubState::$json_error['code'] );
                $this->assertFalse( AjaxLoginTestStubs::$signon_called );
        }

        public function test_ajax_login_missing_fields_errors(): void {
                try {
                        LoginShortcode::ajax_login();
                } catch ( \RuntimeException $e ) {
                        $this->assertSame( 'json', $e->getMessage() );
                }

                $this->assertSame( 'INVALID_CREDENTIALS', StubState::$json_error['code'] );
                $this->assertFalse( AjaxLoginTestStubs::$signon_called );
        }

        public function test_ajax_login_rate_limiting(): void {
                $_SERVER['REMOTE_ADDR'] = '8.8.8.8';
                $_POST['username']      = 'user';
                $_POST['password']      = 'bad';
                $key                    = 'ap_login_fail_' . md5( '8.8.8.8|user' );
                delete_transient( $key );
                AjaxLoginTestStubs::$signon_return = new WP_Error( 'failed', 'Bad' );

                for ( $i = 0; $i < 5; $i++ ) {
                        AjaxLoginTestStubs::$signon_called = false;
                        StubState::$json_error             = null;
                        try {
                                LoginShortcode::ajax_login();
                        } catch ( \RuntimeException $e ) {
                                $this->assertSame( 'json', $e->getMessage() );
                        }
                        $this->assertSame( 'INVALID_CREDENTIALS', StubState::$json_error['code'] );
                        $this->assertTrue( AjaxLoginTestStubs::$signon_called );
                }

                AjaxLoginTestStubs::$signon_called = false;
                StubState::$json_error             = null;
                try {
                        LoginShortcode::ajax_login();
                } catch ( \RuntimeException $e ) {
                        $this->assertSame( 'json', $e->getMessage() );
                }
                $this->assertSame( 'TOO_MANY_ATTEMPTS', StubState::$json_error['code'] );
                $this->assertFalse( AjaxLoginTestStubs::$signon_called );

                delete_transient( $key );
        }

        /**
         * @dataProvider roles_provider
         */
        public function test_ajax_login_success_returns_dashboard( string $role, string $expected ): void {
                $_POST['username'] = 'user';
                $_POST['password'] = 'pass';
                AjaxLoginTestStubs::$signon_return = (object) array( 'ID' => 1, 'roles' => array( $role ) );

                try {
                        LoginShortcode::ajax_login();
                } catch ( \RuntimeException $e ) {
                        $this->assertSame( 'json', $e->getMessage() );
                }

                $this->assertTrue( StubState::$json['ok'] );
                $this->assertSame( $expected, StubState::$json['dashboardUrl'] );
                $this->assertTrue( AjaxLoginTestStubs::$signon_called );
        }

        public static function roles_provider(): array {
                return array(
                        array( 'member', \ArtPulse\Core\Plugin::get_user_dashboard_url() ),
                        array( 'artist', \ArtPulse\Core\Plugin::get_artist_dashboard_url() ),
                        array( 'organization', \ArtPulse\Core\Plugin::get_org_dashboard_url() ),
                );
        }
}
