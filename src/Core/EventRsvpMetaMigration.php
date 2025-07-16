<?php
namespace ArtPulse\Core;

class EventRsvpMetaMigration
{
    public static function maybe_migrate(): void
    {
        if (get_option('ap_event_rsvp_meta_migrated')) {
            return;
        }

        $posts = get_posts([
            'post_type'      => 'artpulse_event',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => [
                'relation' => 'OR',
                ['key' => 'ap_event_requires_rsvp', 'compare' => 'EXISTS'],
                ['key' => 'ap_event_rsvps', 'compare' => 'EXISTS'],
            ],
        ]);

        foreach ($posts as $post_id) {
            $enabled = get_post_meta($post_id, 'ap_event_requires_rsvp', true);
            if ($enabled !== '' && ! metadata_exists('post', $post_id, 'event_rsvp_enabled')) {
                update_post_meta($post_id, 'event_rsvp_enabled', $enabled);
            }

            $list = get_post_meta($post_id, 'ap_event_rsvps', true);
            if ($list && ! metadata_exists('post', $post_id, 'event_rsvp_list')) {
                update_post_meta($post_id, 'event_rsvp_list', $list);
            }

            delete_post_meta($post_id, 'ap_event_requires_rsvp');
            delete_post_meta($post_id, 'ap_event_rsvps');
        }

        update_option('ap_event_rsvp_meta_migrated', 1);
    }
}
