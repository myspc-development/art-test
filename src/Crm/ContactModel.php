<?php
namespace ArtPulse\Crm;

class ContactModel
{
    public static function get_all(int $org_id, string $tag = ''): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_crm_contacts';
        $where = $wpdb->prepare('org_id = %d', $org_id);
        if ($tag !== '') {
            $like = '%' . $wpdb->esc_like($tag) . '%';
            $where .= $wpdb->prepare(' AND tags LIKE %s', $like);
        }
        return $wpdb->get_results("SELECT * FROM $table WHERE $where ORDER BY last_active DESC", ARRAY_A);
    }

    public static function add_or_update(int $org_id, string $email, string $name = '', array $tags = []): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_crm_contacts';
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT id, name, tags FROM $table WHERE org_id = %d AND email = %s", $org_id, $email)
        );
        $now = current_time('mysql');
        if ($row) {
            $existing = $row->tags ? json_decode($row->tags, true) : [];
            $tags = array_unique(array_merge($existing, $tags));
            $wpdb->update(
                $table,
                [
                    'name' => $name ?: $row->name,
                    'tags' => wp_json_encode($tags),
                    'last_active' => $now,
                ],
                ['id' => $row->id]
            );
        } else {
            $wpdb->insert(
                $table,
                [
                    'org_id' => $org_id,
                    'user_id' => 0,
                    'email' => sanitize_email($email),
                    'name' => sanitize_text_field($name),
                    'tags' => wp_json_encode($tags),
                    'first_seen' => $now,
                    'last_active' => $now,
                ]
            );
        }
    }

    public static function add_tag(int $org_id, string $email, string $tag): void
    {
        self::add_or_update($org_id, $email, '', [$tag]);
    }
}
