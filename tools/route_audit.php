<?php
$base = dirname(__DIR__);
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS));
$routes = [];
foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') continue;
    $content = file_get_contents($file->getPathname());
    if (preg_match_all('/register_rest_route\s*\((.*?)\);/s', $content, $calls, PREG_SET_ORDER)) {
        foreach ($calls as $call) {
            $args = $call[1];
            // Extract first two quoted strings for namespace and route
            if (preg_match_all("/'([^']+)'/", $args, $parts) < 2) continue;
            $namespace = $parts[1][0] ?? '';
            $route = $parts[1][1] ?? '';
            $methods = null;
            if (preg_match("/'methods'\s*=>\s*'([^']+)'/", $args, $m)) $methods=$m[1];
            $callback = null;
            if (preg_match("/'callback'\s*=>\s*([^,\n]+)", $args, $m)) $callback=trim($m[1]);
            $perm='absent';
            if (strpos($args,'permission_callback')!==false) $perm='present';
            $routes[]=[
                'file'=>str_replace($base.'/', '', $file->getPathname()),
                'namespace'=>$namespace,
                'route'=>$route,
                'methods'=>$methods,
                'callback'=>$callback,
                'permission_callback'=>$perm,
            ];
        }
    }
}
file_put_contents(__DIR__.'/rest_routes.json', json_encode($routes, JSON_PRETTY_PRINT));
?>
