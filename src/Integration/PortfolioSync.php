<?php
namespace ArtPulse\Integration;

/**
 * Sync artist, artwork and event posts to portfolio entries.
 */
class PortfolioSync
{
    /**
     * Register hooks for syncing portfolio posts.
     */
    public static function register()
    {
        $types = ['artpulse_artist', 'artpulse_artwork', 'artpulse_event'];
        foreach ($types as $type) {
            add_action("save_post_{$type}", [self::class, 'sync_portfolio'], 10, 2);
        }
        add_action('before_delete_post', [self::class, 'delete_portfolio']);
    }

    /**
     * Create or update the portfolio entry when the source post is saved.
     *
     * @param int      $post_id The post ID being saved.
     * @param \WP_Post $post    The post object.
     */
    public static function sync_portfolio($post_id, $post)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (wp_is_post_revision($post_id)) {
            return;
        }

        $existing = get_posts([
            'post_type'   => 'portfolio',
            'meta_key'    => '_ap_source_post',
            'meta_value'  => $post_id,
            'post_status' => 'any',
            'numberposts' => 1,
            'fields'      => 'ids',
        ]);

        if ($existing) {
            $portfolio_id = $existing[0];
            wp_update_post([
                'ID'           => $portfolio_id,
                'post_title'   => $post->post_title,
                'post_content' => $post->post_content,
                'post_status'  => $post->post_status,
            ]);
        } else {
            $portfolio_id = wp_insert_post([
                'post_type'   => 'portfolio',
                'post_title'  => $post->post_title,
                'post_content'=> $post->post_content,
                'post_status' => $post->post_status,
                'post_author' => $post->post_author,
            ]);
            if (!is_wp_error($portfolio_id) && $portfolio_id) {
                update_post_meta($portfolio_id, '_ap_source_post', $post_id);
            }
        }

        if (!empty($portfolio_id) && !is_wp_error($portfolio_id)) {
            $thumb = get_post_thumbnail_id($post_id);
            if ($thumb) {
                set_post_thumbnail($portfolio_id, $thumb);
            } else {
                delete_post_thumbnail($portfolio_id);
            }
        }
    }

    /**
     * Remove the portfolio entry when the source post is deleted.
     *
     * @param int $post_id The source post ID being deleted.
     */
    public static function delete_portfolio($post_id)
    {
        $type = get_post_type($post_id);
        if (!in_array($type, ['artpulse_artist', 'artpulse_artwork', 'artpulse_event'], true)) {
            return;
        }

        $portfolio = get_posts([
            'post_type'   => 'portfolio',
            'meta_key'    => '_ap_source_post',
            'meta_value'  => $post_id,
            'post_status' => 'any',
            'numberposts' => 1,
            'fields'      => 'ids',
        ]);
        if ($portfolio) {
            wp_delete_post($portfolio[0], true);
        }
    }
}
