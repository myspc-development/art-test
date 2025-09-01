<?php
namespace ArtPulse\Ajax\Tests;

use ArtPulse\Ajax\FrontendFilterHandler;
use ArtPulse\Tests\Stubs\MockStorage;
use WP_Query;

/**

 * @group ajax

 */

class FrontendFilterHandlerTest extends \WP_UnitTestCase {

	public static array $posts      = array();
	public static array $json       = array();
	public static array $query_args = array();

	public function set_up(): void {
		parent::set_up();
		self::$posts                 = array();
		MockStorage::$json           = array();
		self::$query_args            = array();
		WP_Query::$default_posts     = array();
		WP_Query::$default_max_pages = 3;
		WP_Query::$last_args         = array();
		$_GET                        = array();
	}

	public function tear_down(): void {
		$_GET              = array();
		self::$posts       = array();
		MockStorage::$json = array();
		self::$query_args  = array();
		parent::tear_down();
	}

	public function test_handle_filter_posts_outputs_json(): void {
		self::$posts             = array( 7, 8 );
		$_GET                    = array(
			'page'     => 2,
			'per_page' => 5,
			'terms'    => 'a,b',
			'nonce'    => 'n',
		);
		WP_Query::$default_posts = self::$posts;
		FrontendFilterHandler::handle_filter_posts();
		self::$query_args = WP_Query::$last_args;
		$this->assertSame( 2, self::$query_args['paged'] );
		$this->assertSame( 5, self::$query_args['posts_per_page'] );
		$this->assertCount( 2, MockStorage::$json['posts'] );
		$this->assertSame( '/post/7', MockStorage::$json['posts'][0]['link'] );
		$this->assertSame( 2, MockStorage::$json['page'] );
		$this->assertSame( 3, MockStorage::$json['max_page'] );
	}
}
