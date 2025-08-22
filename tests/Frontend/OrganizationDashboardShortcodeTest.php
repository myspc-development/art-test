<?php
namespace ArtPulse\Frontend {
    // Shared Frontend stubs (provides get_post_meta, get_user_meta, etc.)
    require_once __DIR__ . '/../TestHelpers/FrontendFunctionStubs.php';

    // Test-specific stubs ONLY (no duplicates of shared ones)
    // Add only what this shortcode uniquely needs (leave out get_post_meta).
}

namespace ArtPulse\Frontend\Tests {
    use PHPUnit\Framework\TestCase;
    use ArtPulse\Frontend\OrganizationDashboardShortcode;
    use ArtPulse\Frontend\Tests\FrontendState;

    final class OrganizationDashboardShortcodeTest extends TestCase
    {
        protected function setUp(): void
        {
            // Configure only what this shortcode needs. Example:
            // FrontendState::$post_meta = [
            //     10 => ['ap_org_profile_published' => '1'],
            // ];
            FrontendState::$post_meta = [];
        }

        public function test_smoke_renders_string(): void
        {
            $html = OrganizationDashboardShortcode::render([]);
            $this->assertIsString($html);
        }
    }
}
