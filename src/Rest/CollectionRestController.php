<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class CollectionRestController
{
    private const NAMESPACE = 'artpulse/v1';

    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route(self::NAMESPACE, '/collections', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [self::class, 'get_collections'],
                'permission_callback' => function () {
                    return current_user_can('read');
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [self::class, 'create_or_update'],
                'permission_callback' => function () {
                    return current_user_can('edit_ap_collections');
                },
                'args'                => [
                    'id'    => [ 'type' => 'integer', 'required' => false ],
                    'title' => [ 'type' => 'string',  'required' => true ],
                    'items' => [
                        'type'  => 'array',
                        'items' => [ 'type' => 'integer' ],
                        'required' => false,
                    ],
                ],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/collection/(?P<id>\d+)', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [self::class, 'get_collection'],
            'permission_callback' => function () {
                return current_user_can('read');
            },
            'args'                => [
                'id' => [ 'validate_callback' => 'is_numeric' ],
            ],
        ]);
    }

    public static function get_collections(WP_REST_Request $request): WP_REST_Response
    {
        $posts = get_posts([
            'post_type'      => 'ap_collection',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ]);

        $data = [];
        foreach ($posts as $id) {
            $post = get_post($id);
            if (!$post) {
                continue;
            }

            $item_ids = get_post_meta($id, 'ap_collection_items', true) ?: [];
            $items    = [];
            foreach ($item_ids as $pid) {
                $p = get_post($pid);
                if (!$p) {
                    continue;
                }
                $items[] = [
                    'type'      => $p->post_type,
                    'id'        => $p->ID,
                    'title'     => $p->post_title,
                    'excerpt'   => get_the_excerpt($p),
                    'thumbnail' => get_the_post_thumbnail_url($p, 'thumbnail') ?: '',
                ];
            }

            $data[] = [
                'id'          => $post->ID,
                'title'       => $post->post_title,
                'description' => $post->post_content,
                'thumbnail'   => get_the_post_thumbnail_url($post, 'thumbnail') ?: '',
                'items'       => $items,
            ];
        }
        return rest_ensure_response($data);
    }

    public static function create_or_update(WP_REST_Request $request): WP_REST_Response
    {
        $id    = intval($request->get_param('id'));
        $title = sanitize_text_field($request->get_param('title'));
        $items = $request->get_param('items');

        $data = [
            'post_title'  => $title,
            'post_type'   => 'ap_collection',
            'post_status' => 'publish',
        ];

        if ($id) {
            $data['ID'] = $id;
            $result = wp_update_post($data, true);
            if (is_wp_error($result)) {
                return $result;
            }
            $id = $result;
        } else {
            $id = wp_insert_post($data, true);
            if (is_wp_error($id)) {
                return $id;
            }
        }

        if (is_array($items)) {
            update_post_meta($id, 'ap_collection_items', array_map('intval', $items));
        }

        return rest_ensure_response(['id' => $id]);
    }

    public static function get_collection(WP_REST_Request $request): WP_REST_Response
    {
        $id = intval($request['id']);
        $post = get_post($id);
        if (!$post || $post->post_type !== 'ap_collection') {
            return new WP_REST_Response(['message' => 'Collection not found'], 404);
        }

        $item_ids = get_post_meta($post->ID, 'ap_collection_items', true) ?: [];
        $items    = [];
        foreach ($item_ids as $pid) {
            $p = get_post($pid);
            if (!$p) {
                continue;
            }
            $items[] = [
                'type'      => $p->post_type,
                'id'        => $p->ID,
                'title'     => $p->post_title,
                'excerpt'   => get_the_excerpt($p),
                'thumbnail' => get_the_post_thumbnail_url($p, 'thumbnail') ?: '',
            ];
        }

        $data = [
            'id'          => $post->ID,
            'title'       => $post->post_title,
            'description' => $post->post_content,
            'thumbnail'   => get_the_post_thumbnail_url($post, 'thumbnail') ?: '',
            'items'       => $items,
        ];
        return rest_ensure_response($data);
    }
}
