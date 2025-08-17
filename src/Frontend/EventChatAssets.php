<?php
declare(strict_types=1);

namespace ArtPulse\Frontend;

class EventChatAssets
{
    public static function register(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'maybe_localize'], 20);
    }

    public static function maybe_localize(): void
    {
        $handle = 'ap-event-chat';
        if (!wp_script_is($handle, 'enqueued') && !wp_script_is($handle, 'registered')) {
            return;
        }

        $event_id = 0;
        if (isset($_GET['event_id'])) {
            $event_id = (int) $_GET['event_id'];
        }
        if (!$event_id && is_singular('artpulse_event')) {
            $event_id = (int) get_queried_object_id();
        }

        wp_localize_script(
            $handle,
            'apChat',
            [
                'eventId'  => $event_id,
                'restBase' => esc_url_raw(rest_url(\defined('ARTPULSE_API_NAMESPACE') ? \ARTPULSE_API_NAMESPACE : 'artpulse/v1')),
                'nonce'    => wp_create_nonce('wp_rest'),
            ]
        );
    }
}

