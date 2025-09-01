<?php
namespace ArtPulse\Rest\Tests;

/**
 * @group REST
 */
class RestRouteAuditCommandTest extends \WP_UnitTestCase {

	public function set_up() {
		parent::set_up();

		// We intentionally register a duplicate route to trigger a conflict.
		// WordPress will mark duplicate registration as "doing it wrong".
		$this->setExpectedIncorrectUsage( 'register_rest_route' );

		\register_rest_route(
			'ap/v1',
			'/conflict',
			array(
				'methods'             => 'GET',
				'callback'            => '__return_true',
				'permission_callback' => '__return_true',
			)
		);

		\register_rest_route(
			'ap/v1',
			'/conflict',
			array(
				'methods'             => 'GET',
				'callback'            => '__return_false',
				'permission_callback' => '__return_true',
			)
		);
	}

	public function tear_down() {
		// Reset the REST server so our test-only routes don't leak into other tests.
		global $wp_rest_server;
		$wp_rest_server = null;
		parent::tear_down();
	}

	public function test_detects_conflict(): void {
		$cmd       = new \AP_CLI_Rest_Route_Audit();
		$conflicts = $cmd->find_conflicts();

		$found = null;
		foreach ( $conflicts as $conflict ) {
			if ( $conflict['path'] === '/ap/v1/conflict' && $conflict['method'] === 'GET' ) {
				$found = $conflict;
				break;
			}
		}

		$this->assertNotNull( $found, 'Conflict was not detected' );
		$this->assertCount( 2, $found['callbacks'], 'Expected 2 callbacks for the conflicted route' );
	}

	public function test_json_output(): void {
		$cmd = new \AP_CLI_Rest_Route_Audit();

		ob_start();
		$cmd->__invoke( array(), array( 'json' => true ) );
		$out  = ob_get_clean();

		$data = json_decode( $out, true );
		$this->assertIsArray( $data );
		$this->assertGreaterThan( 0, count( $data ) );
	}
}
