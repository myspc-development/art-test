<?php
namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\LayoutUtils;
class LayoutUtilsTest extends TestCase
{
    public function test_normalize_layout_filters_invalid_and_duplicates(): void
    {
        $layout = [
            ['id' => 'alpha'],
            ['id' => 'beta', 'visible' => false],
            ['id' => 'alpha'],
            'gamma'
        ];
        $valid = ['alpha', 'beta'];
        $expected = [
            ['id' => 'alpha', 'visible' => true],
            ['id' => 'beta', 'visible' => false],
        ];
        $logs = [];
        $this->assertSame($expected, LayoutUtils::normalize_layout($layout, $valid, $logs));
        $this->assertSame(['gamma'], $logs);
    }

    public function test_merge_styles_sanitizes_keys_and_values(): void
    {
        $base = ['background_color' => '#fff'];
        $updates = ['background_color' => '#000', 'padding' => 'M'];
        $merged = LayoutUtils::merge_styles($base, $updates);
        $this->assertSame(['background_color' => '#000', 'padding' => 'M'], $merged);
    }
}
