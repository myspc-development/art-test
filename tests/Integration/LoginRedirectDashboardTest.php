<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Core\LoginRedirectManager;

/**
 * @group INTEGRATION
 */
class LoginRedirectDashboardTest extends \WP_UnitTestCase {
    private function run_redirect( array $roles ): string {
        $user = (object) array( 'roles' => $roles );
        return LoginRedirectManager::get_post_login_redirect_url( $user, '' );
    }

    public function test_member_redirects_to_user_dashboard(): void {
        $url = $this->run_redirect( array( 'member' ) );
        $this->assertStringContainsString( '/dashboard/user', $url );
    }

    public function test_artist_redirects_to_artist_dashboard(): void {
        $url = $this->run_redirect( array( 'artist' ) );
        $this->assertStringContainsString( '/dashboard/artist', $url );
    }

    public function test_org_redirects_to_org_dashboard(): void {
        $url = $this->run_redirect( array( 'organization' ) );
        $this->assertStringContainsString( '/dashboard/org', $url );
    }
}
