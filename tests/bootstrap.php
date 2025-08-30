<?php
declare(strict_types=1);

// Patchwork must run before any other code so it can redefine functions such as
// plugin_dir_path().
$patchwork = __DIR__ . '/../vendor/antecedent/patchwork/Patchwork.php';
if (file_exists($patchwork)) {
    require_once $patchwork;
}

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

// Stubs should only be registered after Patchwork has been bootstrapped to
// avoid defining plugin_dir_path() too early.
\ArtPulse\Tests\WPFunctionsStubs::register();
\ArtPulse\Tests\WpCliStub::load();
