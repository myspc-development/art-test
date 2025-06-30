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
        'https://calendar.google.com/calendar/r/eventedit?text=%s&dates=%s/%s&details=%s&location=%s',
        rawurlencode($event->post_title),
        $start_str,
        $end_str,
        rawurlencode(wp_strip_all_tags($event->post_content)),
        rawurlencode(get_post_meta($event_id, 'venue_name', true))
    );

    $ics    = home_url('/events/' . $event_id . '/export.ics');
    $yahoo  = sprintf(
        'https://calendar.yahoo.com/?v=60&view=d&type=20&title=%s&st=%s&et=%s&desc=%s&in_loc=%s',
        rawurlencode($event->post_title),
        $start_str,
        $end_str,
        rawurlencode(wp_strip_all_tags($event->post_content)),
        rawurlencode(get_post_meta($event_id, 'venue_name', true))
    );

    ob_start();
    ?>
    <div class="ap-calendar-links" role="group">
        <a class="ap-calendar-google" href="<?php echo esc_url($google); ?>" target="_blank" rel="noopener noreferrer">Google</a>
        <a class="ap-calendar-ics" href="<?php echo esc_url($ics); ?>">ICS</a>
        <a class="ap-calendar-yahoo" href="<?php echo esc_url($yahoo); ?>" target="_blank" rel="noopener noreferrer">Yahoo</a>
    </div>
    <?php
    return trim(ob_get_clean());
}
