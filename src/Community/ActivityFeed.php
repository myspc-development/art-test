<?php
namespace ArtPulse\Community;

/**
 * Helper to aggregate recent activity from followed entities.
 */
class ActivityFeed
{
    /**
     * Get recent activity items from entities the user follows.
     *
     * @param int $user_id Current user ID.
     * @param int $limit   Number of items to return.
     * @return array<int, array<string, mixed>>
     */
    public static function get_feed(int $user_id, int $limit = 20): array
    {
        global $wpdb;

        $follows = FollowManager::get_user_follows($user_id);
        if (empty($follows)) {
            return [];
        }

        $follow_users = [];
        $follow_posts = [];
        foreach ($follows as $row) {
            if ($row->object_type === 'user') {
                $follow_users[] = (int) $row->object_id;
            } else {
                $follow_posts[] = (int) $row->object_id;
            }
        }

        $items = [];
        $posts_table    = $wpdb->posts;
        $comments_table = $wpdb->comments;
        $favorites_table= $wpdb->prefix . 'ap_favorites';

        if ($follow_users) {
            $ids = implode(',', array_map('intval', $follow_users));
            $rows = $wpdb->get_results("SELECT ID, post_title, post_date FROM {$posts_table} WHERE post_author IN ($ids) AND post_type = 'artpulse_event' AND post_status = 'publish' ORDER BY post_date DESC LIMIT {$limit}");
            foreach ($rows as $r) {
                $items[] = [
                    'type'  => 'event',
                    'title' => $r->post_title,
                    'link'  => get_permalink($r->ID),
                    'date'  => $r->post_date,
                ];
            }

            $rows = $wpdb->get_results("SELECT comment_ID, comment_content, comment_date, comment_post_ID FROM {$comments_table} WHERE user_id IN ($ids) AND comment_approved = '1' ORDER BY comment_date DESC LIMIT {$limit}");
            foreach ($rows as $c) {
                $items[] = [
                    'type'    => 'comment',
                    'title'   => get_the_title($c->comment_post_ID),
                    'link'    => get_comment_link($c->comment_ID),
                    'content' => $c->comment_content,
                    'date'    => $c->comment_date,
                ];
            }

            $rows = $wpdb->get_results("SELECT object_id, object_type, favorited_on FROM {$favorites_table} WHERE user_id IN ($ids) ORDER BY favorited_on DESC LIMIT {$limit}");
            foreach ($rows as $f) {
                $title = '';
                if (post_type_exists($f->object_type)) {
                    $p = get_post($f->object_id);
                    if ($p) {
                        $title = $p->post_title;
                    }
                }
                $items[] = [
                    'type'  => 'favorite',
                    'title' => $title,
                    'link'  => $title ? get_permalink($f->object_id) : '',
                    'date'  => $f->favorited_on,
                ];
            }
        }

        if ($follow_posts) {
            $ids = implode(',', array_map('intval', $follow_posts));
            $rows = $wpdb->get_results("SELECT comment_ID, comment_content, comment_date, comment_post_ID FROM {$comments_table} WHERE comment_post_ID IN ($ids) AND comment_approved = '1' ORDER BY comment_date DESC LIMIT {$limit}");
            foreach ($rows as $c) {
                $items[] = [
                    'type'    => 'comment',
                    'title'   => get_the_title($c->comment_post_ID),
                    'link'    => get_comment_link($c->comment_ID),
                    'content' => $c->comment_content,
                    'date'    => $c->comment_date,
                ];
            }
        }

        usort($items, static function ($a, $b) {
            return strcmp($b['date'], $a['date']);
        });

        return array_slice($items, 0, $limit);
    }
}
