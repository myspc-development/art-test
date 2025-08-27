<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardPresets;

final class DashboardPresetsLoadTest extends TestCase {
    private string $dataDir;

    protected function setUp(): void {
        $this->dataDir = dirname(__DIR__, 2) . '/data';
    }

    /** @runInSeparateProcess */
    public function test_fallback_when_json_missing(): void {
        $roles = ['member','artist','organization'];
        $backups = [];
        foreach ($roles as $r) {
            $path = "$this->dataDir/preset-$r.json";
            if (is_readable($path)) {
                $backups[$r] = file_get_contents($path);
                unlink($path);
            }
        }
        $expected = [
            'member' => ['widget_membership','widget_account_tools','widget_my_follows','widget_recommended_for_you','widget_local_events','widget_my_events','widget_site_stats'],
            'artist' => ['widget_artist_revenue_summary','widget_artist_artwork_manager','widget_artist_audience_insights','widget_artist_feed_publisher','widget_my_events','widget_site_stats'],
            'organization' => ['widget_audience_crm','widget_org_ticket_insights','widget_webhooks','widget_my_events','widget_site_stats'],
        ];
        foreach ($roles as $r) {
            $this->assertSame($expected[$r], DashboardPresets::forRole($r));
        }
        foreach ($backups as $r => $contents) {
            file_put_contents("$this->dataDir/preset-$r.json", (string) $contents);
        }
    }

    /** @runInSeparateProcess */
    public function test_json_canonicalization(): void {
        $path = "$this->dataDir/preset-member.json";
        $orig = is_readable($path) ? file_get_contents($path) : null;
        file_put_contents($path, json_encode(['membership','account-tools','widget_followed_artists']));
        $this->assertSame(
            ['widget_membership','widget_account_tools','widget_my_follows'],
            DashboardPresets::forRole('member')
        );
        if ($orig !== null) {
            file_put_contents($path, $orig);
        } else {
            unlink($path);
        }
    }

    public function test_bogus_falls_back_to_member(): void {
        $ids = DashboardPresets::forRole('bogus');
        $this->assertContains('widget_membership', $ids);
    }
}
