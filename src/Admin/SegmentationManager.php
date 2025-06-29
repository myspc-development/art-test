<?php
namespace ArtPulse\Admin;

/**
 * Filters and exports user segments.
 */
class SegmentationManager
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/admin/users', [
            'methods'  => 'GET',
            'callback' => [self::class, 'handle'],
            'permission_callback' => [self::class, 'check_permission'],
        ]);
    }

    public static function check_permission(): bool
    {
        return current_user_can('manage_options');
    }

    public static function handle(\WP_REST_Request $request)
    {
        $role  = sanitize_text_field($request->get_param('role'));
        $level = sanitize_text_field($request->get_param('level'));

        $args = [
            'number' => 1000,
            'fields' => ['ID', 'display_name', 'user_email'],
        ];

        if ($role) {
            $args['role__in'] = [$role];
        }

        if ($level) {
            $args['meta_query'] = [
                [
                    'key'   => 'ap_membership_level',
                    'value' => $level,
                ],
            ];
        }

        $users = get_users($args);

        $rows = array_map(
            static fn($u) => [
                'ID'    => $u->ID,
                'name'  => $u->display_name ?: $u->user_login,
                'email' => $u->user_email,
            ],
            $users
        );

        if ($request->get_param('format') === 'csv') {
            $stream = fopen('php://temp', 'w');
            fputcsv($stream, ['ID', 'name', 'email']);
            foreach ($rows as $row) {
                fputcsv($stream, [$row['ID'], $row['name'], $row['email']]);
            }
            rewind($stream);
            $csv = stream_get_contents($stream);
            fclose($stream);

            return new \WP_REST_Response($csv, 200, [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename="users.csv"',
            ]);
        }

        return rest_ensure_response($rows);
    }
}
