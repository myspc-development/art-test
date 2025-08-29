<?php
declare(strict_types=1);

if (!function_exists('ap_rest_request')) {
    /**
     * Dispatch a REST request with automatic nonce and params.
     *
     * @param string $method HTTP method.
     * @param string $route Route path.
     * @param array  $params Parameters/body.
     * @return \WP_REST_Response
     */
    function ap_rest_request(string $method, string $route, array $params = []) {
        $req = new WP_REST_Request($method, $route);
        if (strtoupper($method) === 'GET') {
            foreach ($params as $k => $v) {
                $req->set_param($k, $v);
            }
        } else {
            $req->set_body_params($params);
        }
        $req->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
        return rest_get_server()->dispatch($req);
    }
}

if (!function_exists('ap_as_user_with_role')) {
    /** Create and switch to a user with the given role. */
    function ap_as_user_with_role(string $role): int {
        $user_id = wp_insert_user([
            'user_login' => $role . '_' . wp_generate_password(5, false),
            'user_pass'  => 'password',
            'user_email' => $role . '_' . uniqid() . '@example.com',
            'role'       => $role,
        ]);
        wp_set_current_user($user_id);
        return (int) $user_id;
    }
}
