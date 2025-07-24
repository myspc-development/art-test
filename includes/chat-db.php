<?php
namespace ArtPulse\DB\Chat;

use WPDB;

/**
 * Ensure chat tables exist.
 */
function install_tables(): void {
    global $wpdb;
    $charset = $wpdb->get_charset_collate();
    $chat = $wpdb->prefix . 'ap_event_chat';
    $reactions = $wpdb->prefix . 'ap_event_chat_reactions';
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $sql1 = "CREATE TABLE $chat (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        event_id BIGINT NOT NULL,
        user_id BIGINT NOT NULL,
        content TEXT NOT NULL,
        flagged TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY event_id (event_id),
        KEY user_id (user_id),
        KEY created_at (created_at)
    ) $charset;";
    $sql2 = "CREATE TABLE $reactions (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        message_id BIGINT NOT NULL,
        user_id BIGINT NOT NULL,
        emoji VARCHAR(10) NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY message_id (message_id),
        KEY user_id (user_id)
    ) $charset;";
    dbDelta($sql1);
    dbDelta($sql2);
}

function maybe_install_tables(): void {
    global $wpdb;
    $chat = $wpdb->prefix . 'ap_event_chat';
    if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $chat)) !== $chat) {
        install_tables();
    }
}

function insert_message(int $event_id, int $user_id, string $content): array {
    global $wpdb;
    $table = $wpdb->prefix . 'ap_event_chat';
    $wpdb->insert($table, [
        'event_id'   => $event_id,
        'user_id'    => $user_id,
        'content'    => $content,
        'created_at' => current_time('mysql'),
    ]);
    $id = (int) $wpdb->insert_id;
    wp_cache_delete('ap_event_chat_' . $event_id, 'ap_event_chat');
    return [
        'id'         => $id,
        'event_id'   => $event_id,
        'user_id'    => $user_id,
        'content'    => $content,
        'created_at' => current_time('mysql'),
        'reactions'  => [],
        'flagged'    => 0,
    ];
}

function get_messages(int $event_id): array {
    $cache_key = 'ap_event_chat_' . $event_id;
    $cached = wp_cache_get($cache_key, 'ap_event_chat');
    if ($cached !== false) {
        return $cached;
    }
    global $wpdb;
    $table = $wpdb->prefix . 'ap_event_chat';
    $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE event_id = %d ORDER BY created_at ASC LIMIT 50", $event_id), ARRAY_A);
    $messages = [];
    foreach ($rows as $row) {
        $msg_id = (int) $row['id'];
        $messages[] = [
            'id'         => $msg_id,
            'event_id'   => (int) $row['event_id'],
            'user_id'    => (int) $row['user_id'],
            'content'    => $row['content'],
            'created_at' => $row['created_at'],
            'flagged'    => (int) $row['flagged'],
            'reactions'  => get_reactions($msg_id),
        ];
    }
    wp_cache_set($cache_key, $messages, 'ap_event_chat', 10);
    return $messages;
}

function get_reactions(int $message_id): array {
    global $wpdb;
    $table = $wpdb->prefix . 'ap_event_chat_reactions';
    $rows = $wpdb->get_results($wpdb->prepare("SELECT emoji, COUNT(*) as cnt FROM $table WHERE message_id = %d GROUP BY emoji", $message_id), ARRAY_A);
    $reactions = [];
    foreach ($rows as $r) {
        $reactions[$r['emoji']] = (int) $r['cnt'];
    }
    return $reactions;
}

function add_reaction(int $message_id, int $user_id, string $emoji): void {
    global $wpdb;
    $table = $wpdb->prefix . 'ap_event_chat_reactions';
    $wpdb->insert($table, [
        'message_id' => $message_id,
        'user_id'    => $user_id,
        'emoji'      => $emoji,
        'created_at' => current_time('mysql'),
    ]);
    wp_cache_delete('ap_event_chat_' . get_event_for_message($message_id), 'ap_event_chat');
}

function get_event_for_message(int $message_id): int {
    global $wpdb;
    $table = $wpdb->prefix . 'ap_event_chat';
    return (int) $wpdb->get_var($wpdb->prepare("SELECT event_id FROM $table WHERE id = %d", $message_id));
}

function delete_message(int $id): void {
    global $wpdb;
    $event = get_event_for_message($id);
    $table = $wpdb->prefix . 'ap_event_chat';
    $wpdb->delete($table, ['id' => $id]);
    $react = $wpdb->prefix . 'ap_event_chat_reactions';
    $wpdb->delete($react, ['message_id' => $id]);
    wp_cache_delete('ap_event_chat_' . $event, 'ap_event_chat');
}

function flag_message(int $id): void {
    global $wpdb;
    $event = get_event_for_message($id);
    $table = $wpdb->prefix . 'ap_event_chat';
    $wpdb->update($table, ['flagged' => 1], ['id' => $id]);
    wp_cache_delete('ap_event_chat_' . $event, 'ap_event_chat');
}
