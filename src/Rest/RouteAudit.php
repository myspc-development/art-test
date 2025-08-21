<?php
namespace ArtPulse\Rest;

final class RouteAudit {
    /**
     * Returns first conflict found or an array of conflicts.
     * Conflict = same route path AND overlapping HTTP method.
     */
    public static function find_conflicts(): ?array {
        $server = rest_get_server();
        if (!$server) return null;
        $routes = $server->get_routes();
        $conflicts = [];

        foreach ($routes as $path => $handlers) {
            // $handlers is array of endpoint arrays
            // Build method signatures for this path
            $methodSeen = [];
            foreach ((array) $handlers as $endpoint) {
                $methods = $endpoint['methods'] ?? [];
                if (is_string($methods)) $methods = [$methods];
                foreach ($methods as $m) {
                    $sig = strtoupper($m);
                    if (isset($methodSeen[$sig])) {
                        $conflicts[] = ['path' => $path, 'method' => $sig];
                    } else {
                        $methodSeen[$sig] = true;
                    }
                }
            }
        }
        return $conflicts ? $conflicts : null;
    }
}
