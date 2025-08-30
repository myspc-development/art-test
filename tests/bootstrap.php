<?php
declare(strict_types=1);

$autoloader = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
}

if (!defined('AP_TESTING')) {
    define('AP_TESTING', true);
}
if (getenv('AP_TEST_MODE') === false) {
    putenv('AP_TEST_MODE=1');
}

\ArtPulse\Tests\WPFunctionsStubs::register();
\ArtPulse\Tests\WpCliStub::load();
