<?php
namespace ArtPulse\Support;

// Stub WordPress get_option function for testing
if (!function_exists(__NAMESPACE__ . '\\get_option')) {
    function get_option($name, $default = false) {
        return \ArtPulse\Support\Tests\OptionUtilsTest::$options[$name] ?? $default;
    }
}

namespace ArtPulse\Support\Tests;

use ArtPulse\Support\OptionUtils;
use PHPUnit\Framework\TestCase;

class OptionUtilsTest extends TestCase
{
    public static array $options = [];

    protected function setUp(): void
    {
        self::$options = [];
    }

    public function test_returns_arrays_directly(): void
    {
        self::$options['test'] = ['a' => 1];
        $this->assertSame(['a' => 1], OptionUtils::get_array_option('test', ['default']));
    }

    public function test_normalizes_traversable(): void
    {
        self::$options['test'] = new \ArrayIterator(['a' => 1, 'b' => 2]);
        $this->assertSame(['a' => 1, 'b' => 2], OptionUtils::get_array_option('test'));
    }

    public function test_decodes_json_string(): void
    {
        self::$options['test'] = '{"a":1}';
        $this->assertSame(['a' => 1], OptionUtils::get_array_option('test'));
    }

    public function test_returns_default_for_invalid_value(): void
    {
        self::$options['test'] = 123;
        $this->assertSame(['d'], OptionUtils::get_array_option('test', ['d']));
    }

    public function test_returns_default_when_option_missing(): void
    {
        $this->assertSame(['d'], OptionUtils::get_array_option('missing', ['d']));
    }

    public function test_returns_default_for_malformed_json(): void
    {
        self::$options['test'] = '{"a":1';
        $this->assertSame(['d'], OptionUtils::get_array_option('test', ['d']));
    }
}
