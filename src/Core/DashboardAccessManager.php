<?php
namespace ArtPulse\Core;

class DashboardAccessManager
{
    public static function register(): void
    {
        add_action('template_redirect', [self::class, 'maybe_redirect']);
    }

    public static function maybe_redirect(): void
    {
        if (is_user_logged_in() || !is_page()) {
            return;
        }

        global $post;
        if (!$post) {
            return;
        }

        $content = $post->post_content ?? '';
        if (
            strpos($content, '[ap_user_dashboard]') !== false ||
            strpos($content, '[ap_react_dashboard]') !== false
        ) {
            $login_url = Plugin::get_login_url();
            if (!$login_url) {
                $login_url = wp_login_url();
            }
            if (is_string($login_url) && $login_url !== '') {
                wp_safe_redirect(add_query_arg('redirect_to', rawurlencode(get_permalink($post)), $login_url));
                exit;
            }
        }
    }
}
