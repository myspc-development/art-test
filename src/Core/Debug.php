<?php
namespace ArtPulse\Core;

function ap_debug_log(string $message): void {
    if (defined('WP_CLI') && WP_CLI) {
        // Use WP-CLI's debug channel so output is hidden unless --debug is set.
        \WP_CLI::debug($message, 'artpulse');
        return;
    }
    // Default: never pollute STDOUT; send to error log
    error_log('[ArtPulse] ' . $message);
}

function ap_maybe_echo(string $message): void {
    // Only echo when explicitly enabled (local debugging)
    if (getenv('AP_DEBUG_ECHO')) {
        echo $message;
    } else {
        ap_debug_log($message);
    }
}
