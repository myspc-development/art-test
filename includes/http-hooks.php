<?php
if (!defined('ABSPATH')) { exit; }

add_filter('http_request_args', function ($args, $url) {
    if (strpos($url, 'api.github.com') !== false) {
        $token = get_option('ap_github_token');
        if (!empty($token)) {
            $args['headers']['Authorization'] = 'token ' . $token;
        }
    }
    return $args;
}, 10, 2);
