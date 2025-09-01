<?php
namespace ArtPulse\Rest\Tests;

use WP_REST_Request;
use WP_REST_Server;
use WP_User;
use PHPUnit\Framework\Assert;

function as_role(string $role): int {
    $login = $role . '_user';
    $id    = username_exists($login);
    if (! $id) {
        $id = wp_create_user($login, 'password', $login . '@example.com');
    }
    $user = new WP_User((int) $id);
    $user->set_role($role);
    wp_set_current_user((int) $id);
    return (int) $id;
}

function nonce(string $action = 'wp_rest'): string {
    return wp_create_nonce($action);
}

function call(string $method, string $route, array $params = [], array $headers = []) {
    $req = new WP_REST_Request($method, $route);

    foreach ($headers as $key => $value) {
        $req->set_header($key, $value);
    }

    if (! empty($params)) {
        $contentType = $headers['Content-Type'] ?? $headers['content-type'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $req->set_body(json_encode($params));
        } elseif (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $req->set_body_params($params);
        } else {
            foreach ($params as $k => $v) {
                $req->set_param($k, $v);
            }
        }
    }

    $server = rest_get_server();
    if (! $server instanceof WP_REST_Server) {
        $server = new WP_REST_Server();
        $GLOBALS['wp_rest_server'] = $server;
        do_action('rest_api_init', $server);
    }

    return $server->dispatch($req);
}

function assertStatus($res, int $code): void {
    Assert::assertSame($code, $res->get_status());
}

function body($res) {
    return $res->get_data();
}
