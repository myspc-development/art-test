<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_Error;

class EventListController
{
    public static function register(): void
    {
        if (did_action('rest_api_init')) {
            self::register_routes();
        } else {
            add_action('rest_api_init', [self::class, 'register_routes']);
        }
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/event-list', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_list'],
            'permission_callback' => '__return_true',
            'args'                => [
                'venue'      => [ 'type' => 'string' ],
                'after'      => [ 'type' => 'string' ],
                'before'     => [ 'type' => 'string' ],
                'category'   => [ 'type' => 'string' ],
                'event_type' => [ 'type' => 'string' ],
                'organizer'  => [ 'type' => 'string' ],
                'price_type' => [ 'type' => 'string' ],
                'lat'        => [ 'type' => 'number' ],
                'lng'        => [ 'type' => 'number' ],
                'radius'     => [ 'type' => 'number', 'default' => 50 ],
                'alpha'      => [ 'type' => 'string' ],
                'sort'       => [ 'type' => 'string', 'default' => 'soonest' ],
                'per_page'   => [ 'type' => 'integer', 'default' => 12 ],
            ],
        ]);
    }

    public static function get_list(WP_REST_Request $request)
    {
        $meta_query = [];
        $venue      = $request->get_param('venue');
        $after      = $request->get_param('after');
        $before     = $request->get_param('before');
        $organizer  = $request->get_param('organizer');
        $price_type = $request->get_param('price_type');
        $lat        = $request->get_param('lat');
        $lng        = $request->get_param('lng');
        $radius     = $request->get_param('radius');
        $alpha      = $request->get_param('alpha');
        if ($venue !== null && $venue !== '') {
            $meta_query[] = [
                'key'     => 'venue_name',
                'value'   => sanitize_text_field($venue),
                'compare' => 'LIKE',
            ];
        }
        if ($after) {
            $meta_query[] = [
                'key'     => 'event_start_date',
                'value'   => sanitize_text_field($after),
                'compare' => '>=',
                'type'    => 'DATE',
            ];
        }
        if ($before) {
            $meta_query[] = [
                'key'     => 'event_end_date',
                'value'   => sanitize_text_field($before),
                'compare' => '<=',
                'type'    => 'DATE',
            ];
        }
        if ($organizer) {
            $meta_query[] = [
                'key'     => 'event_organizer_name',
                'value'   => sanitize_text_field($organizer),
                'compare' => 'LIKE',
            ];
        }
        if ($price_type) {
            $meta_query[] = [
                'key'   => 'price_type',
                'value' => sanitize_text_field($price_type),
            ];
        }
        if (is_numeric($lat) && is_numeric($lng) && is_numeric($radius)) {
            $lat = floatval($lat);
            $lng = floatval($lng);
            $r   = floatval($radius) / 111.0;
            $meta_query[] = [
                'key'     => 'event_lat',
                'value'   => [ $lat - $r, $lat + $r ],
                'compare' => 'BETWEEN',
                'type'    => 'numeric',
            ];
            $meta_query[] = [
                'key'     => 'event_lng',
                'value'   => [ $lng - $r, $lng + $r ],
                'compare' => 'BETWEEN',
                'type'    => 'numeric',
            ];
        }

        $tax_query = [];
        $category   = $request->get_param('category');
        $event_type = $request->get_param('event_type');
        if ($category) {
            $tax_query[] = [
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    => array_map('trim', explode(',', sanitize_text_field($category))),
            ];
        }
        if ($event_type) {
            $tax_query[] = [
                'taxonomy' => 'event_type',
                'field'    => 'slug',
                'terms'    => array_map('trim', explode(',', sanitize_text_field($event_type))),
            ];
        }

        $sort    = $request->get_param('sort');
        $orderby = 'meta_value';
        $order   = 'ASC';
        $meta_key = 'event_start_date';
        if ($sort === 'az') {
            $orderby = 'title';
            $meta_key = '';
        } elseif ($sort === 'newest') {
            $orderby = 'date';
            $order   = 'DESC';
            $meta_key = '';
        }

        $args = [
            'post_type'      => 'artpulse_event',
            'post_status'    => 'publish',
            'posts_per_page' => intval($request->get_param('per_page')),
            'orderby'        => $orderby,
            'order'          => $order,
        ];
        if ($meta_key) {
            $args['meta_key'] = $meta_key;
        }
        if ($meta_query) {
            $args['meta_query'] = $meta_query;
        }
        if ($tax_query) {
            $args['tax_query'] = $tax_query;
        }

        $query = new \WP_Query($args);
        $html  = '';
        if ($query->have_posts()) {
            foreach ($query->posts as $p) {
                $title_first = strtoupper(mb_substr($p->post_title, 0, 1));
                if ($alpha) {
                    if ($alpha === '#') {
                        if (ctype_alpha($title_first)) {
                            continue;
                        }
                    } elseif ($title_first !== strtoupper($alpha)) {
                        continue;
                    }
                }
                $html .= ap_get_event_card($p->ID);
            }
        }
        return rest_ensure_response(['html' => $html]);
    }
}
