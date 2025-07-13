<?php
namespace ArtPulse\Community;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;

class ForumRestController
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/forum/threads', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [self::class, 'list_threads'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);

        register_rest_route('artpulse/v1', '/forum/threads', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [self::class, 'create_thread'],
            'permission_callback' => fn() => is_user_logged_in(),
            'args'                => [
                'title'   => [ 'type' => 'string', 'required' => true ],
                'content' => [ 'type' => 'string', 'required' => false ],
            ],
        ]);

        register_rest_route('artpulse/v1', '/forum/thread/(?P<id>\d+)/comments', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [self::class, 'get_comments'],
            'permission_callback' => fn() => is_user_logged_in(),
            'args'                => [ 'id' => [ 'validate_callback' => 'is_numeric' ] ],
        ]);

        register_rest_route('artpulse/v1', '/forum/thread/(?P<id>\d+)/comments', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [self::class, 'add_comment'],
            'permission_callback' => fn() => is_user_logged_in(),
            'args'                => [
                'id'      => [ 'validate_callback' => 'is_numeric' ],
                'content' => [ 'type' => 'string', 'required' => true ],
            ],
        ]);
    }

    public static function list_threads(WP_REST_Request $request): WP_REST_Response
    {
        $posts = get_posts([
            'post_type'      => 'ap_forum_thread',
            'post_status'    => 'publish',
            'posts_per_page' => 20,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'fields'         => 'ids',
        ]);

        $data = array_map(function($id) {
            $p = get_post($id);
            if (!$p) {
                return null;
            }
            return [
                'id'      => $p->ID,
                'title'   => $p->post_title,
                'author'  => get_the_author_meta('display_name', $p->post_author),
                'date'    => $p->post_date,
                'excerpt' => wp_trim_words($p->post_content, 55),
            ];
        }, $posts);

        return rest_ensure_response(array_values(array_filter($data)));
    }

    public static function create_thread(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $title   = sanitize_text_field($request['title']);
        $content = wp_kses_post($request['content'] ?? '');

        $post_id = wp_insert_post([
            'post_title'   => $title,
            'post_content' => $content,
            'post_type'    => 'ap_forum_thread',
            'post_status'  => 'publish',
            'post_author'  => get_current_user_id(),
        ], true);

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        return rest_ensure_response(['id' => $post_id]);
    }

    public static function get_comments(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $thread_id = absint($request['id']);
        if (!$thread_id || get_post_type($thread_id) !== 'ap_forum_thread') {
            return new WP_Error('invalid_thread', 'Invalid thread.', ['status' => 404]);
        }

        $comments = get_comments([
            'post_id' => $thread_id,
            'status'  => 'approve',
        ]);

        $data = array_map(function($c) {
            return [
                'id'      => $c->comment_ID,
                'author'  => $c->comment_author,
                'content' => $c->comment_content,
                'date'    => $c->comment_date,
            ];
        }, $comments);

        return rest_ensure_response($data);
    }

    public static function add_comment(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $thread_id = absint($request['id']);
        if (!$thread_id || get_post_type($thread_id) !== 'ap_forum_thread') {
            return new WP_Error('invalid_thread', 'Invalid thread.', ['status' => 404]);
        }

        $content = sanitize_text_field($request['content']);
        if ($content === '') {
            return new WP_Error('empty_content', 'Comment content is required.', ['status' => 400]);
        }

        $user = wp_get_current_user();
        $data = [
            'comment_post_ID'      => $thread_id,
            'comment_content'      => $content,
            'user_id'              => $user->ID,
            'comment_author'       => $user->display_name,
            'comment_author_email' => $user->user_email,
            'comment_approved'     => 0,
        ];

        $comment_id = wp_insert_comment($data);
        if (!$comment_id) {
            return new WP_Error('create_failed', 'Unable to add comment.', ['status' => 500]);
        }

        return rest_ensure_response(['id' => $comment_id, 'status' => 'pending']);
    }
}
