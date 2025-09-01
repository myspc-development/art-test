<?php
namespace ArtPulse\Community\Tests;

use ArtPulse\Community\ProfileLinkRequestRestController;

/**

 * @group COMMUNITY

 */

class ProfileLinkRequestRestControllerTest extends \WP_UnitTestCase {

	private int $artist_id;
	private int $org_user;
	private int $org_post;

	public function set_up() {
		parent::set_up();
		do_action( 'init' );
		ProfileLinkRequestRestController::register();
		do_action( 'rest_api_init' );

		$this->artist_id = self::factory()->user->create( array( 'role' => 'artist' ) );
		$this->org_user  = self::factory()->user->create( array( 'role' => 'organization' ) );
		$this->org_post  = wp_insert_post(
			array(
				'post_title'  => 'Org',
				'post_type'   => 'artpulse_org',
				'post_status' => 'publish',
				'post_author' => $this->org_user,
			)
		);
	}

	public function test_create_request_saves_meta(): void {
		wp_set_current_user( $this->artist_id );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/link-request' );
		$req->set_param( 'org_id', $this->org_post );
		$req->set_param( 'message', 'please link' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$id   = $data['request_id'];
		$this->assertSame( $this->artist_id, (int) get_post_meta( $id, 'artist_user_id', true ) );
		$this->assertSame( $this->org_post, (int) get_post_meta( $id, 'org_id', true ) );
		$this->assertSame( 'pending', get_post_meta( $id, 'status', true ) );
	}

	public function test_approve_creates_profile_link(): void {
		wp_set_current_user( $this->artist_id );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/link-request' );
		$req->set_param( 'org_id', $this->org_post );
		$res = rest_get_server()->dispatch( $req );
		$id  = $res->get_data()['request_id'];

		$admin = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/link-request/' . $id . '/approve' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );

		$links = get_posts(
			array(
				'post_type'   => 'ap_profile_link',
				'meta_key'    => 'request_id',
				'meta_value'  => $id,
				'post_status' => 'publish',
				'fields'      => 'ids',
				'numberposts' => 1,
			)
		);
		$this->assertCount( 1, $links );
		$link_id = $links[0];
		$this->assertSame( $this->artist_id, (int) get_post_meta( $link_id, 'artist_user_id', true ) );
		$this->assertSame( $this->org_post, (int) get_post_meta( $link_id, 'org_id', true ) );
		$this->assertSame( 'approved', get_post_meta( $id, 'status', true ) );
	}
}
