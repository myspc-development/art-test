<?php
namespace ArtPulse\Frontend;

require_once __DIR__ . '/_Html.php';

namespace ArtPulse\Frontend\Tests;

use WP_UnitTestCase;
use function ArtPulse\Frontend\Html\extract_widget_ids;
use ArtPulse\Core\DashboardWidgetRegistry;

class UserDashboardShortcodeTest extends WP_UnitTestCase {
    protected function setUp(): void {
        parent::setUp();
        // Swallow any accidental output to keep test strict
        $this->setOutputCallback(static fn() => '');
    }

    /**
     * @return array<string, array{0:string,1:string}>
     */
    public function provide_roles(): array {
        return [
            'subscriber'   => ['subscriber', 'member'],
            'contributor'  => ['contributor', 'member'],
            'author'       => ['author', 'member'],
            'editor'       => ['editor', 'member'],
            'member'       => ['member', 'member'],
            'artist'       => ['artist', 'artist'],
            'organization' => ['organization', 'organization'],
            'administrator'=> ['administrator', 'organization'],
        ];
    }

    /**
     * Ensure [user_dashboard] shortcode outputs the same widgets as
     * DashboardWidgetRegistry::render_for_role for various roles.
     *
     * @dataProvider provide_roles
     */
    public function test_user_dashboard_matches_render_for_role(string $wpRole, string $queryRole): void {
        if (!get_role($wpRole)) {
            add_role($wpRole, ucfirst($wpRole));
        }
        $uid = self::factory()->user->create(['role' => $wpRole]);
        wp_set_current_user($uid);

        // Render via registry
        ob_start();
        DashboardWidgetRegistry::render_for_role($uid);
        $expectedHtml = ob_get_clean();
        $expectedIds = extract_widget_ids($expectedHtml);

        // Render via shortcode
        set_query_var('ap_role', $queryRole);
        $html = do_shortcode('[user_dashboard]');
        $ids = extract_widget_ids($html);

        $this->assertSame($expectedIds, $ids, "Mismatch for role $wpRole");
        // Ensure canonicalization and deduplication
        $this->assertSame($ids, array_values(array_unique($ids)));
        $this->assertNotContains('widget_news_feed', $ids);
    }
}
