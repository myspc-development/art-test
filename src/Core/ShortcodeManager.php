<?php
namespace ArtPulse\Core;

class ShortcodeManager
{
    public static function register()
    {
        add_shortcode('ap_events',       [ self::class, 'renderEvents' ]);
        add_shortcode('ap_artists',      [ self::class, 'renderArtists' ]);
        add_shortcode('ap_artworks',     [ self::class, 'renderArtworks' ]);
        add_shortcode('ap_organizations',[ self::class, 'renderOrganizations' ]);
        add_shortcode('ap_spotlights',   [ self::class, 'renderSpotlights' ]);
    }

    public static function renderEvents($atts)
    {
        $atts = shortcode_atts(['limit'=>10], $atts, 'ap_events');
        $query = new \WP_Query([
            'post_type'      => 'artpulse_event',
            'posts_per_page' => intval($atts['limit']),
            // Fetch IDs only to reduce memory footprint.
            'fields'         => 'ids',
            // No pagination required so skip FOUND_ROWS calculation.
            'no_found_rows'  => true,
        ]);
        ob_start();
        echo '<div class="ap-portfolio-grid">';
        foreach ($query->posts as $post_id) {
            echo ap_get_event_card($post_id);
        }
        echo '</div>';
        return ob_get_clean();
    }

    public static function renderArtists($atts)
    {
        $atts = shortcode_atts(['limit'=>10], $atts, 'ap_artists');
        $query = new \WP_Query([
            'post_type'      => 'artpulse_artist',
            'posts_per_page' => intval($atts['limit']),
            // Fetch IDs only to reduce memory footprint.
            'fields'         => 'ids',
            // No pagination required so skip FOUND_ROWS calculation.
            'no_found_rows'  => true,
        ]);
        ob_start();
        echo '<div class="ap-portfolio-grid">';
        foreach ($query->posts as $post_id) {
            echo '<div class="portfolio-item">';
            echo get_the_post_thumbnail($post_id, 'medium');
            echo '<h3><a href="' . esc_url(get_permalink($post_id)) . '">' . esc_html(get_the_title($post_id)) . '</a></h3>';
            echo '</div>';
        }
        echo '</div>';
        return ob_get_clean();
    }

    public static function renderArtworks($atts)
    {
        $atts = shortcode_atts(['limit'=>10], $atts, 'ap_artworks');
        $query = new \WP_Query([
            'post_type'      => 'artpulse_artwork',
            'posts_per_page' => intval($atts['limit']),
            // Fetch IDs only to reduce memory footprint.
            'fields'         => 'ids',
            // No pagination required so skip FOUND_ROWS calculation.
            'no_found_rows'  => true,
        ]);
        ob_start();
        echo '<div class="ap-portfolio-grid">';
        foreach ($query->posts as $post_id) {
            echo '<div class="portfolio-item">';
            echo get_the_post_thumbnail($post_id, 'medium');
            echo '<h3><a href="' . esc_url(get_permalink($post_id)) . '">' . esc_html(get_the_title($post_id)) . '</a></h3>';
            echo '</div>';
        }
        echo '</div>';
        return ob_get_clean();
    }

    public static function renderOrganizations($atts)
    {
        $atts = shortcode_atts(['limit'=>10], $atts, 'ap_organizations');
        $query = new \WP_Query([
            'post_type'      => 'artpulse_org',
            'posts_per_page' => intval($atts['limit']),
            // Fetch IDs only to reduce memory footprint.
            'fields'         => 'ids',
            // No pagination required so skip FOUND_ROWS calculation.
            'no_found_rows'  => true,
        ]);
        ob_start();
        echo '<div class="ap-portfolio-grid">';
        foreach ($query->posts as $post_id) {
            echo '<div class="portfolio-item">';
            echo get_the_post_thumbnail($post_id, 'medium');
            echo '<h3><a href="' . esc_url(get_permalink($post_id)) . '">' . esc_html(get_the_title($post_id)) . '</a></h3>';
            echo '</div>';
        }
        echo '</div>';
        return ob_get_clean();
    }

    public static function renderSpotlights($atts)
    {
        $atts = shortcode_atts(['limit'=>5], $atts, 'ap_spotlights');

        $today = current_time('Y-m-d');
        $query = new \WP_Query([
            'post_type'      => 'artpulse_artist',
            'posts_per_page' => intval($atts['limit']),
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'meta_query'     => [
                [ 'key' => 'artist_spotlight', 'value' => '1' ],
                [
                    'key'     => 'spotlight_start_date',
                    'value'   => $today,
                    'compare' => '<=',
                    'type'    => 'DATE',
                ],
                [
                    'relation' => 'OR',
                    [
                        'key'     => 'spotlight_end_date',
                        'value'   => $today,
                        'compare' => '>=',
                        'type'    => 'DATE',
                    ],
                    [ 'key' => 'spotlight_end_date', 'compare' => 'NOT EXISTS' ],
                    [ 'key' => 'spotlight_end_date', 'value' => '', 'compare' => '=' ],
                ],
            ],
        ]);

        ob_start();
        echo '<div class="ap-spotlights">';
        foreach ($query->posts as $post_id) {
            set_query_var('post', get_post($post_id));
            $template_path = plugin_dir_path(__FILE__) . '../../templates/partials/content-artpulse-item.php';
            if (file_exists($template_path)) {
                include $template_path;
            } else {
                printf('<a href="%s">%s</a>', esc_url(get_permalink($post_id)), esc_html(get_the_title($post_id)));
            }
        }
        echo '</div>';
        return ob_get_clean();
    }
}
