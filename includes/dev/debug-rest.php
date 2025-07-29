<?php
add_filter('rest_endpoints', function ($endpoints) {
    $seen = [];
    foreach ($endpoints as $route => $handlers) {
        if (isset($seen[$route])) {
            error_log("[REST DUPLICATE] Route already registered: $route");
        }
        $seen[$route] = true;
    }
    return $endpoints;
});
