<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Messages\AP_Message;

/**
 * @group REST
 */
class APMessageTest extends \WP_UnitTestCase {
	public function test_send_and_receive_message() {
		$sender   = self::factory()->user->create();
		$receiver = self::factory()->user->create();
		$id       = AP_Message::send( $sender, $receiver, 'Test message' );
		$inbox    = AP_Message::get_inbox( $receiver );
		$this->assertNotEmpty( $inbox );
		$this->assertEquals( 'Test message', $inbox[0]->content );
	}
}
