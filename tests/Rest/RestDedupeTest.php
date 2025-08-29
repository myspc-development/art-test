<?php
namespace ArtPulse\Rest\Tests;

require_once dirname( __DIR__, 2 ) . '/includes/rest-dedupe.php';

/**
 * @group restapi
 */
class RestDedupeTest extends \WP_UnitTestCase {
        public function test_removes_duplicate_routes_with_same_callback(): void {
                $callback = static fn () => 'ok';
                $routes   = array(
                        '/ap/v1/thing' => array(
                                array(
                                        'methods'  => 'GET',
                                        'callback' => $callback,
                                ),
                                array(
                                        'methods'  => 'GET',
                                        'callback' => $callback,
                                ),
                        ),
                );

                $filtered = \ap_deduplicate_rest_routes( $routes );
                $this->assertCount( 1, $filtered['/ap/v1/thing'] );
        }

        public function test_conflicting_routes_trigger_notice(): void {
                $cb1   = static fn () => 'ok1';
                $cb2   = static fn () => 'ok2';
                $routes = array(
                        '/ap/v1/thing' => array(
                                array(
                                        'methods'  => 'GET',
                                        'callback' => $cb1,
                                ),
                                array(
                                        'methods'  => 'GET',
                                        'callback' => $cb2,
                                ),
                        ),
                );

                $this->setExpectedIncorrectUsage( 'ap_rest_dedupe' );
                $filtered = \ap_deduplicate_rest_routes( $routes );
                $this->assertCount( 2, $filtered['/ap/v1/thing'] );
                $this->assertContains( '/ap/v1/thing', $GLOBALS['ap_rest_diagnostics']['conflicts'] );
        }
}
