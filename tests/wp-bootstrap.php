<?php
// tests/wp-bootstrap.php
declare(strict_types=1);

// 1) Composer autoload (look in common locations)
$paths = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../../../vendor/autoload.php',
];
foreach ($paths as $p) {
    if (file_exists($p)) { require $p; break; }
}

// 2) Locate wp-phpunit and bootstrap WordPress tests
$_wp_dir = getenv('WP_PHPUNIT__DIR') ?: __DIR__ . '/../vendor/wp-phpunit/wp-phpunit';
$bootstrap = rtrim($_wp_dir, "/\\") . '/includes/bootstrap.php';
if (!file_exists($bootstrap)) {
    fwrite(STDERR, "wp-phpunit bootstrap not found at {$bootstrap}\n");
    exit(1);
}
require $bootstrap;

// Optional: define plugin constants used by your code if needed.
// if (!defined('ARTPULSE_PLUGIN_FILE')) define('ARTPULSE_PLUGIN_FILE', __DIR__ . '/../artpulse.php');
