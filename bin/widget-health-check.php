#!/usr/bin/env php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

$root = dirname(__DIR__);
$config = include $root . '/config/dashboard-widgets.php';
$functionsFile = file_get_contents($root . '/includes/dashboard-widgets.php');

$lines = [];
foreach ($config as $id => $def) {
    $status = '[✗]';
    $detail = '';
    if (isset($def['class'])) {
        $class = ltrim($def['class'], '\\');
        if (str_starts_with($class, 'ArtPulse\\Widgets\\')) {
            $relative = substr($class, strlen('ArtPulse\\Widgets\\'));
            $parts = explode('\\', $relative);
            if (count($parts) > 1 && !empty($parts[0])) {
                $parts[0] = strtolower($parts[0]);
            }
            $path = $root . '/widgets/' . implode('/', $parts) . '.php';
        } else {
            $path = $root . '/widgets/' . $class . '.php';
        }
        if (file_exists($path)) {
            $contents = file_get_contents($path);
            if (str_contains($contents, 'function render')) {
                $status = '[✓]';
                $detail = $class . '::render()';
                if (str_contains($contents, 'ap-widget-placeholder') || str_contains($contents, 'This will')) {
                    $detail .= ' (stub)';
                }
            } else {
                $detail = 'Missing render method';
            }
        } else {
            $detail = 'Missing class file';
        }
    } elseif (isset($def['callback'])) {
        $cb = $def['callback'];
        if (is_array($cb)) {
            $class = $cb[0];
            $method = $cb[1];
            if (class_exists($class) && method_exists($class, $method)) {
                $status = '[✓]';
                $detail = $class . '::' . $method . '()';
            } else {
                $detail = 'Missing callback';
            }
        } else {
            if (str_contains($functionsFile, "function $cb")) {
                $status = '[✓]';
                $detail = $cb;
                $pattern = "/function\\s+$cb\\s*\\([^)]*\\)\\s*\{([^}]*)\}/s";
                if (preg_match($pattern, $functionsFile, $m) && (str_contains($m[1], 'ap-widget-placeholder') || str_contains($m[1], 'This widget will'))) {
                    $detail .= ' (stub)';
                }
            } else {
                $detail = 'Missing callback';
            }
        }
    } else {
        $detail = 'No implementation';
    }
    $lines[] = "$status $id → $detail";
}

echo implode(PHP_EOL, $lines) . PHP_EOL;
