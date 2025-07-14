<?php
namespace ArtPulse\Community;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class CommentReportRestController
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/comment/(?P<id>\\d+)/report', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'report'],
            'permission_callback' => fn() => is_user_logged_in(),
            'args'                => [
                'id'     => [ 'validate_callback' => 'is_numeric' ],
                'reason' => [ 'type' => 'string', 'required' => false ],
            ],
        ]);
    }

    public static function report(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $comment_id = absint($request['id']);
        $comment    = get_comment($comment_id);
        if (!$comment) {
            return new WP_Error('invalid_comment', 'Comment not found.', ['status' => 404]);
        }

        $reason   = sanitize_text_field($request['reason'] ?? '');
        $user_id  = get_current_user_id();
        CommentReports::add_report($comment_id, $user_id, $reason);

        $count = CommentReports::count_reports($comment_id);
        if ($count >= 3) {
            update_comment_meta($comment_id, 'ap_hidden', 1);
        }

        return rest_ensure_response(['reported' => true, 'count' => $count]);
    }
}
