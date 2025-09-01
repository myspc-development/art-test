<?php
namespace ArtPulse\Admin\Tests;

use ArtPulse\Admin\OrgCommunicationsCenter;
use WP_UnitTestCase;

/**
 * @group ADMIN
 */
class OrgCommunicationsCenterTest extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		OrgCommunicationsCenter::install_messages_table();
	}

	public function test_get_messages_for_org_returns_empty(): void {
		$messages = OrgCommunicationsCenter::get_messages_for_org( 1 );
		$this->assertIsArray( $messages );
		$this->assertCount( 0, $messages );
	}
}
