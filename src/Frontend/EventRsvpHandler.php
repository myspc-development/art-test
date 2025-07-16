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

        if (!check_admin_referer('ap_rsvp_event')) {
            wp_die(__('Invalid nonce', 'artpulse'));
        }

        $event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
        if (!$event_id) {
            return;
        }

        $enabled = get_post_meta($event_id, 'event_rsvp_enabled', true);
        if (!$enabled) {
            wp_safe_redirect(get_permalink($event_id));
            exit;
        }

        $user_id = get_current_user_id();
        $existing = get_post_meta($event_id, 'event_rsvp_list', true);
        if (!is_array($existing)) {
            $existing = [];
        }

        if (!in_array($user_id, $existing, true)) {
            $existing[] = $user_id;
            update_post_meta($event_id, 'event_rsvp_list', $existing);
            do_action('ap_event_rsvp_added', $event_id, get_current_user_id());
        }

        wp_safe_redirect(get_permalink($event_id));
        exit;
    }

    public static function get_rsvp_summary_for_user($user_id): array
    {
        $event_ids = get_user_meta($user_id, 'ap_rsvp_events', true);
        if (!is_array($event_ids)) {
            $event_ids = [];
        }

        $going      = 0;
        $interested = 0;
        $trend_map  = [];

        $today = strtotime('today');

        foreach ($event_ids as $eid) {
            $date = get_post_meta($eid, '_ap_event_date', true);
            $ts   = $date ? strtotime($date) : false;
            if ($ts !== false && $ts >= $today) {
                $going++;
            } else {
                $interested++;
            }

            $history = get_post_meta($eid, 'event_rsvp_history', true);
            if (is_array($history)) {
                foreach ($history as $day => $count) {
                    if (!isset($trend_map[$day])) {
                        $trend_map[$day] = 0;
                    }
                    $trend_map[$day] += (int) $count;
                }
            }
        }

        ksort($trend_map);
        $trend = [];
        foreach ($trend_map as $day => $count) {
            $trend[] = ['date' => $day, 'count' => $count];
        }

        return [
            'going'      => $going,
            'interested' => $interested,
            'trend'      => $trend,
        ];
    }
}
