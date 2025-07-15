<?php
namespace ArtPulse\Frontend;

use WP_REST_Request;
use WP_REST_Response;

class WidgetsController
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('widgets', '/embed.js', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'embed'],
            'permission_callback' => '__return_true',
        ]);
        register_rest_route('widgets', '/render', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'render'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function embed(WP_REST_Request $request): WP_REST_Response
    {
        $src = add_query_arg($request->get_params(), rest_url('widgets/render'));
        $js  = 'var f=document.createElement("iframe");f.src="' . esc_url_raw($src) . '";' .
            'f.style.width="100%";f.style.border="none";f.height="500";' .
            'document.currentScript.parentNode.insertBefore(f, document.currentScript);';
        $headers = ['Content-Type' => 'application/javascript', 'Cache-Control' => 'public,max-age=3600'];
        return new WP_REST_Response($js, 200, $headers);
    }

    public static function render(WP_REST_Request $request): WP_REST_Response
    {
        $type  = sanitize_key($request['type']);
        $id    = (int) $request['id'];
        $style = sanitize_key($request['style']);

        $events = [];
        if ($type === 'artist') {
            $events = get_posts([
                'post_type'      => 'artpulse_event',
                'post_status'    => 'publish',
                'posts_per_page' => 5,
                'author'         => $id,
            ]);
        } elseif ($type === 'gallery') {
            $events = get_posts([
                'post_type'      => 'artpulse_event',
                'post_status'    => 'publish',
                'posts_per_page' => 5,
                'meta_key'       => '_ap_event_organization',
                'meta_value'     => $id,
            ]);
        }

        ob_start();
        echo '<html><head><meta charset="utf-8"><style>body{margin:0;font-family:sans-serif}</style></head><body>';
        foreach ($events as $event) {
            echo '<div style="padding:8px;border-bottom:1px solid #eee">';
            echo '<a href="' . esc_url(get_permalink($event)) . '" target="_blank">' . esc_html($event->post_title) . '</a>';
            echo '</div>';
        }
        echo '</body></html>';
        $html = ob_get_clean();
        $headers = ['Cache-Control' => 'public,max-age=3600'];
        return new WP_REST_Response($html, 200, $headers);
    }
}
