<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;

class ImportRestController
{
    /**
     * Allowed post types for import.
     *
     * @var string[]
     */
    protected static array $allowed_post_types = [
        'artpulse_org',
        'artpulse_event',
        'artpulse_artist',
        'artpulse_artwork',
    ];

    public static function register(): void
    {
        register_rest_route(
            'artpulse/v1',
            '/import',
            [
                'methods'             => 'POST',
                'callback'            => [self::class, 'handle_import'],
                'permission_callback' => [self::class, 'check_permissions'],
            ]
        );
    }

    public static function check_permissions(): bool
    {
        return current_user_can('manage_options');
    }

    public static function handle_import(WP_REST_Request $request): WP_REST_Response
    {
        $params    = $request->get_json_params();
        $rows      = $params['rows'] ?? [];
        $post_type = sanitize_key($params['post_type'] ?? '');

        if (!in_array($post_type, self::$allowed_post_types, true)) {
            return new WP_REST_Response(['message' => 'Invalid post type'], 400);
        }

        $created = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $postarr = [
                'post_type'   => $post_type,
                'post_status' => 'publish',
            ];

            if (isset($row['post_title'])) {
                $postarr['post_title'] = sanitize_text_field($row['post_title']);
                unset($row['post_title']);
            }
            if (isset($row['post_content'])) {
                $postarr['post_content'] = wp_kses_post($row['post_content']);
                unset($row['post_content']);
            }

            $post_id = wp_insert_post($postarr, true);
            if (is_wp_error($post_id)) {
                continue;
            }

            foreach ($row as $key => $value) {
                update_post_meta($post_id, sanitize_key($key), sanitize_text_field($value));
            }
            $created[] = $post_id;
        }

        return rest_ensure_response(['created' => $created]);
    }
}
