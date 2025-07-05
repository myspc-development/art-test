<?php
namespace ArtPulse\Frontend;

class EventRsvpHandler
{
    public static function register(): void
    {
        add_action('admin_post_ap_rsvp_event', [self::class, 'handle']);
        add_action('admin_post_nopriv_ap_rsvp_event', [self::class, 'handle']);
    }

    public static function handle(): void
    {
        if (!is_user_logged_in()) {
            return;
        }

        $event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
        if (!$event_id) {
            return;
        }

        $enabled = get_post_meta($event_id, 'ap_event_requires_rsvp', true);
        if (!$enabled) {
            wp_safe_redirect(get_permalink($event_id));
            exit;
        }

        $user_id = get_current_user_id();
        $existing = get_post_meta($event_id, 'ap_event_rsvps', true);
        if (!is_array($existing)) {
            $existing = [];
        }
        if (!in_array($user_id, $existing, true)) {
            $existing[] = $user_id;
            update_post_meta($event_id, 'ap_event_rsvps', $existing);
        }

        wp_safe_redirect(get_permalink($event_id));
        exit;
    }
}
