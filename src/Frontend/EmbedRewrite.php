<?php
namespace ArtPulse\Frontend;

use WP_REST_Request;
use ArtPulse\Frontend\WidgetsController;

class EmbedRewrite {
    public static function register(): void {
        add_action('init', [self::class, 'add_rules']);
        add_filter('query_vars', [self::class, 'vars']);
        add_action('template_redirect', [self::class, 'maybe_render']);
    }

    public static function add_rules(): void {
        add_rewrite_rule('^embed\.js$', 'index.php?ap_embed_js=1', 'top');
        add_rewrite_rule('^embed$', 'index.php?ap_embed=1', 'top');
    }

    public static function vars(array $vars): array {
        $vars[] = 'ap_embed_js';
        $vars[] = 'ap_embed';
        return $vars;
    }

    public static function maybe_render(): void {
        if (get_query_var('ap_embed_js')) {
            $req = new WP_REST_Request('GET');
            $req->set_query_params($_GET);
            $resp = WidgetsController::embed($req);
            status_header($resp->get_status());
            foreach ($resp->get_headers() as $k => $v) { header("$k: $v"); }
            echo $resp->get_data();
            exit;
        }
        if (get_query_var('ap_embed')) {
            $req = new WP_REST_Request('GET');
            $req->set_query_params($_GET);
            $resp = WidgetsController::render($req);
            status_header($resp->get_status());
            foreach ($resp->get_headers() as $k => $v) { header("$k: $v"); }
            echo $resp->get_data();
            exit;
        }
    }
}
