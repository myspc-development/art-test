<?php
namespace ArtPulse\Rest;

class RouteAudit {
    public static function analyze(): array {
        $routes = rest_get_server()->get_routes();
        $norm = [];
        foreach ($routes as $path => $defs) {
            $key = strtolower(preg_replace('#\(\?P<[^>]+>[^)]+\)#', '{}', $path));
            $norm[$key] = ($norm[$key] ?? 0) + 1;
        }
        $conflicts = [];
        foreach ($norm as $k => $n) {
            if ($n > 1) {
                $conflicts[] = $k;
            }
        }
        return ['count' => count($routes), 'conflicts' => $conflicts];
    }
}
