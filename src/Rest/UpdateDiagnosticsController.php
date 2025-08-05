<?php
namespace ArtPulse\Rest;

use WP_REST_Response;
use WP_REST_Server;
use WP_Error;

class UpdateDiagnosticsController
{
    public static function register(): void
    {
        if (did_action('rest_api_init')) {
            self::register_routes();
        } else {
            add_action('rest_api_init', [self::class, 'register_routes']);
        }
    }

    public static function register_routes(): void
    {
        register_rest_route(
            'artpulse/v1',
            '/update/diagnostics',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [self::class, 'get_diagnostics'],
                    'permission_callback' => function () {
                        if (!current_user_can('update_plugins')) {
                            return new WP_Error('rest_forbidden', __('Unauthorized.', 'artpulse'), ['status' => 403]);
                        }
                        return true;
                    },
                ],
            ]
        );
    }

    public static function get_diagnostics(): WP_REST_Response
    {
        $repo = get_option('ap_github_repo_url');
        if (!$repo) {
            return rest_ensure_response(['error' => 'No repo URL configured']);
        }

        $api  = str_replace('https://github.com/', 'https://api.github.com/repos/', rtrim($repo, '/')) . '/releases/latest';
        $resp = wp_remote_get($api, ['timeout' => 10]);

        return rest_ensure_response([
            'repo'      => $repo,
            'api'       => $api,
            'http_code' => wp_remote_retrieve_response_code($resp),
            'body'      => json_decode(wp_remote_retrieve_body($resp), true),
            'checked_at'=> current_time('mysql'),
        ]);
    }
}
