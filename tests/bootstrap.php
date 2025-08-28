<?php
declare(strict_types=1);

/**
 * Minimal bootstrap for pure unit tests — no WordPress test library here.
 */
error_reporting( E_ALL );
ini_set( 'display_errors', '0' );
ini_set( 'display_startup_errors', '0' );
ini_set( 'opcache.enable_cli', '0' );

require_once __DIR__ . '/../vendor/autoload.php';
// Canonical WP-CLI stub for unit tests
require_once __DIR__ . '/Cli/WP_CLI_Stub.php';
