---
title: REST Testing Guide
category: developer
role: developer
last_updated: 2025-08-03
status: draft
---

# REST Testing Guide

## Spin Up Test Environment
Use the plugin's PHPUnit environment or `npm run test:php:up` to start a WordPress instance with tests tables.

## Hitting Routes with `WP_REST_Request`
```php
$request  = new WP_REST_Request( 'GET', '/my-plugin/v1/example' );
$response = rest_do_request( $request );
```

## Assertions
- Expect `401` or `403` for unauthenticated requests.
- Grant permissions and expect `200` for authorized requests.
- Validate returned data against the route schema.

## Sample Test
```php
public function test_route_requires_auth() {
    $request  = new WP_REST_Request( 'GET', '/my-plugin/v1/example' );
    $response = rest_do_request( $request );
    $this->assertSame( 401, $response->get_status() );
}
```
