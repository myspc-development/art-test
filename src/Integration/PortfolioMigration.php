<?php
namespace ArtPulse\Integration;

use WP_CLI;

/**
 * Simple WP-CLI command to migrate existing plugin portfolio items
 * to Salient portfolio posts.
 */
class PortfolioMigration
{
    public static function register()
    {
        if (defined('WP_CLI') && WP_CLI) {
            WP_CLI::add_command('ap migrate-portfolio', [self::class, 'migrate']);
        }
    }

    /**
     * Copy artpulse_portfolio posts into Salient portfolio CPT.
     *
     * @wp-cli-command migrate-portfolio
     */
    public static function migrate()
    {
        $posts = get_posts([
            'post_type'      => 'artpulse_portfolio',
            'posts_per_page' => -1,
            'post_status'    => 'any',
        ]);

        foreach ($posts as $post) {
            $exists = get_posts([
                'post_type'   => 'portfolio',
                'meta_key'    => '_ap_source_post',
                'meta_value'  => $post->ID,
                'post_status' => 'any',
                'numberposts' => 1,
                'fields'      => 'ids',
            ]);

            if ($exists) {
                continue;
            }

            $portfolio_id = wp_insert_post([
                'post_type'   => 'portfolio',
                'post_title'  => $post->post_title,
                'post_content'=> $post->post_content,
                'post_status' => $post->post_status,
                'post_author' => $post->post_author,
            ]);
            if (is_wp_error($portfolio_id)) {
                WP_CLI::warning("Failed to insert portfolio for {$post->ID}");
                continue;
            }

            update_post_meta($portfolio_id, '_ap_source_post', $post->ID);
            update_post_meta($post->ID, '_ap_portfolio_id', $portfolio_id);
        }

        WP_CLI::success('Portfolio migration complete.');
    }
}

