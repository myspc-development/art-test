<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\PortfolioRestController;

/**
 * @group REST
 */
class PortfolioControllerTest extends \WP_UnitTestCase {
	private int $u1;
	private int $u2;
	private int $attachment;

	public function set_up() {
		parent::set_up();
		$this->u1 = self::factory()->user->create( array( 'role' => 'artist' ) );
		$this->u2 = self::factory()->user->create( array( 'role' => 'artist' ) );
		wp_set_current_user( $this->u1 );
		$file             = DIR_TESTDATA . '/images/canola.jpg';
		$this->attachment = self::factory()->attachment->create_upload_object( $file );
		wp_update_post(
			array(
				'ID'          => $this->attachment,
				'post_author' => $this->u1,
			)
		);
		PortfolioRestController::register();
		do_action( 'rest_api_init' );
	}

	public function test_portfolio_crud_and_permissions(): void {
		// GET portfolio
		$req = new \WP_REST_Request( 'GET', '/ap/v1/portfolio' );
		$req->set_param( 'user', 'me' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertArrayHasKey( 'items', $data );
		$this->assertSame( array(), $data['items'] );

		// POST without alt
		$req = new \WP_REST_Request( 'POST', '/ap/v1/portfolio/items' );
		$req->set_param( 'media_id', $this->attachment );
		$req->set_param( 'meta', array() );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 400, $res->get_status() );
		$this->assertSame( 'invalid_alt', $res->get_data()['code'] );

		// POST with alt
		$req = new \WP_REST_Request( 'POST', '/ap/v1/portfolio/items' );
		$req->set_param( 'media_id', $this->attachment );
		$req->set_param( 'meta', array( 'alt' => 'test image' ) );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertCount( 1, $data['items'] );
		$this->assertSame( array( $this->attachment ), get_post_meta( $data['profile_id'], 'ap_portfolio_order', true ) );

		// Another user cannot link
		wp_set_current_user( $this->u2 );
		$req = new \WP_REST_Request( 'POST', '/ap/v1/portfolio/items' );
		$req->set_param( 'media_id', $this->attachment );
		$req->set_param( 'meta', array( 'alt' => 'x' ) );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 403, $res->get_status() );

		// Owner cannot include other user's attachment in order or featured
		wp_set_current_user( $this->u1 );
		$file  = DIR_TESTDATA . '/images/canola.jpg';
		$other = self::factory()->attachment->create_upload_object( $file );
		wp_update_post(
			array(
				'ID'          => $other,
				'post_author' => $this->u2,
			)
		);

		$req = new \WP_REST_Request( 'POST', '/ap/v1/portfolio/order' );
		$req->set_param( 'order', array( $this->attachment, $other ) );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 403, $res->get_status() );

		$req = new \WP_REST_Request( 'POST', '/ap/v1/portfolio/featured' );
		$req->set_param( 'attachment_id', $other );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 403, $res->get_status() );
	}
}
