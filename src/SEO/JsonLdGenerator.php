<?php
namespace ArtPulse\SEO;

use WP_Post;

/**
 * Helper to output JSON-LD schema for events and artists.
 */
class JsonLdGenerator
{
    /**
     * Print schema for the current singular post if supported.
     */
    public static function output(): void
    {
        if (!is_singular()) {
            return;
        }

        $post = get_post();
        if (!$post instanceof WP_Post) {
            return;
        }

        if ($post->post_type === 'artpulse_event') {
            $schema = self::event_schema($post);
        } elseif ($post->post_type === 'artpulse_artist') {
            $schema = self::artist_schema($post);
        } elseif ($post->post_type === 'artpulse_artwork') {
            $schema = self::artwork_schema($post);
        } else {
            return;
        }

        echo '<script type="application/ld+json">' .
            wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) .
            '</script>' . "\n";
    }

    /**
     * Build Event schema data.
     */
    private static function event_schema(WP_Post $post): array
    {
        $start   = get_post_meta($post->ID, 'event_start_date', true);
        if (!$start) {
            $start = get_post_meta($post->ID, '_ap_event_date', true);
        }
        $end     = get_post_meta($post->ID, 'event_end_date', true);
        $loc     = get_post_meta($post->ID, '_ap_event_location', true);
        $address = get_post_meta($post->ID, '_ap_event_address', true);
        $desc    = $post->post_excerpt ?: wp_trim_words($post->post_content, 50);

        $image = get_the_post_thumbnail_url($post, 'full');

        $data = [
            '@context'   => 'https://schema.org',
            '@type'      => 'Event',
            'name'       => get_the_title($post),
            'startDate'  => $start,
            'endDate'    => $end ?: $start,
            'url'        => get_permalink($post),
            'description'=> wp_strip_all_tags($desc),
            'image'      => $image,
        ];

        if ($loc || $address) {
            $data['location'] = [
                '@type'   => 'Place',
                'name'    => $loc ?: $address,
                'address' => $address,
            ];
        }

        $org = get_post_meta($post->ID, 'event_organizer_name', true);
        if ($org) {
            $data['organizer'] = [
                '@type' => 'Person',
                'name'  => $org,
            ];
        }

        return array_filter($data);
    }

    /**
     * Build Artist schema data.
     */
    private static function artist_schema(WP_Post $post): array
    {
        $name = get_post_meta($post->ID, 'artist_name', true) ?: get_the_title($post);
        $bio  = get_post_meta($post->ID, 'artist_bio', true);
        if (!$bio) {
            $bio = $post->post_excerpt ?: wp_trim_words($post->post_content, 50);
        }

        $links = [];
        foreach (['artist_website', 'artist_facebook', 'artist_instagram', 'artist_twitter'] as $key) {
            $url = get_post_meta($post->ID, $key, true);
            if ($url) {
                $links[] = esc_url_raw($url);
            }
        }

        $data = [
            '@context'   => 'https://schema.org',
            '@type'      => 'Person',
            'name'       => $name,
            'description'=> wp_strip_all_tags($bio),
            'url'        => get_permalink($post),
        ];

        if ($links) {
            $data['sameAs'] = $links;
        }

        return $data;
    }

    /**
     * Build VisualArtwork schema data.
     */
    private static function artwork_schema(WP_Post $post): array
    {
        $artist_id   = get_post_meta($post->ID, '_ap_artwork_artist', true);
        $artist_name = $artist_id ? get_the_title($artist_id) : '';

        $desc = $post->post_excerpt ?: wp_trim_words($post->post_content, 50);

        $tags = wp_get_post_tags($post->ID, ['fields' => 'names']);

        $image = get_the_post_thumbnail_url($post, 'full');

        $data = [
            '@context'   => 'https://schema.org',
            '@type'      => 'VisualArtwork',
            'name'       => get_the_title($post),
            'image'      => $image,
            'description'=> wp_strip_all_tags($desc),
            'url'        => get_permalink($post),
        ];

        if ($artist_name) {
            $data['creator'] = [
                '@type' => 'Person',
                'name'  => $artist_name,
            ];
        }

        if ($tags) {
            $data['keywords'] = $tags;
        }

        return array_filter($data);
    }
}
