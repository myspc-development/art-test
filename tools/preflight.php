<?php
declare(strict_types=1);

$errors = [];

if (!extension_loaded('mysqli')) {
    $errors[] = 'The mysqli extension is not loaded.';
}

$dbHost = getenv('WP_TESTS_DB_HOST') ?: getenv('DB_HOST');
$dbUser = getenv('WP_TESTS_DB_USER') ?: getenv('DB_USER');
$dbPass = getenv('WP_TESTS_DB_PASSWORD') ?: getenv('DB_PASSWORD');
$dbName = getenv('WP_TESTS_DB_NAME') ?: getenv('DB_NAME');

if ($dbHost && $dbUser) {
    mysqli_report(MYSQLI_REPORT_OFF);
    $mysqli = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($mysqli->connect_error) {
        $errors[] = 'Database connection failed: ' . $mysqli->connect_error;
    } else {
        $mysqli->close();
    }
} else {
    $errors[] = 'Database credentials not provided via WP_TESTS_DB_* or DB_* env vars.';
}

$wpDir = getenv('WP_PHPUNIT__DIR');
if (!$wpDir || !is_dir($wpDir) || !file_exists($wpDir . '/wp-settings.php')) {
    $errors[] = 'WP_PHPUNIT__DIR is missing or incomplete. Run tools/provision-wp-core.sh.';
}

if ($errors) {
    fwrite(STDERR, "Preflight checks failed:\n - " . implode("\n - ", $errors) . "\n");
    exit(1);
}

echo "Preflight checks passed.\n";
