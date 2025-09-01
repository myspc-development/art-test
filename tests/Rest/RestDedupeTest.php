<?php
namespace ArtPulse\Rest\Tests;

require_once dirname( __DIR__, 2 ) . '/includes/rest-dedupe.php';

/**
 * @group restapi
 */
class RestDedupeTest extends \WP_UnitTestCase {
          public static function ok(): string { return 'ok'; }
          public static function ok1(): string { return 'ok1'; }
          public static function ok2(): string { return 'ok2'; }

          protected function setUp(): void {
                  parent::setUp();
                  $GLOBALS['ap_rest_dedupe_notices'] = array();
          }

          public function test_removes_duplicate_routes_with_same_callback(): void {
                  $callback = [self::class, 'ok'];
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
                  $cb1   = [self::class, 'ok1'];
                  $cb2   = [self::class, 'ok2'];
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

                $filtered = \ap_deduplicate_rest_routes( $routes );
                $this->assertCount( 2, $filtered['/ap/v1/thing'] );
                $this->assertNotEmpty( $GLOBALS['ap_rest_dedupe_notices'] );
                $this->assertStringContainsString( '/ap/v1/thing', $GLOBALS['ap_rest_dedupe_notices'][0] );
        }
}
