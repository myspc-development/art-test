<?php
namespace ArtPulse\Community;

class NotificationHooks {
    /**
     * Register all action/event hooks.
     */
    public static function register() {
        // 🔔 Notify post authors of new approved comments
        add_action('comment_post', [self::class, 'notify_on_comment'], 10, 3);

        // 🔔 Membership changes (upgrade/downgrade/expired)
        add_action('ap_membership_level_changed', [self::class, 'notify_on_membership_change'], 10, 4);

        // 🔔 Membership payment events
        add_action('ap_membership_payment', [self::class, 'notify_on_payment'], 10, 4);

        // 🔔 RSVP added to an event
        add_action('ap_event_rsvp_added', [self::class, 'notify_on_rsvp'], 10, 2);

        // 🔔 Event status transitions
        add_action('transition_post_status', [self::class, 'notify_on_event_status'], 10, 3);
    }

    /**
     * Notify post author of a new comment.
     */
    public static function notify_on_comment($comment_ID, $comment_approved, $commentdata) {
        if ($comment_approved != 1) return;

        $post = get_post($commentdata['comment_post_ID']);
        if (!$post) return;

        $author_id = $post->post_author;
        $commenter_id = intval($commentdata['user_id']);

        if ($author_id && $author_id !== $commenter_id) {
            NotificationManager::add(
                $author_id,
                'comment',
                $post->ID,
                $commenter_id,
                sprintf('New comment on your post "%s".', $post->post_title)
            );
        }
    }

    /**
     * Notify user on membership level change.
     */
    public static function notify_on_membership_change($user_id, $old_level, $new_level, $change_type) {
        NotificationManager::add(
            $user_id,
            'membership_' . $change_type,
            null,
            null,
            sprintf(
                'Your membership was %s: %s → %s.',
                esc_html($change_type),
                esc_html($old_level),
                esc_html($new_level)
            )
        );
    }

    /**
     * Notify user of payment-related events.
     */
    public static function notify_on_payment($user_id, $amount, $currency, $event_type) {
        $amount_display = number_format_i18n($amount, 2) . ' ' . strtoupper($currency);
        NotificationManager::add(
            $user_id,
            'payment_' . $event_type,
            null,
            null,
            sprintf('Payment %s: %s.', esc_html($event_type), esc_html($amount_display))
        );
    }

    /**
     * Notify event organizer when an RSVP is added.
     */
    public static function notify_on_rsvp($event_id, $rsvping_user_id) {
        $event = get_post($event_id);
        if (!$event || $event->post_type !== 'artpulse_event') {
            return;
        }

        $organizer_id = intval($event->post_author);
        if (!$organizer_id || $organizer_id === intval($rsvping_user_id)) {
            return;
        }

        NotificationManager::add(
            $organizer_id,
            'rsvp_received',
            $event_id,
            $rsvping_user_id
        );
    }

    /**
     * Notify event organizer when an event status changes.
     */
    public static function notify_on_event_status($new_status, $old_status, $post) {
        if ($post->post_type !== 'artpulse_event') {
            return;
        }

        if ($old_status === 'pending' && $new_status === 'publish') {
            NotificationManager::add(
                $post->post_author,
                'event_approved',
                $post->ID
            );
        } elseif ($old_status === 'pending' && in_array($new_status, ['trash', 'rejected'], true)) {
            NotificationManager::add(
                $post->post_author,
                'event_rejected',
                $post->ID
            );
        }
    }
}
