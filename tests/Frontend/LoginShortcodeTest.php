<?php
namespace ArtPulse\Frontend;

require_once __DIR__ . '/../TestHelpers/FrontendFunctionStubs.php';

namespace ArtPulse\Frontend\Tests;

use ArtPulse\Frontend\LoginShortcode;

class LoginShortcodeTest extends \WP_UnitTestCase {
        public function set_up(): void {
                parent::set_up();
                \ArtPulse\Frontend\StubState::reset();
        }

        public function test_maybe_redirect_uses_stubs(): void {
                \ArtPulse\Frontend\StubState::$function_exists_map['wp_safe_redirect'] = true;
                \ArtPulse\Frontend\StubState::$function_exists_map['wp_get_referer']    = true;

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

                $this->assertSame( '/referer', \ArtPulse\Frontend\StubState::$page );
        }
}
