<?php
namespace ArtPulse\Cli\Tests;

require_once __DIR__ . '/../Stubs/wp_rest.php';
// WP-CLI stub loaded via bootstrap
require_once __DIR__ . '/../../includes/class-cli-rest-route-audit.php';

use PHPUnit\Framework\TestCase;
use WP_CLI;

/**

 * @group CLI

 */

class RestRouteAuditCliTest extends TestCase {

	protected function setUp(): void {
		WP_CLI::$commands       = array();
		WP_CLI::$last_output    = '';
		$GLOBALS['rest_server'] = null;
	}

	private static function server( array $routes ) {
		return new class($routes) {
			private array $routes;
			public function __construct( array $routes ) {
				$this->routes = $routes; }
			public function get_routes(): array {
				return $this->routes; }
		};
	}

	public static function return_true() {
		return true; }
	public static function return_false() {
		return false; }

	public function test_json_output_no_conflicts(): void {
		global $rest_server;
               $rest_server = self::server(
                       array(
                               '/ap/v1/widget_foo' => array(
					array(
						'methods'  => 'GET',
						'callback' => array( self::class, 'return_true' ),
					),
				),
			)
		);
		WP_CLI::add_command( 'ap:audit-rest-routes', \AP_CLI_Rest_Route_Audit::class );
		$out = WP_CLI::runcommand( 'ap:audit-rest-routes --json' );
		$this->assertSame( '[]', $out );
		$out2 = WP_CLI::runcommand( 'ap:audit-rest-routes' );
		$this->assertStringContainsString( 'No REST route conflicts found.', $out2 );
	}

	public function test_conflict_detection(): void {
		global $rest_server;
		$rest_server = self::server(
			array(
				'/ap/v1/conflict' => array(
					array(
						'methods'  => 'GET',
						'callback' => array( self::class, 'return_true' ),
					),
					array(
						'methods'  => 'GET',
						'callback' => array( self::class, 'return_false' ),
					),
				),
			)
		);
		WP_CLI::add_command( 'ap:audit-rest-routes', \AP_CLI_Rest_Route_Audit::class );
		$out = WP_CLI::runcommand( 'ap:audit-rest-routes' );
		$this->assertStringContainsString( 'GET /ap/v1/conflict', $out );
	}
}
