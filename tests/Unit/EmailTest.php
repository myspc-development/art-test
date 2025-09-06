<?php
namespace {
	if ( ! function_exists( 'antispambot' ) ) {
		function antispambot( $email ) {
			return str_replace( '@', '&#064;', $email );
		}
	}
}

namespace ArtPulse\Util\Tests {
	use PHPUnit\Framework\TestCase;

	require_once __DIR__ . '/../../src/Util/Email.php';

	/**
	 * @group UNIT
	 */
	class EmailTest extends TestCase {
		public function test_obfuscates_at_symbol(): void {
			$result = \ArtPulse\Util\ap_obfuscate_email( 'user@example.com' );
			$this->assertStringContainsString( '&#64;', $result );
			$this->assertStringNotContainsString( '@', $result );
		}
	}
}
