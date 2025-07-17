<?php
namespace ArtPulse\Frontend;

/**
 * Generate a feed URL with optional filters.
 *
 * @param array $params Query parameters like org, tag or region.
 * @param string $format Output format: 'ics', 'json' or 'rss'.
 * @return string
 */
function ap_filtered_feed_link(array $params = [], string $format = 'ics'): string
{
    $ext = match ($format) {
        'json' => 'json',
        'rss'  => 'xml',
        default => 'ics',
    };
    $url = home_url('/feeds/events.' . $ext);
    if ($params) {
        $url = add_query_arg(array_map('sanitize_text_field', $params), $url);
    }
    return $url;
}
