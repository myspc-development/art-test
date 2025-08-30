<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__);
}

// Load Patchwork early so we can redefine classes if necessary.
$patchwork = __DIR__ . '/../../vendor/antecedent/patchwork/Patchwork.php';
if (file_exists($patchwork)) {
    require_once $patchwork;
}

// Load the stub and alias it before Composer autoloads production code.
require_once __DIR__ . '/../Support/Stubs/DashboardControllerStub.php';

$stubClass       = \ArtPulse\Tests\Stubs\DashboardControllerStub::class;
$productionClass = 'ArtPulse\\Core\\DashboardController';

// If the production class was already loaded, redirect its methods to the stub.
if (class_exists($productionClass, false)) {
    if (function_exists('\\Patchwork\\redefine')) {
        \Patchwork\redefine(
            '\\' . $productionClass . '::*',
            static function (...$args) use ($stubClass) {
                $method = \Patchwork\getMethod();
                return $stubClass::$method(...$args);
            }
        );
    }
} else {
    class_alias($stubClass, $productionClass);
}

// Finally load the main bootstrap which in turn loads Composer autoload, etc.
require_once __DIR__ . '/../bootstrap.php';
