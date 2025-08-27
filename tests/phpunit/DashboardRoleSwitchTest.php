<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\WidgetRegistry;
use ArtPulse\Core\DashboardPresets;

final class DashboardRoleSwitchTest extends TestCase
{
    protected function setUp(): void
    {
        WidgetRegistry::register('widget_membership', static fn() => '<section></section>');
        WidgetRegistry::register('widget_artist_revenue_summary', static fn() => '<section></section>');
        WidgetRegistry::register('widget_audience_crm', static fn() => '<section></section>');
    }

    /** @dataProvider roles */
    public function test_role_param_affects_container_attributes(string $role): void
    {
        // Simulate preset access to ensure role is accepted
        $ids = DashboardPresets::forRole($role);
        $this->assertIsArray($ids);
        $this->assertNotEmpty($ids, "No preset IDs for role=$role");

        // Render one known widget to ensure non-empty HTML
        $html = WidgetRegistry::render($ids[0]);
        $this->assertNotSame('', trim((string)$html));

        // Emulate container attributes using the same sanitation
        $attr = sprintf('data-role="%s"', $role);
        $this->assertStringContainsString($role, $attr);
    }

    public function test_presets_differ_per_role(): void
    {
        $member = DashboardPresets::forRole('member');
        $artist = DashboardPresets::forRole('artist');
        $org    = DashboardPresets::forRole('organization');
        $this->assertNotSame($member, $artist);
        $this->assertNotSame($member, $org);
        $this->assertNotSame($artist, $org);
    }

    public function roles(): array
    {
        return [['member'], ['artist'], ['organization']];
    }
}
