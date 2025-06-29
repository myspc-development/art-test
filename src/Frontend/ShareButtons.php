<?php
namespace ArtPulse\Frontend;

/**
 * Helper to render social sharing buttons for an event.
 *
 * @param int $event_id Event post ID.
 * @return string HTML markup for share buttons.
 */
function ap_event_share_buttons(int $event_id): string
{
    $url   = get_permalink($event_id);
    $title = get_the_title($event_id);

    if (!$url || !$title) {
        return '';
    }

    $networks = [
        'facebook' => sprintf('https://www.facebook.com/sharer/sharer.php?u=%s', urlencode($url)),
        'twitter'  => sprintf('https://twitter.com/share?url=%s&text=%s', urlencode($url), rawurlencode($title)),
        'whatsapp' => sprintf('https://wa.me/?text=%s%%20%s', rawurlencode($title), urlencode($url)),
        'email'    => sprintf('mailto:?subject=%s&body=%s%%20%s', rawurlencode($title), rawurlencode(__('Check out this event:', 'artpulse')), urlencode($url)),
    ];

    /**
     * Filter the share network links.
     *
     * @param array  $networks Key/value pairs of network => URL.
     * @param int    $event_id Event ID.
     */
    $networks = apply_filters('ap_event_share_networks', $networks, $event_id);

    ob_start();
    echo '<div class="ap-share-buttons" role="group">';
    foreach ($networks as $name => $link) {
        $label = ucfirst($name);
        $attr  = esc_url($link);
        printf('<a class="ap-share-%1$s" href="%2$s" target="_blank" rel="noopener noreferrer" aria-label="%3$s">%3$s</a>',
            esc_attr($name),
            $attr,
            esc_html($label)
        );
    }
    echo '</div>';
    return trim(ob_get_clean());
}
