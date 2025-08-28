<?php
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('opcache.enable_cli', '0');

$lib = getenv('WP_PHPUNIT__DIR') ?: 'vendor/wp-phpunit/wp-phpunit';
if (!is_dir($lib)) {
  fwrite(STDERR, "WP_PHPUNIT__DIR not found at $lib\n");
  exit(1);
}
if (!defined('ABSPATH')) {
  $wp = __DIR__ . '/../wordpress';
  if (!is_dir($wp) || !file_exists($wp . '/wp-settings.php')) {
    fwrite(STDERR, "wordpress/ core not found. Run tools/provision-wp-core.sh or download WP.\n");
    exit(1);
  }
  define('ABSPATH', realpath($wp) . '/');
}
require $lib . '/includes/bootstrap.php';
