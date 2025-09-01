<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Core\DirectoryManager;

/**
 * @group REST
 */
class OrgDirectoryTest extends \WP_UnitTestCase {

	private int $org_id;
	private int $logo_id;
	private string $org_type = 'museum';

	public function set_up() {
		parent::set_up();
		// create dummy image for featured logo
		$upload_dir = wp_upload_dir();
		$filename   = $upload_dir['basedir'] . '/test-logo.jpg';
		if ( ! file_exists( $filename ) ) {
			file_put_contents( $filename, 'logo' );
		}
		$this->logo_id = self::factory()->attachment->create_upload_object( $filename );

		$this->org_id = wp_insert_post(
			array(
				'post_title'  => 'Org',
				'post_type'   => 'artpulse_org',
				'post_status' => 'publish',
				'meta_input'  => array(
					'ead_org_logo_id'        => $this->logo_id,
					'ead_org_street_address' => '123 Main St',
					'ead_org_website_url'    => 'http://example.com',
					'ead_org_type'           => $this->org_type,
				),
			)
		);

		DirectoryManager::register();
		do_action( 'rest_api_init' );
	}

	public function test_org_directory_response_includes_meta(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/filter' );
		$req->set_param( 'type', 'org' );
		$res = rest_get_server()->dispatch( $req );

		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertCount( 1, $data );
		$org = $data[0];
		$this->assertSame( '123 Main St', $org['address'] );
		$this->assertSame( 'http://example.com', $org['website'] );
		$this->assertSame( $this->org_type, $org['org_type'] );
		$this->assertNotEmpty( $org['featured_media_url'] );
		$this->assertStringContainsString( 'test-logo.jpg', $org['featured_media_url'] );
	}

	public function test_filter_by_org_type(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/filter' );
		$req->set_param( 'type', 'org' );
		$req->set_param( 'org_type', $this->org_type );
		$res = rest_get_server()->dispatch( $req );

		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertCount( 1, $data );
		$this->assertSame( $this->org_type, $data[0]['org_type'] );
	}
}
