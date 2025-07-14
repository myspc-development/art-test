<?php
if (!defined('ABSPATH')) {
    exit;
}

function ap_output_pwa_meta() {
    echo '<link rel="manifest" href="/manifest.json">' . "\n";
    echo '<meta name="theme-color" content="#000000">' . "\n";
}
add_action('wp_head', 'ap_output_pwa_meta');
