<?php
namespace ArtPulse\Integration;

use ArtPulse\Core\PortfolioSyncLogger;

/**
 * Sync artist, artwork and event posts to portfolio entries.
 */
class PortfolioSync
{
    /** @var bool */
    private static $syncing = false;
    /**
     * Register hooks for syncing portfolio posts.
     */
    public static function register()
    {
        $types = ['artpulse_artist', 'artpulse_artwork', 'artpulse_event', 'artpulse_org'];
        foreach ($types as $type) {
            add_action("save_post_{$type}", [self::class, 'sync_portfolio'], 10, 2);
        }
        add_action('before_delete_post', [self::class, 'delete_portfolio']);
        add_action('save_post_portfolio', [self::class, 'sync_source'], 10, 2);

        if (defined('WP_CLI') && WP_CLI) {
            \WP_CLI::add_command('ap sync-portfolio', [self::class, 'cli_sync']);
        }
    }

    /**
     * Create or update the portfolio entry when the source post is saved.
     *
     * @param int      $post_id The post ID being saved.
     * @param \WP_Post $post    The post object.
     */
    public static function sync_portfolio($post_id, $post)
    {
        if (self::$syncing || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
            return;
        }
        if (wp_is_post_revision($post_id)) {
            return;
        }

        self::$syncing = true;

        $ids = [];
        if (in_array($post->post_type, ['artpulse_artwork', 'artpulse_event', 'artpulse_org'], true)) {
            $ids = get_post_meta($post_id, '_ap_submission_images', true);
            if (!is_array($ids)) {
                $ids = [];
            }
        }

        $existing = get_posts([
            'post_type'   => 'portfolio',
            'meta_key'    => '_ap_source_post',
            'meta_value'  => $post_id,
            'post_status' => 'any',
            'numberposts' => 1,
            'fields'      => 'ids',
        ]);
        if (count($existing) > 1) {
            PortfolioSyncLogger::log('sync', 'Duplicate portfolio records', ['post' => $post_id, 'ids' => $existing], get_current_user_id());
        }

       if ($existing) {
           $portfolio_id = $existing[0];
           $result = wp_update_post([
               'ID'           => $portfolio_id,
               'post_title'   => $post->post_title,
               'post_content' => $post->post_content,
               'post_status'  => $post->post_status,
           ], true);
           if (is_wp_error($result)) {
               PortfolioSyncLogger::log('sync', 'Update failed', ['post' => $post_id, 'error' => $result->get_error_message()], get_current_user_id());
           }
           update_post_meta($post_id, '_ap_portfolio_id', $portfolio_id);
       } else {
            $portfolio_id = wp_insert_post([
                'post_type'   => 'portfolio',
                'post_title'  => $post->post_title,
                'post_content'=> $post->post_content,
                'post_status' => $post->post_status,
                'post_author' => $post->post_author,
            ], true);
            if (!is_wp_error($portfolio_id) && $portfolio_id) {
                update_post_meta($portfolio_id, '_ap_source_post', $post_id);
                update_post_meta($post_id, '_ap_portfolio_id', $portfolio_id);
                PortfolioSyncLogger::log('sync', 'Created portfolio', ['post' => $post_id, 'portfolio' => $portfolio_id], get_current_user_id());
            } else {
                PortfolioSyncLogger::log('sync', 'Insert failed', ['post' => $post_id, 'error' => is_wp_error($portfolio_id) ? $portfolio_id->get_error_message() : 'unknown'], get_current_user_id());
            }
       }

        if (!empty($portfolio_id) && !is_wp_error($portfolio_id)) {
            update_post_meta($portfolio_id, '_ap_submission_images', $ids);

            // Map plugin content to Salient extra content meta
            if ($post->post_content !== '') {
                update_post_meta($portfolio_id, '_nectar_portfolio_extra_content', $post->post_content);
            }

            // Copy portfolio categories/tags when present
            $cats = wp_get_object_terms($post_id, 'portfolio_category', ['fields' => 'ids']);
            if (!empty($cats) && !is_wp_error($cats)) {
                wp_set_object_terms($portfolio_id, $cats, 'project-category', false);
            }
            $tags = wp_get_object_terms($post_id, 'portfolio_tag', ['fields' => 'ids']);
            if (!empty($tags) && !is_wp_error($tags)) {
                wp_set_object_terms($portfolio_id, $tags, 'project-tag', false);
            }

            if ($ids) {
                set_post_thumbnail($portfolio_id, $ids[0]);
            } else {
                $thumb = get_post_thumbnail_id($post_id);
                if ($thumb) {
                    set_post_thumbnail($portfolio_id, $thumb);
                } else {
                    delete_post_thumbnail($portfolio_id);
                    PortfolioSyncLogger::log('sync', 'Missing images', ['post' => $post_id], get_current_user_id());
                }
            }

            if ($post->post_type === 'artpulse_event') {
                $meta_keys = [
                    '_ap_event_date',
                    '_ap_event_venue',
                    '_ap_event_start_time',
                    '_ap_event_end_time',
                ];
                foreach ($meta_keys as $key) {
                    $val = get_post_meta($post_id, $key, true);
                    if ($val !== '') {
                        update_post_meta($portfolio_id, $key, $val);
                    } else {
                        delete_post_meta($portfolio_id, $key);
                    }
                }
            }

            if ($post->post_type === 'artpulse_org') {
                $org_keys = [
                    'ead_org_logo_id',
                    'ead_org_banner_id',
                    'ead_org_website_url',
                    'ead_org_street_address',
                    'ead_org_type',
                ];
                foreach ($org_keys as $key) {
                    $val = get_post_meta($post_id, $key, true);
                    if ($val !== '') {
                        update_post_meta($portfolio_id, $key, $val);
                    } else {
                        delete_post_meta($portfolio_id, $key);
                    }
                }
            }
            PortfolioSyncLogger::log('sync', 'Synced portfolio', ['post' => $post_id, 'portfolio' => $portfolio_id], get_current_user_id());
            self::$syncing = false;
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
        if (!in_array($type, ['artpulse_artist', 'artpulse_artwork', 'artpulse_event', 'artpulse_org'], true)) {
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

    /**
     * Sync Salient portfolio edits back to the source post.
     */
    public static function sync_source($post_id, $post)
    {
        if (self::$syncing || wp_is_post_revision($post_id)) {
            return;
        }

        $source = (int) get_post_meta($post_id, '_ap_source_post', true);
        if (!$source) {
            return;
        }

        self::$syncing = true;

        $result = wp_update_post([
            'ID'           => $source,
            'post_title'   => $post->post_title,
            'post_content' => $post->post_content,
        ], true);
        if (is_wp_error($result)) {
            PortfolioSyncLogger::log('sync', 'Source update failed', ['portfolio' => $post_id, 'error' => $result->get_error_message()], get_current_user_id());
        }

        update_post_meta($source, '_ap_portfolio_id', $post_id);

        $ids = get_post_meta($post_id, '_ap_submission_images', true);
        if (is_array($ids)) {
            update_post_meta($source, '_ap_submission_images', $ids);
        }

        PortfolioSyncLogger::log('sync', 'Synced source', ['portfolio' => $post_id, 'source' => $source], get_current_user_id());
        self::$syncing = false;
    }

    /**
     * Run a full synchronization of all source posts.
     */
    public static function sync_all(): int
    {
        $types = ['artpulse_artist', 'artpulse_artwork', 'artpulse_event', 'artpulse_org'];
        $count = 0;
        foreach ($types as $type) {
            $posts = get_posts([
                'post_type'      => $type,
                'posts_per_page' => -1,
                'post_status'    => 'any',
                'fields'         => 'ids',
            ]);
            foreach ($posts as $id) {
                $post = get_post($id);
                if ($post) {
                    self::sync_portfolio($id, $post);
                    $count++;
                }
            }
        }
        PortfolioSyncLogger::log('sync', 'Bulk sync complete', ['count' => $count], get_current_user_id());
        return $count;
    }

    public static function cli_sync()
    {
        if (!function_exists('WP_CLI')) {
            return;
        }
        if (!\wp_get_current_user()->has_cap('manage_options')) {
            \WP_CLI::error('Insufficient permissions.');
        }
        $count = self::sync_all();
        ap_clear_portfolio_cache();
        \WP_CLI::success("Synced {$count} items.");
    }
}
