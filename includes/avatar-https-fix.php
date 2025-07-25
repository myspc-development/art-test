<?php
if (!defined('ABSPATH')) {
    exit;
}
// Force HTTPS for avatar URLs to avoid mixed content warnings
add_filter('get_avatar_url', function($url) {
    if ($url) {
        $url = set_url_scheme($url, 'https');
    }
    return $url;
});

// Final fallback in case other filters didn't convert to HTTPS
add_filter('get_avatar_url', function($url) {
    if ($url) {
        $url = preg_replace('/^http:\/\//i', 'https://', $url);
    }
    return $url;
}, 20);
// Support Simple Local Avatars plugin if active
add_filter('simple_local_avatar_url', function($url) {
    if ($url) {
        $url = set_url_scheme($url, 'https');
    }
    return $url;
});

// Force HTTPS for attachment URLs as well
add_filter('wp_get_attachment_url', function($url) {
    if ($url) {
        $url = set_url_scheme($url, 'https');
    }
    return $url;
});
