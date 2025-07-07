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
}
