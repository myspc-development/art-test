<?php
namespace ArtPulse\Core;

use ArtPulse\Core\EventMetrics;

class EventViewCounter
{
    public static function register(): void
    {
        add_action('wp', [self::class, 'track']);
        add_action('ap_favorite_added', [self::class, 'track_favorite'], 10, 3);
        add_action('ap_event_shared', [self::class, 'track_share'], 10, 2);
    }

    public static function track(): void
    {
        if (!is_singular('artpulse_event') && !is_singular('artpulse_artist')) {
            return;
        }

        global $post;
        if (!$post) {
            return;
        }

        $count = (int) get_post_meta($post->ID, 'view_count', true);
        update_post_meta($post->ID, 'view_count', $count + 1);

        if (class_exists('\\ArtPulse\\Personalization\\RecommendationEngine')) {
            $type = is_singular('artpulse_artist') ? 'artist' : 'event';
            $uid  = get_current_user_id();
            \ArtPulse\Personalization\RecommendationEngine::log($uid, $type, $post->ID, 'view');
        }

        if (is_singular('artpulse_event')) {
            EventMetrics::log_metric($post->ID, 'view');
        }
    }

    public static function track_favorite(int $user_id, int $object_id, string $object_type): void
    {
        if ($object_type === 'artpulse_event') {
            EventMetrics::log_metric($object_id, 'favorite');
        }
    }

    public static function track_share(int $event_id): void
    {
        EventMetrics::log_metric($event_id, 'share');

        $post = get_post($event_id);
        if ($post && in_array($post->post_type, ['artpulse_event', 'artpulse_artwork'], true)) {
            global $wpdb;
            $meta_key = 'share_count';
            $updated = $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$wpdb->postmeta} SET meta_value = GREATEST(CAST(meta_value AS SIGNED) + 1, 0) WHERE post_id = %d AND meta_key = %s",
                    $event_id,
                    $meta_key
                )
            );
            if (!$updated) {
                add_post_meta($event_id, $meta_key, 1, true);
            }
        }
    }
}
