<?php
namespace ArtPulse\Community\Tests;

use ArtPulse\Community\ReferralManager;
use ArtPulse\Core\BadgeRules;
use ArtPulse\Core\UserDashboardManager;

/**

 * @group COMMUNITY
 */

class ReferralManagerTest extends \WP_UnitTestCase {

	private int $referrer;
	private array $new_users = array();

	public function set_up() {
		parent::set_up();
		ReferralManager::register();
		BadgeRules::register();
		do_action( 'init' );
		do_action( 'rest_api_init' );

		$this->referrer = self::factory()->user->create();
	}

	public function test_redeem_referral_awards_badge_after_three(): void {
		$code = ReferralManager::create_code( $this->referrer );
		for ( $i = 0; $i < 3; $i++ ) {
			$uid               = self::factory()->user->create();
			$this->new_users[] = $uid;
			wp_set_current_user( $uid );
			$req = new \WP_REST_Request( 'POST', '/artpulse/v1/referral/redeem' );
			$req->set_param( 'code', $code );
			rest_get_server()->dispatch( $req );
			// generate new code for next user except last
			if ( $i < 2 ) {
				$code = ReferralManager::create_code( $this->referrer );
			}
		}

		$badges = UserDashboardManager::getBadges( $this->referrer );
		$this->assertContains( 'connector', $badges );
	}
}
