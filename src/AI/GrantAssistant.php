<?php
namespace ArtPulse\AI;

use WP_REST_Request;

class GrantAssistant
{
    public static function register(): void
    {
        add_action('init', [self::class, 'maybe_install_table']);
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/ai/generate-grant-copy', [
            'methods'  => 'POST',
            'callback' => [self::class, 'generate'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);

        register_rest_route('artpulse/v1', '/ai/rewrite', [
            'methods'  => 'POST',
            'callback' => [self::class, 'rewrite'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);
    }

    public static function maybe_install_table(): void
    {
        global $wpdb;
        $table  = $wpdb->prefix . 'ap_generated_grant_copy';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            $charset = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $table (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                org_id INT NULL,
                user_id BIGINT NULL,
                type VARCHAR(40) NULL,
                tone VARCHAR(20) NULL,
                source TEXT NULL,
                draft TEXT NOT NULL,
                created_at DATETIME NOT NULL,
                PRIMARY KEY (id),
                KEY org_id (org_id),
                KEY user_id (user_id)
            ) $charset;";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }
    }

    private static function get_prompt(string $type, string $tone): string
    {
        $templates = [
            'project_summary'  => 'Write a 150 word project summary.',
            'public_benefit'   => 'Describe the public benefit.',
            'mission_alignment'=> 'Explain how this aligns with the mission.',
        ];

        $tones = [
            'formal'     => 'Use a formal grant application tone.',
            'public'     => 'Use an engaging public voice.',
            'curatorial' => 'Write for a curatorial audience.',
            'grant'      => 'Write for a grant application.',
        ];

        return ($templates[$type] ?? $templates['project_summary']) . ' ' . ($tones[$tone] ?? '');
    }

    public static function generate(WP_REST_Request $req)
    {
        $type   = sanitize_key($req->get_param('type'));
        $tone   = sanitize_key($req->get_param('tone'));
        $source = sanitize_textarea_field($req->get_param('source'));

        $draft  = trim(self::get_prompt($type, $tone) . ' ' . $source);
        $output = wpautop(esc_html($draft));

        if ($req->get_param('save')) {
            global $wpdb;
            $wpdb->insert(
                $wpdb->prefix . 'ap_generated_grant_copy',
                [
                    'org_id'    => absint($req->get_param('org_id')),
                    'user_id'   => get_current_user_id(),
                    'type'      => $type,
                    'tone'      => $tone,
                    'source'    => $source,
                    'draft'     => $draft,
                    'created_at'=> current_time('mysql'),
                ]
            );
        }

        return rest_ensure_response([
            'draft'  => $draft,
            'output' => $output,
        ]);
    }

    public static function rewrite(WP_REST_Request $req)
    {
        $text = sanitize_textarea_field($req->get_param('text'));
        $tone = sanitize_key($req->get_param('tone'));

        $prompt = self::get_prompt('rewrite', $tone);
        $draft  = trim(($prompt ?: 'Rewrite:') . ' ' . $text);
        $output = wpautop(esc_html($draft));

        if ($req->get_param('save')) {
            global $wpdb;
            $wpdb->insert(
                $wpdb->prefix . 'ap_generated_grant_copy',
                [
                    'org_id'    => absint($req->get_param('org_id')),
                    'user_id'   => get_current_user_id(),
                    'type'      => 'rewrite',
                    'tone'      => $tone,
                    'source'    => $text,
                    'draft'     => $draft,
                    'created_at'=> current_time('mysql'),
                ]
            );
        }

        return rest_ensure_response([
            'draft'  => $draft,
            'output' => $output,
        ]);
    }
}
