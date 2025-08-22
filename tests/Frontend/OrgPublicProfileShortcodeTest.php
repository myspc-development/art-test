<?php
namespace ArtPulse\Frontend {
    // Shared Frontend stubs (provides get_post_meta, get_user_meta, etc.)
    require_once __DIR__ . '/../TestHelpers/FrontendFunctionStubs.php';

    // Test-specific stubs ONLY (no duplicates of shared ones)

    if (!function_exists(__NAMESPACE__ . '\get_page_by_path')) {
        function get_page_by_path($path, $output = OBJECT, $type = 'page') {
            return \ArtPulse\Frontend\Tests\OrgPublicProfileShortcodeTest::$page;
        }
    }

    if (!function_exists(__NAMESPACE__ . '\wp_get_attachment_url')) {
        function wp_get_attachment_url($id) { return 'img' . $id . '.jpg'; }
    }
}

namespace ArtPulse\Frontend\Tests {
    use PHPUnit\Framework\TestCase;
    use ArtPulse\Frontend\OrgPublicProfileShortcode;
    use ArtPulse\Frontend\Tests\FrontendState;

    final class OrgPublicProfileShortcodeTest extends TestCase
    {
        public static array $meta = []; // kept if the shortcode reads using get_post_meta
        public static $page = null;

        protected function setUp(): void
        {
            // Configure shared post meta state
            FrontendState::$post_meta = [
                // Per-post (id => [key => value])
                1 => [
                    'ap_org_profile_published' => '1',
                    'ap_org_tagline'           => 'Best Org',
                    'ead_org_logo_id'          => 4,
                    'ead_org_banner_id'        => 5,
                    'ead_org_description'      => 'About us',
                    'ap_org_featured_events'   => '2,3',
                ],
                // Global fallbacks
                'ap_org_theme_color' => '#abc',
            ];
            self::$page = null;
        }

        public function test_render_outputs_tagline(): void
        {
            $html = OrgPublicProfileShortcode::render(['id' => 1]);
            $this->assertStringContainsString('Best Org', $html);
            $this->assertStringContainsString('img4.jpg', $html);
            $this->assertStringContainsString('Event 2', $html);
        }
    }
}
