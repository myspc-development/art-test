<?php
// ===== DB (adjust if needed) =====
if (!defined('DB_NAME'))     define('DB_NAME',     getenv('DB_NAME')     ?: 'wordpress_test');
if (!defined('DB_USER'))     define('DB_USER',     getenv('DB_USER')     ?: 'wordpress_test');
if (!defined('DB_PASSWORD')) define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '0preggers2');
if (!defined('DB_HOST'))     define('DB_HOST',     getenv('DB_HOST')     ?: '127.0.0.1'); // force TCP

if (!defined('DB_CHARSET'))  define('DB_CHARSET',  'utf8mb4');
if (!defined('DB_COLLATE'))  define('DB_COLLATE',  '');

// Table prefix as a variable (let vendor bootstrap define the constant)
$table_prefix = 'wptests_';

// ===== Defaults =====
if (!defined('WP_TESTS_DOMAIN')) define('WP_TESTS_DOMAIN', 'example.org');
if (!defined('WP_TESTS_EMAIL'))  define('WP_TESTS_EMAIL',  'admin@example.org');
if (!defined('WP_TESTS_TITLE'))  define('WP_TESTS_TITLE',  'Test Blog');
if (!defined('WP_DEBUG'))        define('WP_DEBUG', true);
if (!defined('WP_DEBUG_DISPLAY'))define('WP_DEBUG_DISPLAY', true);

// Where WordPress core will be installed by the test runner (must end with slash)
if (!defined('ABSPATH')) define('ABSPATH', sys_get_temp_dir() . '/wordpress/');

/** PHP CLI binary used by the WP test runner */
if ( ! defined( 'WP_PHP_BINARY' ) ) {
    define( 'WP_PHP_BINARY', PHP_BINARY ? PHP_BINARY : 'php' );
}
