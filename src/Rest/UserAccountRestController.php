<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class UserAccountRestController
{
    public static function register(): void
    {
        // If rest_api_init already ran (e.g. register() invoked within the
        // hook itself) register routes immediately. Otherwise hook normally.
        if ( did_action('rest_api_init') ) {
            self::register_routes();
        } else {
            add_action('rest_api_init', [self::class, 'register_routes']);
        }
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/user/export', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'export_user_data'],
            'permission_callback' => fn() => is_user_logged_in(),
            'args'                => [
                'format' => [
                    'type'    => 'string',
                    'enum'    => ['json', 'csv'],
                    'default' => 'json',
                ],
            ],
        ]);

        register_rest_route('artpulse/v1', '/user/delete', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'delete_user_data'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);
    }

    public static function export_user_data(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $format  = $request->get_param('format') ?: 'json';
        $user_id = get_current_user_id();
        $user    = get_userdata($user_id);

        if (!$user) {
            return new WP_Error('invalid_user', 'Invalid user', ['status' => 400]);
        }

        $profile = [
            'ID'                 => $user->ID,
            'display_name'       => $user->display_name,
            'email'              => $user->user_email,
            'membership_level'   => get_user_meta($user_id, 'ap_membership_level', true),
            'membership_expires' => get_user_meta($user_id, 'ap_membership_expires', true),
            'country'            => get_user_meta($user_id, 'ap_country', true),
            'state'              => get_user_meta($user_id, 'ap_state', true),
            'city'               => get_user_meta($user_id, 'ap_city', true),
        ];

        $post_types = ['artpulse_event', 'artpulse_artist', 'artpulse_artwork', 'artpulse_org'];
        $posts = get_posts([
            'post_type'   => $post_types,
            'post_status' => 'any',
            'author'      => $user_id,
            'numberposts' => -1,
        ]);
        $post_rows = [];
        foreach ($posts as $p) {
            $post_rows[] = [
                'ID'    => $p->ID,
                'type'  => $p->post_type,
                'title' => $p->post_title,
                'status'=> $p->post_status,
            ];
        }

        if ($format === 'csv') {
            $stream = fopen('php://temp', 'w');
            fputcsv($stream, array_keys($profile));
            fputcsv($stream, array_values($profile));
            fputcsv($stream, []); // blank line
            fputcsv($stream, ['post_id', 'post_type', 'post_title', 'post_status']);
            foreach ($post_rows as $row) {
                fputcsv($stream, [$row['ID'], $row['type'], $row['title'], $row['status']]);
            }
            rewind($stream);
            $csv = stream_get_contents($stream);
            fclose($stream);
            return new WP_REST_Response($csv, 200, [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename="user-export.csv"',
            ]);
        }

        return rest_ensure_response([
            'profile' => $profile,
            'posts'   => $post_rows,
        ]);
    }

    public static function delete_user_data(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $user_id = get_current_user_id();

        $post_types = ['artpulse_event', 'artpulse_artist', 'artpulse_artwork', 'artpulse_org'];
        $post_ids = get_posts([
            'post_type'   => $post_types,
            'post_status' => 'any',
            'author'      => $user_id,
            'numberposts' => -1,
            'fields'      => 'ids',
        ]);
        foreach ($post_ids as $pid) {
            wp_trash_post($pid);
        }

        $meta_keys = [
            'ap_country',
            'ap_state',
            'ap_city',
            'ap_membership_level',
            'ap_membership_expires',
            'ap_membership_paused',
            'stripe_customer_id',
            'stripe_payment_ids',
            'ap_push_token',
            'ap_phone_number',
            'ap_sms_opt_in',
        ];
        foreach ($meta_keys as $key) {
            delete_user_meta($user_id, $key);
        }

        wp_delete_user($user_id);

        return rest_ensure_response(['success' => true]);
    }
}
