<?php
namespace ArtPulse\Rest;

class DashboardMessagesController {
    public static function register(): void {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void {
        register_rest_route('artpulse/v1', '/dashboard/messages', [
            'methods'  => 'GET',
            'callback' => [self::class, 'get_messages'],
            'permission_callback' => function () { return is_user_logged_in(); },
        ]);
    }

    public static function get_messages() {
        $user_id = get_current_user_id();
        $messages = self::get_recent_messages_for_user($user_id);
        return rest_ensure_response($messages);
    }

    private static function get_recent_messages_for_user(int $user_id): array {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_messages';
        $sql = $wpdb->prepare(
            "SELECT m.id, m.content, m.sender_id, u.display_name AS sender_name
             FROM $table m
             JOIN {$wpdb->users} u ON m.sender_id = u.ID
             WHERE m.recipient_id = %d OR m.sender_id = %d
             ORDER BY m.created_at DESC
             LIMIT 5",
            $user_id,
            $user_id
        );
        $rows = $wpdb->get_results($sql, ARRAY_A);
        foreach ($rows as &$row) {
            $row['id'] = (int) $row['id'];
            $row['sender_id'] = (int) $row['sender_id'];
        }
        return $rows;
    }
}
