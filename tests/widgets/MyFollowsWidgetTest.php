<?php

use ArtPulse\Community\FollowManager;

/**

 * @group WIDGETS

 */

class MyFollowsWidgetTest extends \WP_UnitTestCase {
	public function set_up() {
		parent::set_up();
		FollowManager::install_follows_table();
	}

	public function test_fallback_message_when_no_follows(): void {
		$user_id = self::factory()->user->create();
		wp_set_current_user( $user_id );

		$output = ap_widget_my_follows( $user_id );

		$this->assertStringContainsString(
			'You are not following any artists or events.',
			$output,
			'Fallback message should render when follow data is absent.'
		);
	}
}
