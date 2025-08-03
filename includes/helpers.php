<?php
declare(strict_types=1);

function ap_get_ui_mode(): string {
    if (isset($_GET['ui_mode'])) {
        return sanitize_text_field($_GET['ui_mode']);
    }
    return get_option('ap_ui_mode', 'salient');
}

function ap_get_portfolio_display_mode(): string {
    return get_option('ap_portfolio_display', 'plugin');
}

/**
 * Simple object cache wrapper for expensive queries.
 */
function ap_cache_get(string $key, callable $callback, int $expires = HOUR_IN_SECONDS) {
    $group = 'artpulse_queries';
    $value = wp_cache_get($key, $group);
    if (false === $value) {
        $value = $callback();
        wp_cache_set($key, $value, $group, $expires);
    }
    return $value;
}

/**
 * Fetch and parse an RSS feed.
 *
 * Wraps WordPress fetch_feed() with basic error handling.
 *
 * @param string $url Feed URL.
 * @return array|SimplePie
 */
function ap_get_feed(string $url): array|SimplePie {
    include_once ABSPATH . WPINC . '/feed.php';

    $feed = fetch_feed($url);
    if (is_wp_error($feed)) {
        return [];
    }

    return $feed;
}

function ap_template_context(array $args = [], array $defaults = []): array {
    return wp_parse_args($args, $defaults);
}

function ap_safe_include(string $relative_template, string $fallback_path, array $context = []): void {
    $template = locate_template($relative_template);
    if (!$template) {
        $template = $fallback_path;
    }
    if ($template && file_exists($template)) {
        if (!empty($context)) {
            extract($context, EXTR_SKIP);
        }
        include $template;
    } else {
        error_log("ArtPulse: Missing template → $relative_template or fallback.");
    }
}

/**
 * Locate a template allowing theme overrides similar to WooCommerce.
 *
 * @param string $relative_template Relative path within the theme.
 * @param string $plugin_path       Default path in the plugin.
 * @return string Absolute file path to load.
 */
function ap_locate_template(string $relative_template, string $plugin_path): string {
    $template = locate_template($relative_template);
    if (!$template) {
        $template = trailingslashit(get_stylesheet_directory()) . $relative_template;
        if (!file_exists($template)) {
            $template = $plugin_path;
        }
    }
    /**
     * Filter located template path.
     *
     * @param string $template Located template file path.
     * @param string $relative_template Requested relative template.
     */
    return apply_filters('ap_locate_template', $template, $relative_template);
}

function ap_clear_portfolio_cache(): void {
    wp_cache_flush();
}

/**
 * Render the unified dashboard template for the current user.
 *
 * @param string[] $allowed_roles Roles permitted to view the dashboard.
 */
function ap_render_dashboard(array $allowed_roles = []): void {
    $allowed_roles = array_map('sanitize_key', $allowed_roles);
    $user_role     = \ArtPulse\Core\DashboardController::get_role(get_current_user_id());

    if ($allowed_roles && !in_array($user_role, $allowed_roles, true)) {
        wp_die(__('Access denied', 'artpulse'));
    }

    ap_safe_include(
        'dashboard-role.php',
        plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'templates/dashboard-role.php',
        [
            'allowed_roles' => $allowed_roles,
            'user_role'     => $user_role,
        ]
    );
}

/**
 * Convenience wrapper to render the member dashboard.
 */
function ap_render_member_dashboard(): void {
    ap_render_dashboard(['member']);
}

/**
 * Convenience wrapper to render the artist dashboard.
 */
function ap_render_artist_dashboard(): void {
    ap_render_dashboard(['artist']);
}

/**
 * Convenience wrapper to render the organization dashboard.
 */
function ap_render_organization_dashboard(): void {
    ap_render_dashboard(['organization']);
}
