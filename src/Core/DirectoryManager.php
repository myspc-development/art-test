<?php
namespace ArtPulse\Core;

use WP_REST_Request;
use ArtPulse\Search\ExternalSearch;

class DirectoryManager {
    public static function register() {
        add_shortcode('ap_directory',   [ self::class, 'renderDirectory' ]);
        add_shortcode('ap_event_directory',  [ self::class, 'renderEventDirectory' ]);
        add_shortcode('ap_artist_directory', [ self::class, 'renderArtistDirectory' ]);
        add_shortcode('ap_artwork_directory',[ self::class, 'renderArtworkDirectory' ]);
        add_shortcode('ap_org_directory',    [ self::class, 'renderOrgDirectory' ]);
        add_action('wp_enqueue_scripts',[ self::class, 'enqueueAssets'  ]);
        add_action('rest_api_init',     [ self::class, 'register_routes' ]);
    }

    public static function enqueueAssets() {
        wp_enqueue_script(
            'ap-directory-js',
            plugins_url('assets/js/ap-directory.js', ARTPULSE_PLUGIN_FILE),
            ['wp-api-fetch'],
            '1.0.0',
            true
        );
        wp_enqueue_script(
            'ap-analytics-js',
            plugins_url('assets/js/ap-analytics.js', ARTPULSE_PLUGIN_FILE),
            ['ap-directory-js'],
            '1.0.0',
            true
        );
        wp_localize_script('ap-directory-js', 'ArtPulseApi', [
            'root'  => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
        if (function_exists('ap_enqueue_global_styles')) {
            add_filter('ap_bypass_shortcode_detection', '__return_true');
            ap_enqueue_global_styles();
        }
    }

    public static function register_routes() {
        register_rest_route('artpulse/v1', '/filter', [
            'methods'             => 'GET',
            'callback'            => [ self::class, 'handleFilter' ],
            'permission_callback' => '__return_true',
            'args' => [
                'type'       => [ 'type' => 'string',  'required' => true ],
                'limit'      => [ 'type' => 'integer', 'default'  => 10 ],
                'event_type' => [ 'type' => 'integer' ],
                'medium'     => [ 'type' => 'integer' ],
                'style'      => [ 'type' => 'integer' ],
                'location'   => [ 'type' => 'string' ],
                'city'       => [ 'type' => 'string' ],
                'region'     => [ 'type' => 'string' ],
                'for_sale'   => [ 'type' => 'boolean' ],
                'keyword'    => [ 'type' => 'string' ],
            ]
        ]);
    }

    public static function handleFilter(WP_REST_Request $request) {
        $type       = sanitize_text_field( $request->get_param('type') );
        $limit      = intval( $request->get_param('limit') ?? 10 );
        $event_type = absint( $request->get_param('event_type') );
        $medium     = absint( $request->get_param('medium') );
        $style      = absint( $request->get_param('style') );
        $location   = sanitize_text_field( $request->get_param('location') );
        $city       = sanitize_text_field( $request->get_param('city') );
        $region     = sanitize_text_field( $request->get_param('region') );
        $for_sale   = $request->has_param('for_sale') ? rest_sanitize_boolean( $request->get_param('for_sale') ) : null;
        $keyword    = sanitize_text_field( $request->get_param('keyword') );

        $allowed = ['event', 'artist', 'artwork', 'org'];
        if (!in_array($type, $allowed, true)) {
            return new \WP_Error('invalid_type', 'Invalid directory type', [ 'status' => 400 ]);
        }

        $args       = [
            'post_type'      => 'artpulse_' . $type,
            'posts_per_page' => $limit,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ];
        $tax_query  = [];
        $meta_query = [];

        $search_args = [
            'limit'      => $limit,
            'event_type' => $event_type,
            'medium'     => $medium,
            'style'      => $style,
            'location'   => $location,
            'city'       => $city,
            'region'     => $region,
            'for_sale'   => $for_sale,
            'keyword'    => $keyword,
        ];

        if ( ExternalSearch::is_enabled() ) {
            $posts = ExternalSearch::search( $type, $search_args );
        } else {
            if ( $type === 'event' ) {
                if ( $event_type ) {
                    $tax_query[] = [
                        'taxonomy' => 'artpulse_event_type',
                        'field'    => 'term_id',
                        'terms'    => $event_type,
                    ];
                }

                if ( $city ) {
                    $meta_query[] = [ 'key' => 'event_city',  'value' => $city ];
                }

                if ( $region ) {
                    $meta_query[] = [ 'key' => 'event_state', 'value' => $region ];
                }
            }

            if ( $type === 'artwork' && $for_sale !== null ) {
                $meta_query[] = [
                    'key'     => 'for_sale',
                    'value'   => $for_sale ? '1' : '0',
                    'compare' => '=',
                ];
            }

            if ( $medium ) {
                $tax_query[] = [
                    'taxonomy' => 'artpulse_medium',
                    'field'    => 'term_id',
                    'terms'    => $medium,
                ];
            }

            if ( $style ) {
                $tax_query[] = [
                    'taxonomy' => 'artwork_style',
                    'field'    => 'term_id',
                    'terms'    => $style,
                ];
            }

            if ( $location ) {
                $meta_query[] = [
                    'key'     => 'address_components',
                    'value'   => $location,
                    'compare' => 'LIKE',
                ];
            }

            if ( $keyword ) {
                $args['s'] = $keyword;
            }

            if ( ! empty( $tax_query ) ) {
                $args['tax_query'] = $tax_query;
            }

            if ( ! empty( $meta_query ) ) {
                $args['meta_query'] = $meta_query;
            }

            $posts = get_posts( $args );
        }

        $data = array_map(function($p) use ($type) {
            $item = [
                'id'      => $p->ID,
                'title'   => $p->post_title,
                'link'    => get_permalink($p),
                'featured_media_url' => get_the_post_thumbnail_url($p, 'medium'),
            ];
            if ($type === 'event') {
                $item['date']     = get_post_meta($p->ID, '_ap_event_date', true);
                $item['location'] = get_post_meta($p->ID, '_ap_event_location', true);
                $item['card_html'] = ap_get_event_card($p->ID);
            } elseif ($type === 'artist') {
                $item['bio']    = get_post_meta($p->ID, '_ap_artist_bio', true);
                $item['org_id'] = (int) get_post_meta($p->ID, '_ap_artist_org', true);
            } elseif ($type === 'artwork') {
                $item['medium']     = get_post_meta($p->ID, '_ap_artwork_medium', true);
                $item['dimensions'] = get_post_meta($p->ID, '_ap_artwork_dimensions', true);
                $item['materials']  = get_post_meta($p->ID, '_ap_artwork_materials', true);
                $item['for_sale']   = (bool) get_post_meta($p->ID, 'for_sale', true);
                $item['price']      = get_post_meta($p->ID, 'price', true);
            } elseif ($type === 'org') {
                $item['address'] = get_post_meta($p->ID, 'ead_org_street_address', true);
                $item['website'] = get_post_meta($p->ID, 'ead_org_website_url', true);
            }
            return $item;
        }, $posts);

        return rest_ensure_response($data);
    }

    public static function renderDirectory($atts) {
        $atts = shortcode_atts([
            'type'  => 'event',
            'limit' => 10,
        ], $atts, 'ap_directory');

        ob_start(); ?>
        <div class="ap-directory" data-type="<?php echo esc_attr($atts['type']); ?>" data-limit="<?php echo esc_attr($atts['limit']); ?>">
            <div class="ap-directory-filters">
                <?php if ($atts['type'] === 'event'): ?>
                    <label><?php _e('Filter by Event Type','artpulse'); ?>:</label>
                    <select class="ap-filter-event-type"></select>
                    <input type="text" class="ap-filter-city" placeholder="<?php esc_attr_e('City','artpulse'); ?>" />
                    <input type="text" class="ap-filter-region" placeholder="<?php esc_attr_e('Region','artpulse'); ?>" />
                <?php endif; ?>
                <label><?php _e('Medium','artpulse'); ?>:</label>
                <select class="ap-filter-medium"></select>
                <label><?php _e('Style','artpulse'); ?>:</label>
                <select class="ap-filter-style"></select>
                <input type="text" class="ap-filter-location" placeholder="<?php esc_attr_e('Location','artpulse'); ?>" />
                <input type="text" class="ap-filter-keyword" placeholder="<?php esc_attr_e('Keyword','artpulse'); ?>" />
                <label><?php _e('Limit','artpulse'); ?>:</label>
                <input type="number" class="ap-filter-limit" value="<?php echo esc_attr($atts['limit']); ?>" />
                <button class="ap-filter-apply"><?php _e('Apply','artpulse'); ?></button>
            </div>
            <div class="ap-directory-results" role="status" aria-live="polite"></div>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function renderEventDirectory($atts) {
        $atts['type'] = 'event';
        return self::renderDirectory($atts);
    }

    public static function renderArtistDirectory($atts) {
        $atts['type'] = 'artist';
        return self::renderDirectory($atts);
    }

    public static function renderArtworkDirectory($atts) {
        $atts['type'] = 'artwork';
        return self::renderDirectory($atts);
    }

    public static function renderOrgDirectory($atts) {
        $atts['type'] = 'org';
        return self::renderDirectory($atts);
    }
}
