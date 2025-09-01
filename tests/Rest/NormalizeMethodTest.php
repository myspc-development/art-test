<?php
namespace {
        if ( ! function_exists( 'add_filter' ) ) {
                function add_filter( ...$args ) {}
        }
}

namespace ArtPulse\Rest\Tests {

require_once dirname( __DIR__, 2 ) . '/includes/rest-dedupe.php';

/**
 * @group REST
 */
class NormalizeMethodTest extends \PHPUnit\Framework\TestCase {
        public function test_normalizes_string_method(): void {
                $this->assertSame( 'GET', \ap__normalize_method( 'readable' ) );
        }

        public function test_normalizes_array_methods(): void {
                $this->assertSame( 'GET,POST', \ap__normalize_method( array( 'readable', 'post' ) ) );
        }
}

}
