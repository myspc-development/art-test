<?php
namespace ArtPulse\Frontend;

class OrgRsvpDashboard
{
    public static function register(): void
    {
        add_shortcode('ap_org_rsvp_dashboard', [self::class, 'render']);
    }

    public static function render(): string
    {
        if (!is_user_logged_in()) {
            return '';
        }
        $user_id = get_current_user_id();
        $events = get_posts([
            'post_type'      => 'artpulse_event',
            'post_status'    => ['publish','pending'],
            'author'         => $user_id,
            'meta_key'       => 'event_rsvp_enabled',
            'meta_value'     => '1',
            'posts_per_page' => -1
        ]);

        ob_start();
        foreach ($events as $event) {
            echo '<h3>' . esc_html($event->post_title) . '</h3><ul>';
            $rsvps = get_post_meta($event->ID, 'event_rsvp_list', true);
            if (!is_array($rsvps) || empty($rsvps)) {
                echo '<li>' . esc_html__('No RSVPs yet', 'artpulse') . '</li>';
            } else {
                foreach ($rsvps as $uid) {
                    $user = get_userdata($uid);
                    if ($user) {
                        echo '<li>' . esc_html($user->display_name) . ' (' . esc_html($user->user_email) . ')</li>';
                    }
                }
            }
            echo '</ul><hr>';
        }
        return ob_get_clean();
    }
}
