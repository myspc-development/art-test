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
        add_action('save_post',         [ self::class, 'clear_cache' ], 10, 3);
        add_action('deleted_post',      [ self::class, 'clear_cache' ]);
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
            'permission_callback' => function() {
                if (!current_user_can('read')) {
                    return new \WP_Error('rest_forbidden', __('Unauthorized.', 'artpulse'), ['status' => 403]);
                }
                return true;
            },
            'args' => [
                'type'       => [ 'type' => 'string',  'required' => true ],
                'limit'      => [ 'type' => 'integer', 'default'  => 10 ],
                'event_type' => [ 'type' => 'integer' ],
                'medium'     => [ 'type' => 'integer' ],
                'style'      => [ 'type' => 'integer' ],
                'org_type'   => [ 'type' => 'string' ],
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
        $org_type   = sanitize_text_field( $request->get_param('org_type') );
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
            'org_type'   => $org_type,
            'location'   => $location,
            'city'       => $city,
            'region'     => $region,
            'for_sale'   => $for_sale,
            'keyword'    => $keyword,
        ];

        $cache_key = self::get_cache_key( array_merge( [ 'type' => $type ], $search_args ) );
        $cached_ids = get_transient( $cache_key );

        if ( $cached_ids !== false ) {
            $posts = get_posts( [
                'post_type'      => 'artpulse_' . $type,
                'post__in'       => $cached_ids,
                'orderby'        => 'post__in',
                'posts_per_page' => $limit,
            ] );
        } elseif ( ExternalSearch::is_enabled() ) {
            $posts = ExternalSearch::search( $type, $search_args );
            set_transient( $cache_key, wp_list_pluck( $posts, 'ID' ), 5 * MINUTE_IN_SECONDS );
        } else {
            if ( $type === 'event' ) {
                if ( $event_type ) {
                    $tax_query[] = [
                        'taxonomy' => 'event_type',
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
                    'taxonomy' => $type === 'artist' ? 'artist_specialty' : 'artpulse_medium',
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

            if ( $type === 'org' && $org_type ) {
                $meta_query[] = [
                    'key'   => 'ead_org_type',
                    'value' => $org_type,
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
            set_transient( $cache_key, wp_list_pluck( $posts, 'ID' ), 5 * MINUTE_IN_SECONDS );
        }

        $data = array_map(function($p) use ($type) {
            $featured = get_the_post_thumbnail_url($p, 'medium');
            if ($type === 'org' && empty($featured)) {
                $logo_id   = get_post_meta($p->ID, 'ead_org_logo_id', true);
                $banner_id = get_post_meta($p->ID, 'ead_org_banner_id', true);
                $attachment_id = $logo_id ?: $banner_id;
                if ($attachment_id) {
                    $featured = wp_get_attachment_image_url($attachment_id, 'medium');
                }
            }

            $item = [
                'id'      => $p->ID,
                'title'   => $p->post_title,
                'link'    => get_permalink($p),
                'featured_media_url' => $featured,
            ];
            if ($type === 'event') {
                $item['date']     = get_post_meta($p->ID, '_ap_event_date', true);
                $item['location'] = get_post_meta($p->ID, '_ap_event_location', true);
                $item['card_html'] = ap_get_event_card($p->ID);
            } elseif ($type === 'artist') {
                $item['bio']    = get_post_meta($p->ID, '_ap_artist_bio', true);
                $item['org_id'] = (int) get_post_meta($p->ID, '_ap_artist_org', true);
                $medium_terms   = get_the_terms($p->ID, 'artist_specialty');
                $style_terms    = get_the_terms($p->ID, 'artwork_style');
                $item['medium'] = $medium_terms && !is_wp_error($medium_terms) ? wp_list_pluck($medium_terms, 'name') : [];
                $item['style']  = $style_terms && !is_wp_error($style_terms) ? wp_list_pluck($style_terms, 'name') : [];
            } elseif ($type === 'artwork') {
                $item['medium']     = get_post_meta($p->ID, '_ap_artwork_medium', true);
                $terms = get_the_terms($p->ID, 'artwork_style');
                if ($terms && !is_wp_error($terms)) {
                    $names = wp_list_pluck($terms, 'name');
                    $item['style'] = implode(', ', $names);
                } else {
                    $item['style'] = '';
                }
                $item['dimensions'] = get_post_meta($p->ID, '_ap_artwork_dimensions', true);
                $item['materials']  = get_post_meta($p->ID, '_ap_artwork_materials', true);
                $item['for_sale']   = (bool) get_post_meta($p->ID, 'for_sale', true);
                $item['price']      = get_post_meta($p->ID, 'price', true);
            } elseif ($type === 'org') {
                $item['address'] = get_post_meta($p->ID, 'ead_org_street_address', true);
                $item['website'] = get_post_meta($p->ID, 'ead_org_website_url', true);
                $item['org_type'] = get_post_meta($p->ID, 'ead_org_type', true);
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
            <form class="ap-directory-filters" aria-controls="ap-directory-results">
                <?php if ($atts['type'] === 'event'): ?>
                    <label for="ap-filter-event-type"><?php _e('Filter by Event Type','artpulse'); ?>:</label>
                    <select id="ap-filter-event-type" class="ap-filter-event-type"></select>
                    <label for="ap-filter-city" class="screen-reader-text"><?php esc_html_e('City','artpulse'); ?></label>
                    <input id="ap-filter-city" type="text" class="ap-filter-city" placeholder="<?php esc_attr_e('City','artpulse'); ?>" />
                    <label for="ap-filter-region" class="screen-reader-text"><?php esc_html_e('Region','artpulse'); ?></label>
                    <input id="ap-filter-region" type="text" class="ap-filter-region" placeholder="<?php esc_attr_e('Region','artpulse'); ?>" />
                <?php endif; ?>
                <?php if ($atts['type'] === 'org'): ?>
                    <label for="ap-filter-org-type"><?php _e('Organization Type','artpulse'); ?>:</label>
                    <select id="ap-filter-org-type" class="ap-filter-org-type">
                        <option value=""><?php esc_html_e('All','artpulse'); ?></option>
                        <?php
                        foreach (['gallery','museum','art-fair','studio','collective','non-profit','commercial-gallery','public-art-space','educational-institution','other'] as $t) {
                            echo '<option value="' . esc_attr($t) . '">' . esc_html(ucfirst(str_replace('-', ' ', $t))) . '</option>';
                        }
                        ?>
                    </select>
                <?php else: ?>
                    <label for="ap-filter-medium"><?php _e('Medium','artpulse'); ?>:</label>
                    <select id="ap-filter-medium" class="ap-filter-medium"></select>
                    <label for="ap-filter-style"><?php _e('Style','artpulse'); ?>:</label>
                    <select id="ap-filter-style" class="ap-filter-style"></select>
                <?php endif; ?>
                <label for="ap-filter-location" class="screen-reader-text"><?php esc_html_e('Location','artpulse'); ?></label>
                <input id="ap-filter-location" type="text" class="ap-filter-location" placeholder="<?php esc_attr_e('Location','artpulse'); ?>" />
                <label for="ap-filter-keyword" class="screen-reader-text"><?php esc_html_e('Keyword','artpulse'); ?></label>
                <input id="ap-filter-keyword" type="text" class="ap-filter-keyword" placeholder="<?php esc_attr_e('Keyword','artpulse'); ?>" />
                <label for="ap-filter-limit"><?php _e('Limit','artpulse'); ?>:</label>
                <input id="ap-filter-limit" type="number" class="ap-filter-limit" value="<?php echo esc_attr($atts['limit']); ?>" />
                <button class="ap-filter-apply"><?php _e('Apply','artpulse'); ?></button>
            </form>
            <div id="ap-directory-results" class="ap-directory-results" role="status" aria-live="polite"></div>
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

    /**
     * Generate a cache key for filter requests.
     *
     * @param array<string,mixed> $params Request parameters.
     * @return string
     */
    public static function get_cache_key(array $params): string
    {
        ksort($params);
        return 'ap_dir_' . md5(serialize($params));
    }

    /**
     * Clear all directory filter transients when a related post is updated.
     */
    public static function clear_cache(int $post_id, ?\WP_Post $post = null, bool $update = true): void
    {
        if (wp_is_post_revision($post_id)) {
            return;
        }

        if (!$post) {
            $post = get_post($post_id);
        }

        if ($post && in_array($post->post_type, ['artpulse_event', 'artpulse_artist', 'artpulse_artwork', 'artpulse_org'], true)) {
            global $wpdb;
            $like = $wpdb->esc_like('_transient_ap_dir_') . '%';
            $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $like));
            $like = $wpdb->esc_like('_transient_timeout_ap_dir_') . '%';
            $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $like));
        }
    }
}
