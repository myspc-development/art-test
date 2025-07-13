<?php
namespace ArtPulse\Community;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class EventCommentsController
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/event/(?P<id>\d+)/comments', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'list'],
            'permission_callback' => fn() => is_user_logged_in(),
            'args'                => [ 'id' => [ 'validate_callback' => 'is_numeric' ] ],
        ]);

        register_rest_route('artpulse/v1', '/event/(?P<id>\d+)/comments', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'add'],
            'permission_callback' => fn() => is_user_logged_in(),
            'args'                => [
                'id'      => [ 'validate_callback' => 'is_numeric' ],
                'content' => [ 'type' => 'string', 'required' => true ],
            ],
        ]);

        register_rest_route('artpulse/v1', '/event/comment/(?P<comment_id>\d+)/moderate', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'moderate'],
            'permission_callback' => fn() => current_user_can('moderate_comments'),
            'args'                => [
                'comment_id' => [ 'validate_callback' => 'is_numeric' ],
                'status'     => [ 'type' => 'string', 'enum' => ['approve','spam','trash'], 'required' => true ],
            ],
        ]);
    }

    public static function list(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $event_id = absint($request['id']);
        if (!$event_id || get_post_type($event_id) !== 'artpulse_event') {
            return new WP_Error('invalid_event', 'Invalid event.', ['status' => 404]);
        }

        $comments = get_comments([
            'post_id' => $event_id,
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

    public static function add(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $event_id = absint($request['id']);
        if (!$event_id || get_post_type($event_id) !== 'artpulse_event') {
            return new WP_Error('invalid_event', 'Invalid event.', ['status' => 404]);
        }

        $content = sanitize_text_field($request['content']);
        if ($content === '') {
            return new WP_Error('empty_content', 'Comment content is required.', ['status' => 400]);
        }

        $user    = wp_get_current_user();
        $data    = [
            'comment_post_ID'      => $event_id,
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

    public static function moderate(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $comment_id = absint($request['comment_id']);
        if (!$comment_id || !get_comment($comment_id)) {
            return new WP_Error('invalid_comment', 'Comment not found.', ['status' => 404]);
        }

        $status = $request['status'];
        switch ($status) {
            case 'approve':
                wp_set_comment_status($comment_id, 'approve');
                break;
            case 'spam':
                wp_spam_comment($comment_id);
                break;
            case 'trash':
                wp_trash_comment($comment_id);
                break;
        }

        return rest_ensure_response(['id' => $comment_id, 'status' => $status]);
    }
}
