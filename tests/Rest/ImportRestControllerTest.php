<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\ImportRestController;
use function ArtPulse\Rest\Tests\as_role;
use function ArtPulse\Rest\Tests\call;
use function ArtPulse\Rest\Tests\assertStatus;
use function ArtPulse\Rest\Tests\body;

/**
 * @group REST
 */
class ImportRestControllerTest extends \WP_UnitTestCase {
	public function set_up() {
		parent::set_up();
		ImportRestController::register();
		do_action( 'rest_api_init' );
	}

	public function test_import_creates_posts(): void {
		as_role( 'administrator' );
		$res = call(
			'POST',
			'/artpulse/v1/import',
			array(
				'post_type' => 'artpulse_event',
				'rows'      => array(
					array(
						'post_title'   => 'My Event',
						'post_content' => 'Hello',
					),
				),
			)
		);
		assertStatus( $res, 200 );
		$data = body( $res );
		$this->assertArrayHasKey( 'created', $data );
		$this->assertCount( 1, $data['created'] );
		$post = get_post( $data['created'][0] );
		$this->assertNotNull( $post );
		$this->assertSame( 'My Event', $post->post_title );
	}

	public function test_import_requires_manage_options(): void {
		as_role( 'subscriber' );
		$res = call(
			'POST',
			'/artpulse/v1/import',
			array(
				'post_type' => 'artpulse_event',
				'rows'      => array(),
			)
		);
		assertStatus( $res, 403 );
	}
}
