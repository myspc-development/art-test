<?php
namespace ArtPulse\Tests;

/**
 * Test-only error handler to silence the specific “Dashboard widget not registered” warning.
 */
function mute_missing_widget_warning(int $errno, string $errstr): bool {
    if ($errno === E_USER_WARNING && strpos($errstr, 'Dashboard widget not registered') !== false) {
        return true; // swallow just this warning
    }
    return false;   // let everything else through
}
