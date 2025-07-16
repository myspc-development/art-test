<?php
namespace ArtPulse\Community;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class QaThreadRestController {
    public static function register(): void {
        add_action('rest_api_init', [self::class, 'routes']);
    }

    public static function routes(): void {
        register_rest_route('artpulse/v1', '/qa-thread/(?P<event_id>\d+)', [
            'methods'  => 'GET',
            'callback' => [self::class, 'get_thread'],
            'permission_callback' => function () {
                return current_user_can('read');
            },
        ]);
        register_rest_route('artpulse/v1', '/qa-thread/(?P<event_id>\d+)/post', [
            'methods'  => 'POST',
            'callback' => [self::class, 'post_comment'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);
    }

    private static function find_thread(int $event_id) {
        $posts = get_posts([
            'post_type'  => 'qa_thread',
            'meta_key'   => 'event_id',
            'meta_value' => $event_id,
            'numberposts'=> 1,
        ]);
        return $posts ? $posts[0] : null;
    }

    public static function get_thread(WP_REST_Request $req): WP_REST_Response|WP_Error {
        $event_id = absint($req['event_id']);
        $thread = self::find_thread($event_id);
        if (!$thread) {
            return new WP_Error('not_found', 'Thread not found', ['status' => 404]);
        }
        $comments = get_comments(['post_id' => $thread->ID, 'status' => 'approve']);
        $data = array_map(function($c) use ($thread){
            return [
                'id'      => $c->comment_ID,
                'author'  => $c->comment_author,
                'user_id' => (int) $c->user_id,
                'content' => $c->comment_content,
                'date'    => $c->comment_date,
            ];
        }, $comments);
        $meta = [
            'start_time'  => get_post_meta($thread->ID, 'start_time', true),
            'end_time'    => get_post_meta($thread->ID, 'end_time', true),
            'participants'=> get_post_meta($thread->ID, 'participants', true),
        ];
        return rest_ensure_response(['thread_id' => $thread->ID, 'meta' => $meta, 'comments' => $data]);
    }

    public static function post_comment(WP_REST_Request $req): WP_REST_Response|WP_Error {
        $event_id = absint($req['event_id']);
        $thread = self::find_thread($event_id);
        if (!$thread) {
            return new WP_Error('not_found', 'Thread not found', ['status' => 404]);
        }
        $start = get_post_meta($thread->ID, 'start_time', true);
        $end   = get_post_meta($thread->ID, 'end_time', true);
        $now   = current_time('mysql');
        if (($start && $now < $start) || ($end && $now > $end)) {
            return new WP_Error('closed', 'Thread closed', ['status' => 403]);
        }
        $participants = get_post_meta($thread->ID, 'participants', true);
        if (is_array($participants) && !empty($participants)) {
            $uid = get_current_user_id();
            $allowed = array_map('intval', $participants);
            if (!in_array($uid, $allowed, true)) {
                return new WP_Error('forbidden', 'Not allowed', ['status' => 403]);
            }
        }
        $content = sanitize_text_field($req['content']);
        if ($content === '') {
            return new WP_Error('empty', 'Content required', ['status' => 400]);
        }
        $comment_id = wp_insert_comment([
            'comment_post_ID' => $thread->ID,
            'comment_content' => $content,
            'user_id'         => get_current_user_id(),
            'comment_approved'=> 1,
        ]);
        return rest_ensure_response(['id' => $comment_id]);
    }
}
