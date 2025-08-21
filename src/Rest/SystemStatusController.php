<?php
namespace ArtPulse\Rest;

use WP_REST_Server;
use WP_REST_Response;

final class SystemStatusController {
    public static function register(): void {
        add_action('rest_api_init', [self::class, 'routes']);
    }
    public static function routes(): void {
        register_rest_route('ap/v1', '/system/status', [
            'methods'  => WP_REST_Server::READABLE,
            'permission_callback' => '__return_true', // public
            'callback' => function () {
                global $wp_version;
                $plugin_v = defined('ARTPULSE_VERSION') ? ARTPULSE_VERSION : 'dev';
                return new WP_REST_Response([
                    'ok'     => true,
                    'php'    => PHP_VERSION,
                    'wp'     => $wp_version ?? 'unknown',
                    'plugin' => $plugin_v,
                ], 200);
            }
        ]);
    }
}
