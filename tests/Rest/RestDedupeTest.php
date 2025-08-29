<?php
namespace ArtPulse\Rest\Tests;

use WP_REST_Server;
use function ArtPulse\Tests\safe_unlink;

/**
 * @group restapi
 */
class RestDedupeTest extends \WP_UnitTestCase {

	public function test_deduplicate_routes_and_log_conflicts(): void {
			$GLOBALS['ap_rest_diagnostics'] = array(
				'conflicts' => array(),
				'missing'   => array(),
			);

			$log  = tempnam( sys_get_temp_dir(), 'aplog' );
			$prev = ini_set( 'error_log', $log );
			ini_set( 'log_errors', '1' );

			$endpoints = array(
				'/ap/v1/foo' => array(
					array(
						'methods'             => 'GET',
						'callback'            => '__return_true',
						'permission_callback' => '__return_true',
					),
					array(
						'methods'             => 'GET',
						'callback'            => '__return_true',
						'permission_callback' => '__return_true',
					),
				),
				'/ap/v1/bar' => array(
					array(
						'methods'             => 'GET',
						'callback'            => '__return_true',
						'permission_callback' => '__return_true',
					),
					array(
						'methods'             => 'GET',
						'callback'            => '__return_false',
						'permission_callback' => '__return_true',
					),
					array(
						'methods'             => 'POST',
						'callback'            => '__return_true',
						'permission_callback' => '__return_true',
					),
					array(
						'methods'             => 'POST',
						'callback'            => '__return_false',
						'permission_callback' => '__return_true',
					),
				),
			);

			$result = \ap_deduplicate_rest_routes( $endpoints );

			$this->assertCount( 1, $result['/ap/v1/foo'] );
			$this->assertCount( 4, $result['/ap/v1/bar'] );

			$contents = file_get_contents( $log );
			$this->assertSame( 1, substr_count( $contents, '[REST CONFLICT] Duplicate route /ap/v1/bar' ) );
			$this->assertSame( array( '/ap/v1/bar' ), $GLOBALS['ap_rest_diagnostics']['conflicts'] );
			$this->assertEmpty( $GLOBALS['ap_rest_diagnostics']['missing'] );

			ini_set( 'error_log', $prev );
			safe_unlink( $log );
	}

	public function test_deduplicate_routes_without_conflicts(): void {
			$GLOBALS['ap_rest_diagnostics'] = array(
				'conflicts' => array(),
				'missing'   => array(),
			);

			$log  = tempnam( sys_get_temp_dir(), 'aplog' );
			$prev = ini_set( 'error_log', $log );
			ini_set( 'log_errors', '1' );

			$endpoints = array(
				'/ap/v1/foo' => array(
					array(
						'methods'             => 'GET',
						'callback'            => '__return_true',
						'permission_callback' => '__return_true',
					),
				),
				'/ap/v1/bar' => array(
					array(
						'methods'             => 'POST',
						'callback'            => '__return_true',
						'permission_callback' => '__return_true',
					),
				),
			);

			$result = \ap_deduplicate_rest_routes( $endpoints );

			$this->assertCount( 1, $result['/ap/v1/foo'] );
			$this->assertCount( 1, $result['/ap/v1/bar'] );

			$contents = file_get_contents( $log );
			$this->assertSame( '', $contents );
			$this->assertEmpty( $GLOBALS['ap_rest_diagnostics']['conflicts'] );
			$this->assertEmpty( $GLOBALS['ap_rest_diagnostics']['missing'] );

			ini_set( 'error_log', $prev );
			safe_unlink( $log );
	}

	public function test_ap_rest_route_registered_detects_methods_and_missing_routes(): void {
			$GLOBALS['ap_rest_diagnostics'] = array(
				'conflicts' => array(),
				'missing'   => array(),
			);

			global $wp_rest_server;
			$prev_server = $wp_rest_server;

			$wp_rest_server = new class() {
				public function get_routes(): array {
						return array(
							'/ap/v1/sample' => array(
								array( 'methods' => 'GET|POST' ),
								array( 'methods' => array( 'DELETE' ) ),
								array( 'methods' => WP_REST_Server::EDITABLE ),
								array( 'methods' => 'OPTIONS' ),
							),
						);
				}
			};

			$this->assertTrue( \ap_rest_route_registered( 'ap/v1/', 'sample/' ) );
			$this->assertTrue( \ap_rest_route_registered( 'ap/v1/', 'sample/', 'get' ) );
			$this->assertTrue( \ap_rest_route_registered( 'ap/v1', '/sample', 'HEAD' ) );
			$this->assertTrue( \ap_rest_route_registered( 'ap/v1', '/sample', 'POST' ) );
			$this->assertTrue( \ap_rest_route_registered( 'ap/v1', '/sample', 'DELETE' ) );
			$this->assertTrue( \ap_rest_route_registered( 'ap/v1', '/sample', 'PUT' ) );
			$this->assertTrue( \ap_rest_route_registered( 'ap/v1', '/sample', 'PATCH' ) );
			$this->assertTrue( \ap_rest_route_registered( 'ap/v1', '/sample', 'OPTIONS' ) );
			$this->assertFalse( \ap_rest_route_registered( 'ap/v1', '/missing' ) );
			$this->assertSame( array( '/ap/v1/missing' ), $GLOBALS['ap_rest_diagnostics']['missing'] );
			$this->assertEmpty( $GLOBALS['ap_rest_diagnostics']['conflicts'] );

			$wp_rest_server = $prev_server;
	}

	public function test_ap_rest_route_registered_handles_trailing_slashes_and_case(): void {
			$GLOBALS['ap_rest_diagnostics'] = array(
				'conflicts' => array(),
				'missing'   => array(),
			);

			global $wp_rest_server;
			$prev_server = $wp_rest_server;

			$wp_rest_server = new class() {
				public function get_routes(): array {
						return array(
							'/ap/v1/sample' => array(
								array( 'methods' => 'GET' ),
							),
						);
				}
			};

			$this->assertTrue( \ap_rest_route_registered( 'AP/V1//', '//Sample///' ) );
			$this->assertEmpty( $GLOBALS['ap_rest_diagnostics']['missing'] );

			$wp_rest_server = $prev_server;
	}
}
