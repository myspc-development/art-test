<?php
namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\AccessControlManager;

/**

 * @group CORE
 */

class AccessControlManagerTest extends TestCase {

	public function test_free_member_requires_redirect(): void {
		$roles    = array( 'member' );
		$settings = array();
		$this->assertTrue(
			AccessControlManager::needsRedirect( $roles, 'Free', $settings )
		);
	}

	public function test_override_skips_redirect(): void {
		$roles    = array( 'artist' );
		$settings = array( 'override_artist_membership' => 1 );
		$this->assertFalse(
			AccessControlManager::needsRedirect( $roles, 'Free', $settings )
		);
	}

	public function test_org_override_skips_redirect(): void {
		$roles    = array( 'organization' );
		$settings = array( 'override_org_membership' => 1 );
		$this->assertFalse(
			AccessControlManager::needsRedirect( $roles, 'Free', $settings )
		);
	}

	public function test_member_override_skips_redirect(): void {
		$roles    = array( 'member' );
		$settings = array( 'override_member_membership' => 1 );
		$this->assertFalse(
			AccessControlManager::needsRedirect( $roles, 'Free', $settings )
		);
	}

	public function test_paid_member_does_not_redirect(): void {
		$roles    = array( 'member' );
		$settings = array();
		$this->assertFalse(
			AccessControlManager::needsRedirect( $roles, 'Premium', $settings )
		);
	}
}
