<?php
namespace ArtPulse\Tests\Rest;
class RequestStub extends \WP_REST_Request {
    public function __construct(string $method = 'GET', string $route = '/') {
        parent::__construct($method, $route);
    }
}
