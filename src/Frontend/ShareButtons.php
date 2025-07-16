<?php
namespace ArtPulse\Frontend;

/**
 * Helper to render social sharing buttons for an event.
 *
 * @param int $event_id Event post ID.
 * @return string HTML markup for share buttons.
 */
function ap_share_buttons(int $object_id, string $object_type = ''): string
{
    $url   = get_permalink($object_id);
    $title = get_the_title($object_id);

    if (!$url || !$title) {
        return '';
    }

    if (!$object_type) {
        $object_type = get_post_type($object_id) ?: 'post';
    }

    $networks = [
        'facebook' => sprintf('https://www.facebook.com/sharer/sharer.php?u=%s', urlencode($url)),
        'twitter'  => sprintf('https://twitter.com/share?url=%s&text=%s', urlencode($url), rawurlencode($title)),
        'linkedin' => sprintf('https://www.linkedin.com/shareArticle?mini=true&url=%s&title=%s', urlencode($url), rawurlencode($title)),
        'email'    => sprintf('mailto:?subject=%s&body=%s%%20%s', rawurlencode($title), rawurlencode(__('Check this out:', 'artpulse')), urlencode($url)),
    ];

    /**
     * Filter the share network links.
     *
     * @param array  $networks Key/value pairs of network => URL.
     * @param int    $object_id Object ID.
     * @param string $object_type Object type.
     */
    $networks = apply_filters('ap_share_networks', $networks, $object_id, $object_type);

    ob_start();
    echo sprintf('<div class="ap-share-buttons" role="group" data-share-id="%d" data-share-type="%s">',
        $object_id,
        esc_attr($object_type)
    );
    foreach ($networks as $name => $link) {
        $label = ucfirst($name);
        printf('<a class="ap-share-%1$s" href="%2$s" target="_blank" rel="noopener noreferrer" aria-label="%3$s">%3$s</a>',
            esc_attr($name),
            esc_url($link),
            esc_html($label)
        );
    }
    echo '</div>';
    return trim(ob_get_clean());
}

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

/**
 * Render calendar links for an event.
 *
 * @param int $event_id Event ID.
 * @return string HTML links.
 */
function ap_event_calendar_links(int $event_id): string
{
    $event = get_post($event_id);
    if (!$event || $event->post_type !== 'artpulse_event') {
        return '';
    }

    $start = get_post_meta($event_id, 'event_start_date', true);
    $end   = get_post_meta($event_id, 'event_end_date', true) ?: $start;
    $start_str = gmdate('Ymd\THis\Z', strtotime($start));
    $end_str   = gmdate('Ymd\THis\Z', strtotime($end));

    $google = sprintf(
        'https://calendar.google.com/calendar/render?action=TEMPLATE&text=%s&dates=%s/%s&details=%s',
        rawurlencode($event->post_title),
        $start_str,
        $end_str,
        rawurlencode(get_permalink($event_id))
    );

    $ics    = home_url('/events/' . $event_id . '/export.ics');

    ob_start();
    ?>
    <div class="ap-calendar-links" role="group">
        <a class="ap-calendar-google" href="<?php echo esc_url($google); ?>" target="_blank" rel="noopener noreferrer">Google</a>
        <a class="ap-calendar-outlook" href="<?php echo esc_url($ics); ?>">Outlook</a>
        <a class="ap-calendar-ics" href="<?php echo esc_url($ics); ?>">Apple (.ics)</a>
    </div>
    <?php
    return trim(ob_get_clean());
}
