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
// Support Simple Local Avatars plugin if active
add_filter('simple_local_avatar_url', function($url) {
    if ($url) {
        $url = set_url_scheme($url, 'https');
    }
    return $url;
});
