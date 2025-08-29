<?php
declare(strict_types=1);

$autoloader = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
}

// Auto-load test helpers/traits/stubs for UNIT tests (no WP loaded here)
foreach ([__DIR__ . '/Traits', __DIR__ . '/Support', __DIR__ . '/helpers'] as $dir) {
    if (is_dir($dir)) {
        foreach (glob($dir . '/*.php') as $file) {
            require_once $file;
        }
    }
}
