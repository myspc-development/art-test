<?php
namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\AccessControlManager;

class AccessControlManagerTest extends TestCase
{
    public function test_free_member_requires_redirect(): void
    {
        $roles = ['member'];
        $settings = [];
        $this->assertTrue(
            AccessControlManager::needsRedirect($roles, 'Free', $settings)
        );
    }

    public function test_override_skips_redirect(): void
    {
        $roles = ['artist'];
        $settings = ['override_artist_membership' => 1];
        $this->assertFalse(
            AccessControlManager::needsRedirect($roles, 'Free', $settings)
        );
    }

    public function test_org_override_skips_redirect(): void
    {
        $roles = ['organization'];
        $settings = ['override_org_membership' => 1];
        $this->assertFalse(
            AccessControlManager::needsRedirect($roles, 'Free', $settings)
        );
    }

    public function test_member_override_skips_redirect(): void
    {
        $roles = ['member'];
        $settings = ['override_member_membership' => 1];
        $this->assertFalse(
            AccessControlManager::needsRedirect($roles, 'Free', $settings)
        );
    }

    public function test_paid_member_does_not_redirect(): void
    {
        $roles = ['member'];
        $settings = [];
        $this->assertFalse(
            AccessControlManager::needsRedirect($roles, 'Premium', $settings)
        );
    }
}
