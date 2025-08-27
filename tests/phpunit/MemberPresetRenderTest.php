<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\WidgetRegistry;
use ArtPulse\Core\DashboardPresets;

final class MemberPresetRenderTest extends TestCase
{
    protected function setUp(): void
    {
        WidgetRegistry::register('widget_my_follows', static fn(): string => '<section></section>');
    }
    public function test_member_preset_contains_my_follows_section(): void
    {
        $ids = $this->memberPresetIds();
        $this->assertNotEmpty($ids, 'Could not resolve member preset IDs');
        $this->assertContains('widget_my_follows', $ids, 'Preset should include widget_my_follows');

        $html = WidgetRegistry::render('widget_my_follows');
        $this->assertIsString($html);
        $this->assertNotSame('', trim($html), 'Rendered markup should not be empty');
        $this->assertStringContainsString('<section', $html, 'widget_my_follows should render a <section>');
    }

    /** @return array<int,string> */
    private function memberPresetIds(): array
    {
        $cls = DashboardPresets::class;

        // Try common static accessors if present
        foreach (['get', 'idsForRole', 'ids_for', 'forRole'] as $m) {
            if (method_exists($cls, $m)) {
                $out = $cls::$m('member');
                if (is_array($out)) return $out;
                if (is_array($out['member'] ?? null)) return $out['member'];
            }
        }

        // Fallback to constants or static properties
        $ref = new \ReflectionClass($cls);

        if ($ref->hasConstant('PRESETS')) {
            $presets = $ref->getConstant('PRESETS');
            if (is_array($presets['member'] ?? null)) return $presets['member'];
        }

        if ($ref->hasProperty('presets')) {
            $prop = $ref->getProperty('presets');
            $prop->setAccessible(true);
            $presets = $prop->getValue();
            if (is_array($presets['member'] ?? null)) return $presets['member'];
        }

        // Ultimate fallback: read JSON preset file if present
        $pluginDir = dirname(__DIR__, 2);
        $jsonPath  = $pluginDir . '/data/presets/member-discovery.json';
        if (is_file($jsonPath)) {
            $data = json_decode((string) file_get_contents($jsonPath), true);
            if (is_array($data)) {
                // accept either full objects or string IDs
                $ids = array_map(
                    static fn($item) => is_array($item) ? ($item['id'] ?? '') : (string) $item,
                    $data
                );
                return array_values(array_filter($ids));
            }
        }

        return [];
    }
}
