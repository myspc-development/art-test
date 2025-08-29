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
}
