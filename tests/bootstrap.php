<?php
require_once __DIR__ . '/../vendor/autoload.php';

$wp_tests_dir = getenv('WP_PHPUNIT__DIR') ?: __DIR__ . '/../vendor/wp-phpunit/wp-phpunit';
if ( ! file_exists( $wp_tests_dir . '/includes/functions.php' ) ) {
    fwrite( STDERR, "\xE2\x9D\x8C WP_PHPUNIT__DIR not found at $wp_tests_dir. Did you run composer install and bin/install-wp-tests.sh?\n" );
    exit(1);
}
require $wp_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
    require dirname(__DIR__) . '/artpulse-management.php';
}
tests_add_filter('muplugins_loaded', '_manually_load_plugin');

require $wp_tests_dir . '/includes/bootstrap.php';

if ( ! file_exists( ABSPATH . 'wp-settings.php' ) ) {
    fwrite( STDERR, "\xE2\x9D\x8C WordPress core could not be located. Did you run bin/install-wp-tests.sh?\n" );
    exit(1);
}

