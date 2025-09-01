<?php
namespace ArtPulse\Support;

// Stub WordPress get_option function for testing
if ( ! function_exists( __NAMESPACE__ . '\\get_option' ) ) {
	function get_option( $name, $default = false ) {
		return \ArtPulse\Support\Tests\OptionUtilsTest::$options[ $name ] ?? $default;
	}
}

namespace ArtPulse\Support\Tests;

use ArtPulse\Support\OptionUtils;
use PHPUnit\Framework\TestCase;

/**

 * @group support

 */

class OptionUtilsTest extends TestCase {

	public static array $options = array();

	protected function setUp(): void {
		self::$options = array();
	}

	public function test_returns_arrays_directly(): void {
		self::$options['test'] = array( 'a' => 1 );
		$this->assertSame( array( 'a' => 1 ), OptionUtils::get_array_option( 'test', array( 'default' ) ) );
	}

	public function test_normalizes_traversable(): void {
		self::$options['test'] = new \ArrayIterator(
			array(
				'a' => 1,
				'b' => 2,
			)
		);
		$this->assertSame(
			array(
				'a' => 1,
				'b' => 2,
			),
			OptionUtils::get_array_option( 'test' )
		);
	}

	public function test_decodes_json_string(): void {
		self::$options['test'] = '{"a":1}';
		$this->assertSame( array( 'a' => 1 ), OptionUtils::get_array_option( 'test' ) );
	}

	public function test_returns_default_for_invalid_value(): void {
		self::$options['test'] = 123;
		$this->assertSame( array( 'd' ), OptionUtils::get_array_option( 'test', array( 'd' ) ) );
	}

	public function test_returns_default_when_option_missing(): void {
		$this->assertSame( array( 'd' ), OptionUtils::get_array_option( 'missing', array( 'd' ) ) );
	}

	public function test_returns_default_for_malformed_json(): void {
		self::$options['test'] = '{"a":1';
		$this->assertSame( array( 'd' ), OptionUtils::get_array_option( 'test', array( 'd' ) ) );
	}
}
