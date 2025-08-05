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
        register_rest_route('artpulse/v1', '/widgets/embed.js', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'embed'],
            'permission_callback' => '__return_true',
        ]);
        register_rest_route('artpulse/v1', '/widgets/render', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'render'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function embed(WP_REST_Request $request): WP_REST_Response
    {
        $src = add_query_arg($request->get_params(), rest_url('artpulse/v1/widgets/render'));
        $js  = 'var f=document.createElement("iframe");f.src="' . esc_url_raw($src) . '";' .
            'f.style.width="100%";f.style.border="none";f.height="500";' .
            'document.currentScript.parentNode.insertBefore(f, document.currentScript);';
        $headers = ['Content-Type' => 'application/javascript', 'Cache-Control' => 'public,max-age=3600'];
        return new WP_REST_Response($js, 200, $headers);
    }

    public static function render(WP_REST_Request $request): WP_REST_Response
    {
        $org    = absint($request->get_param('org'));
        $tag    = sanitize_text_field($request->get_param('tag'));
        $region = sanitize_text_field($request->get_param('region'));
        $limit  = min(20, absint($request->get_param('limit') ?: 5));
        $theme  = $request->get_param('theme') === 'dark' ? 'dark' : 'light';
        $layout = in_array($request->get_param('layout'), ['cards','grid','list'], true) ? $request->get_param('layout') : 'list';
        $compact = filter_var($request->get_param('compact'), FILTER_VALIDATE_BOOLEAN);

        $args = [
            'post_type'      => 'artpulse_event',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
        ];
        $meta_query = [];
        if ($org) {
            $meta_query[] = [ 'key' => '_ap_event_organization', 'value' => $org ];
        }
        if ($region) {
            $meta_query[] = [ 'key' => 'event_state', 'value' => $region ];
        }
        if ($meta_query) { $args['meta_query'] = $meta_query; }
        if ($tag) { $args['tag'] = $tag; }
        $events = get_posts($args);

        ob_start();
        $template = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'templates/embed/' . $layout . '.php';
        if (file_exists($template)) {
            include $template;
        }
        $html = ob_get_clean();
        \ArtPulse\Analytics\EmbedAnalytics::log(md5($org.$tag.$region.$layout.$limit), 0, 'view');
        return new WP_REST_Response($html, 200, ['Cache-Control' => 'public,max-age=3600']);
    }
}
