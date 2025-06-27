<?php
namespace ArtPulse\Core;

class EventViewCounter
{
    public static function register(): void
    {
        add_action('wp', [self::class, 'track']);
    }

    public static function track(): void
    {
        if (!is_singular('artpulse_event')) {
            return;
        }
        global $post;
        if (!$post) {
            return;
        }
        $count = (int) get_post_meta($post->ID, 'view_count', true);
        update_post_meta($post->ID, 'view_count', $count + 1);
    }
}
