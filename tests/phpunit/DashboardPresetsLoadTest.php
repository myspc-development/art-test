<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardPresets;

final class DashboardPresetsLoadTest extends TestCase {
    public function test_member_has_widgets(): void {
        $ids = DashboardPresets::forRole('member');
        $this->assertIsArray($ids);
        $this->assertNotEmpty($ids, 'member preset should not be empty');
        $this->assertContains('widget_membership', $ids);
    }
    public function test_artist_has_widgets(): void {
        $ids = DashboardPresets::forRole('artist');
        $this->assertNotEmpty($ids);
        $this->assertContains('widget_artist_feed_publisher', $ids);
    }
    public function test_org_has_widgets(): void {
        $ids = DashboardPresets::forRole('organization');
        $this->assertNotEmpty($ids);
        $this->assertContains('widget_webhooks', $ids);
    }
    public function test_bogus_falls_back_to_member(): void {
        $ids = DashboardPresets::forRole('bogus');
        $this->assertNotEmpty($ids);
        $this->assertContains('widget_membership', $ids);
    }
}
