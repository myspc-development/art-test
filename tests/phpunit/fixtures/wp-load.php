<?php
function foo_callback() {}
function bar_callback() {}
class WP_REST_Server {
    public function get_routes() {
        return [
            '/duplicate' => [
                ['methods' => 'GET', 'callback' => 'foo_callback'],
                ['methods' => 'GET', 'callback' => 'bar_callback'],
            ],
        ];
    }
}
function rest_get_server() { return new WP_REST_Server(); }
function do_action($hook) {}
?>
