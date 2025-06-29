<?php

namespace ArtPulse\Frontend;

class MyEventsShortcode {

    public static function register() {
        add_shortcode('ap_my_events', [self::class, 'render']);
        add_action('init', [self::class, 'handle_deletion']);
    }

    public static function render($atts) {
        if (!is_user_logged_in()) {
            return '<p>You must be logged in to view your submitted events.</p>';
        }

        $current_user_id = get_current_user_id();

        $args = [
            'post_type'      => 'artpulse_event',
            'author'         => $current_user_id,
            'post_status'    => ['publish', 'pending', 'draft'],
            'posts_per_page' => 10,
        ];

        $events = get_posts($args);

        if (empty($events)) {
            return '<p>You haven’t submitted any events yet.</p>';
        }

        ob_start();
        echo '<div class="ap-my-events-list">';
        foreach ($events as $event) {
            $status   = ucfirst($event->post_status);
            $edit_url = get_edit_post_link($event->ID);
            $delete_url = add_query_arg([
                'ap_delete_event' => $event->ID,
                'ap_nonce'        => wp_create_nonce('ap_delete_event_' . $event->ID),
            ], get_permalink());

            echo '<div class="ap-my-event">';
            echo ap_get_event_card($event->ID);
            echo '<div class="ap-event-actions">';
            echo '<a href="' . esc_url($edit_url) . '" class="ap-edit-link">Edit</a> | ';
            echo '<a href="' . esc_url($delete_url) . '" class="ap-delete-link" onclick="return confirm(\'Are you sure you want to delete this event?\');">Delete</a>';
            echo ' <span class="ap-status">(' . esc_html($status) . ')</span>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
        return ob_get_clean();
    }

    public static function handle_deletion() {
        if (
            !is_user_logged_in() ||
            !isset($_GET['ap_delete_event']) ||
            !isset($_GET['ap_nonce'])
        ) {
            return;
        }

        $event_id = absint($_GET['ap_delete_event']);
        $nonce = sanitize_text_field($_GET['ap_nonce']);

        if (!wp_verify_nonce($nonce, 'ap_delete_event_' . $event_id)) {
            return;
        }

        $event = get_post($event_id);
        if ($event && $event->post_type === 'artpulse_event' && $event->post_author == get_current_user_id()) {
            wp_trash_post($event_id);
            wp_safe_redirect(remove_query_arg(['ap_delete_event', 'ap_nonce']));
            exit;
        }
    }
}
