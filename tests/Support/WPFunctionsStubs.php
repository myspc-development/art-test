<?php
namespace ArtPulse\Tests;

/**
 * Minimal WordPress function shims for unit tests.
 */
final class WPFunctionsStubs
{
    public static function register(): void
    {
        require_once __DIR__ . '/wp-functions-global.php';
    }
}
