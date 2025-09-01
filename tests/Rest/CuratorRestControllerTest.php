<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Curator\CuratorManager;
use ArtPulse\Rest\CuratorRestController;

/**
 * @group REST
 */
class CuratorRestControllerTest extends \WP_UnitTestCase {

	private int $user_id;
	private string $slug;
	private int $collection1;
	private int $collection2;

	public function set_up() {
		parent::set_up();
		wp_set_current_user( self::factory()->user->create() );
		CuratorManager::install_table();
		CuratorRestController::register();
		do_action( 'rest_api_init' );

		$this->user_id = self::factory()->user->create( array( 'role' => 'curator' ) );
		$this->slug    = 'test-curator';
		global $wpdb;
		$table = $wpdb->prefix . 'ap_curators';
		$wpdb->insert(
			$table,
			array(
				'user_id' => $this->user_id,
				'name'    => 'Test Curator',
				'slug'    => $this->slug,
			)
		);

		$this->collection1 = self::factory()->post->create(
			array(
				'post_type'   => 'ap_collection',
				'post_title'  => 'Col 1',
				'post_status' => 'publish',
				'post_author' => $this->user_id,
			)
		);
		$this->collection2 = self::factory()->post->create(
			array(
				'post_type'   => 'ap_collection',
				'post_title'  => 'Col 2',
				'post_status' => 'publish',
				'post_author' => $this->user_id,
			)
		);
	}

	public function test_get_curators(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/curators' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertCount( 1, $data );
		$this->assertSame( 'Test Curator', $data[0]['name'] );
	}

	public function test_get_single_curator(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/curator/' . $this->slug );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertSame( 'Test Curator', $data['name'] );
		$this->assertCount( 2, $data['collections'] );
	}
}
