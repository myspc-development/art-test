<?php
namespace ArtPulse\Rest\Tests;

use WP_REST_Server;

/**
 * @group restapi
 */
class RestDedupeTest extends \WP_UnitTestCase
{
    public function test_deduplicate_routes_and_log_conflicts(): void
    {
        $log = tempnam(sys_get_temp_dir(), 'aplog');
        $prev = ini_set('error_log', $log);
        ini_set('log_errors', '1');

        $endpoints = [
            '/ap/v1/foo' => [
                [
                    'methods' => 'GET',
                    'callback' => '__return_true',
                    'permission_callback' => '__return_true',
                ],
                [
                    'methods' => 'GET',
                    'callback' => '__return_true',
                    'permission_callback' => '__return_true',
                ],
            ],
            '/ap/v1/bar' => [
                [
                    'methods' => 'POST',
                    'callback' => '__return_true',
                    'permission_callback' => '__return_true',
                ],
                [
                    'methods' => 'POST',
                    'callback' => '__return_false',
                    'permission_callback' => '__return_true',
                ],
            ],
        ];

        $result = \ap_deduplicate_rest_routes($endpoints);

        $this->assertCount(1, $result['/ap/v1/foo']);
        $this->assertCount(2, $result['/ap/v1/bar']);

        $contents = file_get_contents($log);
        $this->assertStringContainsString('[REST CONFLICT] Duplicate route /ap/v1/bar (POST)', $contents);

        ini_set('error_log', $prev);
        unlink($log);
    }

    public function test_ap_rest_route_registered_detects_methods_and_missing_routes(): void
    {
        global $wp_rest_server;
        $prev_server = $wp_rest_server;

        $wp_rest_server = new class {
            public function get_routes(): array
            {
                return [
                    '/ap/v1/sample' => [
                        ['methods' => 'GET|POST'],
                        ['methods' => ['DELETE']],
                        ['methods' => WP_REST_Server::EDITABLE],
                    ],
                ];
            }
        };

        $this->assertTrue(\ap_rest_route_registered('ap/v1', '/sample'));
        $this->assertTrue(\ap_rest_route_registered('ap/v1', '/sample', 'GET'));
        $this->assertTrue(\ap_rest_route_registered('ap/v1', '/sample', 'POST'));
        $this->assertTrue(\ap_rest_route_registered('ap/v1', '/sample', 'DELETE'));
        $this->assertTrue(\ap_rest_route_registered('ap/v1', '/sample', 'PUT'));
        $this->assertTrue(\ap_rest_route_registered('ap/v1', '/sample', 'PATCH'));
        $this->assertFalse(\ap_rest_route_registered('ap/v1', '/sample', 'OPTIONS'));
        $this->assertFalse(\ap_rest_route_registered('ap/v1', '/missing'));

        $wp_rest_server = $prev_server;
    }
}
