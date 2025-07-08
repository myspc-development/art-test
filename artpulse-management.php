<?php
/**
 * Plugin Name:     ArtPulse Management
 * Description:     Management plugin for ArtPulse.
 * Version:         1.3.10
 * Author:          craig
 * Text Domain:     artpulse
 * License:         GPL2
 */

use ArtPulse\Core\Plugin;
use ArtPulse\Core\WooCommerceIntegration;
use ArtPulse\Core\ArtworkWooSync;
use ArtPulse\Core\Activator;
use ArtPulse\Admin\EnqueueAssets;

// Suppress deprecated notices if WP_DEBUG enabled
if (defined('WP_DEBUG') && WP_DEBUG) {
    @ini_set('display_errors', '0');
    @error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
}

// Define ARTPULSE_PLUGIN_FILE constant (THIS IS CRUCIAL - MUST BE DEFINED CORRECTLY)
if (!defined('ARTPULSE_PLUGIN_FILE')) {
    define('ARTPULSE_PLUGIN_FILE', __FILE__);
}

// Load Composer autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}
// Load shared frontend helpers
require_once __DIR__ . '/src/Frontend/EventHelpers.php';
require_once __DIR__ . '/src/Frontend/ShareButtons.php';
require_once __DIR__ . '/includes/dashboard-widgets.php';
require_once __DIR__ . '/includes/business-dashboard-widgets.php';

// Handle user dashboard reset
add_action('init', function () {
    if (isset($_POST['reset_user_layout']) && check_admin_referer('ap_reset_user_layout')) {
        \ArtPulse\Admin\UserLayoutManager::reset_user_layout(get_current_user_id());
    }
});

// Redirect users to role-specific dashboards when accessing wp-admin dashboard
add_action('admin_init', function () {
    if (!is_admin()) {
        return;
    }

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if ($screen && $screen->id === 'dashboard') {
        $user = wp_get_current_user();
        $roles = (array) $user->roles;
        if (in_array('artist', $roles, true)) {
            wp_redirect(site_url('/dashboard-artist'));
            exit;
        } elseif (in_array('member', $roles, true)) {
            wp_redirect(site_url('/dashboard-member'));
            exit;
        } elseif (in_array('organization', $roles, true)) {
            wp_redirect(site_url('/dashboard-organization'));
            exit;
        }
    }
});

// Load sample widgets for testing
add_action('init', function () {
    if (class_exists('\\ArtPulse\\Sample\\SampleWidgets')) {
        \ArtPulse\Sample\SampleWidgets::register();
    }
    if (class_exists('\\ArtPulse\\Sample\\RoleBasedWidgets')) {
        \ArtPulse\Sample\RoleBasedWidgets::register();
    }
});

add_action('plugins_loaded', function () {
    \ArtPulse\Admin\DashboardWidgetTools::register();

    \ArtPulse\Core\DashboardWidgetRegistry::register(
        'hello-world',
        __('Hello World', 'artpulse'),
        'smiley',
        __('Example hello world widget.', 'artpulse'),
        static function () {
            return '<p>Hello World!</p>';
        }
    );

    \ArtPulse\Core\DashboardWidgetRegistry::register(
        'php-version',
        __('PHP Version', 'artpulse'),
        'admin-site-alt3',
        __('Displays current PHP version.', 'artpulse'),
        static function () {
            return '<p>PHP ' . phpversion() . '</p>';
        }
    );
});

/**
 * Copy bundled Salient templates to the active child theme.
 */
function ap_copy_templates_to_child_theme() {
    $source_dir  = plugin_dir_path(__FILE__) . 'templates/salient/';
    $target_root = get_stylesheet_directory();
    $target_dir  = trailingslashit($target_root) . 'templates/salient/';

    if (!file_exists($target_dir)) {
        wp_mkdir_p($target_dir);
    }

    $files = [
        'single-artpulse_event.php',
        'content-artpulse_event.php',
        'archive-artpulse_event.php',
    ];

    foreach ($files as $file) {
        $source = $source_dir . $file;
        if (!file_exists($source)) {
            continue;
        }
        $destination = ($file === 'single-artpulse_event.php')
            ? trailingslashit($target_root) . $file
            : $target_dir . $file;

        copy($source, $destination);
    }
}

// 🔧 Boot the main plugin class (responsible for registering menus, settings, CPTs, etc.)
$main = new Plugin();
// Instantiate WooCommerce integration (if needed for runtime)
$plugin = new WooCommerceIntegration();
$artworkSync = new ArtworkWooSync();

// ✅ Hook for activation
register_activation_hook(__FILE__, function () {
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    artpulse_create_custom_table();
    \ArtPulse\Core\FeedbackManager::install_table();
    Activator::activate(); // WooCommerceIntegration has no activate() method
    ap_copy_templates_to_child_theme();

    // Initialize default dashboard widget layout if missing
    if (false === get_option('ap_dashboard_widget_config', false)) {
        $roles       = array_keys(wp_roles()->roles);
        $definitions = \ArtPulse\Core\DashboardWidgetRegistry::get_definitions();
        $all_ids     = array_column($definitions, 'id');
        $default     = [];
        foreach ($roles as $role) {
            $default[$role] = $all_ids;
        }
        add_option('ap_dashboard_widget_config', $default);
    }
});


// Register Dashboard Preview admin page
add_action('admin_menu', function () {
    add_menu_page(
        'Dashboard Preview',
        'Dashboard Preview',
        'manage_options',
        'dashboard-preview',
        'ap_render_dashboard_preview_page',
        'dashicons-visibility',
        80
    );
});

function ap_render_dashboard_preview_page() {
    ?>
    <div class="wrap">
        <h1>Dashboard Preview</h1>
        <form method="get">
            <input type="hidden" name="page" value="dashboard-preview" />
            <select name="role">
                <option value="">Select Role</option>
                <option value="member" <?= selected($_GET['role'] ?? '', 'member') ?>>Member</option>
                <option value="artist" <?= selected($_GET['role'] ?? '', 'artist') ?>>Artist</option>
                <option value="organization" <?= selected($_GET['role'] ?? '', 'organization') ?>>Organization</option>
            </select>
            <button class="button button-primary">Preview</button>
        </form>
        <hr>
    <?php

    $role = $_GET['role'] ?? null;

    if ($role && in_array($role, ['member', 'artist', 'organization'])) {
        echo '<h2>Previewing: ' . ucfirst($role) . ' Dashboard</h2>';
        echo '<div id="ap-user-dashboard" class="ap-dashboard-columns">';
        \ArtPulse\Admin\DashboardWidgetTools::render_role_dashboard_preview($role);
        echo '</div>';
    }

    echo '</div>';
}


// Add ArtPulse Settings page in the Settings menu
add_action('admin_menu', function () {
    add_options_page(
        __('ArtPulse Settings', 'artpulse'),
        __('ArtPulse', 'artpulse'),
        'manage_options',
        'artpulse-settings',
        'artpulse_settings_page'
    );
});

/**
 * Render the ArtPulse Settings page.
 */
function artpulse_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('ArtPulse Settings', 'artpulse'); ?></h1>
        <form method="post">
            <?php wp_nonce_field('artpulse_copy_templates'); ?>
            <p>
                <input type="submit" class="button button-primary" name="ap_copy_templates" value="<?php esc_attr_e('Copy Templates to Child Theme', 'artpulse'); ?>" />
            </p>
        </form>
    </div>
    <?php
}

// Handle template copy action
add_action('admin_init', function () {
    if (!isset($_POST['ap_copy_templates'])) {
        return;
    }

    if (!current_user_can('manage_options')) {
        return;
    }

    check_admin_referer('artpulse_copy_templates');

    ap_copy_templates_to_child_theme();

    add_action('admin_notices', function () {
        echo '<div class="notice notice-success is-dismissible"><p>' .
             esc_html__('Templates copied to child theme.', 'artpulse') .
             '</p></div>';
    });
});

// ✅ Hook for deactivation
//register_deactivation_hook(__FILE__, [$plugin, 'deactivate']);

// Register REST API routes
add_action('rest_api_init', function () {
    \ArtPulse\Rest\PortfolioRestController::register();
    \ArtPulse\Rest\UserAccountRestController::register();
});


function artpulse_create_custom_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'artpulse_data';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title text NOT NULL,
        artist_name varchar(255) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * Enqueue global styles on the frontend.
 */
/**
 * Check if the current post content contains any ArtPulse shortcode.
 *
 * @return bool
 */
function ap_page_has_artpulse_shortcode() {
    if (!is_singular()) {
        return false;
    }

    global $post;

    if (!$post || empty($post->post_content)) {
        return false;
    }

    return strpos($post->post_content, '[ap_') !== false;
}

/**
 * Get the active theme accent color.
 *
 * @return string Hex color string.
 */
function ap_get_accent_color() {
    return get_theme_mod('accent_color', '#0073aa');
}

/**
 * Adjust a hex color brightness by the given percentage.
 *
 * @param string $hex      Base color in hex format.
 * @param float  $percent  Percentage to lighten/darken (-1 to 1).
 * @return string Adjusted hex color.
 */
function ap_adjust_color_brightness($hex, $percent) {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = str_repeat(substr($hex, 0, 1), 2) .
               str_repeat(substr($hex, 1, 1), 2) .
               str_repeat(substr($hex, 2, 1), 2);
    }

    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    $r = max(0, min(255, (int) ($r * (1 + $percent))));
    $g = max(0, min(255, (int) ($g * (1 + $percent))));
    $b = max(0, min(255, (int) ($b * (1 + $percent))));

    return sprintf('#%02x%02x%02x', $r, $g, $b);
}

/**
 * Determine if ArtPulse frontend styles are disabled.
 *
 * @return bool
 */
function ap_styles_disabled() {
    $settings = get_option('artpulse_settings', []);
    return !empty($settings['disable_styles']);
}

/**
 * Enqueue the global UI styles on the frontend.
 *
 * By default the styles are only loaded when a page contains an
 * ArtPulse shortcode. Themes or page builders can bypass this detection by
 * filtering {@see 'ap_bypass_shortcode_detection'} and returning true.
 */
function ap_enqueue_global_styles() {
    if (is_admin()) {
        return;
    }

    $bypass = apply_filters('ap_bypass_shortcode_detection', false);

    if ($bypass || ap_page_has_artpulse_shortcode()) {
        $accent = ap_get_accent_color();
        $hover  = ap_adjust_color_brightness($accent, -0.1);
        wp_add_inline_style(
            'ap-complete-dashboard-style',
            ":root { --ap-primary: {$accent}; --ap-primary-hover: {$hover}; }"
        );
    }
}
add_action('wp_enqueue_scripts', 'ap_enqueue_global_styles');

/**
 * Enqueue dashboard styles only when a page uses an ArtPulse shortcode.
 */
function ap_enqueue_dashboard_styles() {
    if (!ap_page_has_artpulse_shortcode()) {
        return;
    }

    wp_enqueue_style(
        'ap-complete-dashboard-style',
        plugin_dir_url(__FILE__) . 'assets/css/ap-complete-dashboard-frontend.css',
        [],
        filemtime(plugin_dir_path(__FILE__) . 'assets/css/ap-complete-dashboard-frontend.css')
    );
    $user_css = plugin_dir_path(__FILE__) . 'assets/css/ap-user-dashboard.css';
    if (file_exists($user_css)) {
        wp_enqueue_style(
            'ap-user-dashboard-style',
            plugin_dir_url(__FILE__) . 'assets/css/ap-user-dashboard.css',
            ['ap-complete-dashboard-style'],
            filemtime($user_css)
        );
    }
}
add_action('wp_enqueue_scripts', 'ap_enqueue_dashboard_styles');

// Load modern frontend UI styles for Salient/WPBakery integration
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'ap-frontend-styles',
        plugin_dir_url(__FILE__) . 'assets/css/ap-frontend-styles.css',
        [],
        filemtime(plugin_dir_path(__FILE__) . 'assets/css/ap-frontend-styles.css')
    );
});

/**
 * Enqueue the base plugin stylesheet.
 */
function ap_enqueue_main_style() {
    $css_path = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'assets/css/main.min.css';
    wp_enqueue_style(
        'ap-main-style',
        plugins_url('assets/css/main.min.css', ARTPULSE_PLUGIN_FILE),
        [],
        file_exists($css_path) ? filemtime($css_path) : null
    );
}
add_action('wp_enqueue_scripts', 'ap_enqueue_main_style');
add_action('admin_enqueue_scripts', 'ap_enqueue_main_style');

/**
 * Optionally enqueue styles for the admin area.
 *
 * @param string $hook Current admin page hook.
 */
function ap_enqueue_admin_styles($hook) {
    if (strpos($hook, 'artpulse') !== false) {
        wp_enqueue_style(
            'ap-admin-ui',
            plugin_dir_url(__FILE__) . 'assets/css/ap-style.css',
            [],
            '1.0'
        );
    }
}
add_action('admin_enqueue_scripts', 'ap_enqueue_admin_styles');

// Enqueue SortableJS and layout script on dashboard pages
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook === 'index.php' || strpos($hook, 'dashboard') !== false) {
        wp_enqueue_script(
            'sortablejs',
            plugin_dir_url(__FILE__) . 'assets/js/Sortable.min.js',
            [],
            '1.15.0',
            true
        );
        wp_enqueue_script(
            'ap-role-dashboard',
            plugin_dir_url(__FILE__) . 'assets/js/role-dashboard.js',
            ['sortablejs'],
            '1.0',
            true
        );
        wp_localize_script('ap-role-dashboard', 'APLayout', [
            'nonce'    => wp_create_nonce('ap_save_user_layout'),
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
        wp_enqueue_style(
            'ap-dashboard-style',
            plugin_dir_url(__FILE__) . 'assets/css/dashboard-widget.css',
            [],
            '1.0'
        );
    }
});

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook === 'toplevel_page_dashboard-preview') {
        wp_enqueue_script('sortablejs', plugin_dir_url(__FILE__) . 'assets/js/Sortable.min.js', [], null, true);
        wp_enqueue_script('ap-role-dashboard', plugin_dir_url(__FILE__) . 'assets/js/role-dashboard.js', ['sortablejs'], null, true);
        wp_localize_script('ap-role-dashboard', 'APLayout', [
            'nonce'    => wp_create_nonce('ap_save_user_layout'),
            'ajax_url' => admin_url('admin-ajax.php')
        ]);
        wp_enqueue_style('ap-dashboard-style', plugin_dir_url(__FILE__) . 'assets/css/dashboard-widget.css');
    }
});


// Enqueue the full SortableJS library on dashboard pages.
add_action('wp_enqueue_scripts', function () {
    if (is_page('dashboard') || is_page('organization-dashboard')) {
        wp_enqueue_script(
            'sortablejs',
            plugins_url('assets/libs/sortablejs/Sortable.min.js', __FILE__),
            [],
            null,
            true
        );
    }
});

// Deprecated: use REST endpoint /artpulse/v1/favorite instead

function ap_user_has_favorited($user_id, $post_id) {
    $post_type = get_post_type($post_id);
    if (class_exists('\\ArtPulse\\Community\\FavoritesManager') && $post_type) {
        return \ArtPulse\Community\FavoritesManager::is_favorited($user_id, $post_id, $post_type);
    }
    $meta_key = ($post_type == 'artpulse_event') ? 'ap_favorite_events' : 'ap_favorite_artworks';
    $favs = get_user_meta($user_id, $meta_key, true) ?: [];
    return in_array($post_id, $favs);
}

function ap_render_favorite_portfolio() {
    if (!is_user_logged_in()) {
        return '<p>' . __('Please log in to view your favorites.', 'artpulse') . '</p>';
    }
    $user_id = get_current_user_id();
    if (class_exists('\\ArtPulse\\Community\\FavoritesManager')) {
        $favs = \ArtPulse\Community\FavoritesManager::get_user_favorites($user_id);
        $favorite_ids = array_map(fn($f) => $f->object_id, $favs);
    } else {
        $fav_events = get_user_meta($user_id, 'ap_favorite_events', true) ?: [];
        $fav_artworks = get_user_meta($user_id, 'ap_favorite_artworks', true) ?: [];
        $favorite_ids = array_merge($fav_events, $fav_artworks);
    }

    ob_start();
    if ($favorite_ids) {
        $fav_query = new WP_Query([
            'post_type' => ['artpulse_event', 'artpulse_artwork'],
            'post__in' => $favorite_ids,
            'orderby' => 'post__in',
            'posts_per_page' => 12
        ]);
        echo '<div class="row portfolio-items">';
        while($fav_query->have_posts()) : $fav_query->the_post();
            echo '<div class="col span_4">';
            if (get_post_type() === 'artpulse_event') {
                echo ap_get_event_card(get_the_ID());
            } else {
?>
                <div class="nectar-portfolio-item">
                    <a href="<?php the_permalink(); ?>">
                        <?php the_post_thumbnail('portfolio-thumb'); ?>
                        <h3><?php the_title(); ?></h3>
                    </a>
                    <div class="ap-event-actions">
                        <?php echo \ArtPulse\Frontend\ap_render_favorite_button( get_the_ID(), get_post_type() ); ?>
                        <span class="ap-fav-count"><?php echo intval( get_post_meta( get_the_ID(), 'ap_favorite_count', true ) ); ?></span>
                    </div>
                </div>
<?php
            }
            echo '</div>';
        endwhile;
        echo '</div>';
        wp_reset_postdata();
    } else {
        echo '<p>' . __('No favorites yet. Click the star on any event or artwork to add it to your favorites!', 'artpulse') . '</p>';
    }
    return ob_get_clean();
}
add_shortcode('ap_favorite_portfolio', 'ap_render_favorite_portfolio');

function ap_favorites_analytics_widget() {
    ob_start();
    $args = [
        'post_type' => ['artpulse_event', 'artpulse_artwork'],
        'meta_key' => 'ap_favorite_count',
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'posts_per_page' => 5
    ];
    $query = new WP_Query($args);
    echo '<h4>Top Favorited Events/Artworks</h4><ul class="ap-analytics-widget">';
    while($query->have_posts()) : $query->the_post();
        $trend = get_post_meta(get_the_ID(), 'ap_favorite_trend', true) ?: [];
        $labels = [];
        $counts = [];
        foreach(array_slice(array_reverse(array_keys($trend)),0,7) as $d) {
            $labels[] = $d;
            $counts[] = $trend[$d];
        }
        ?>
        <li>
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            <span><?php echo intval(get_post_meta(get_the_ID(), 'ap_favorite_count', true)); ?> favorites</span>
            <canvas id="favTrendChart-<?php the_ID(); ?>" width="300" height="80"></canvas>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                var ctx = document.getElementById('favTrendChart-<?php the_ID(); ?>').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode($labels); ?>,
                        datasets: [{
                            label: 'Favorites per day',
                            data: <?php echo json_encode($counts); ?>,
                            borderColor: '#f5ab35',
                            backgroundColor: 'rgba(245,171,53,0.1)',
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: {
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { display: true, title: { display: true, text: 'Date' } },
                            y: { beginAtZero: true, title: { display: true, text: 'Favorites' } }
                        }
                    }
                });
            });
            </script>
        </li>
    <?php endwhile;
    echo '</ul>';
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('ap_favorites_analytics', 'ap_favorites_analytics_widget');

function ap_enqueue_event_calendar_assets() {
    if (is_page('events') || is_singular('artpulse_event')) {
        wp_enqueue_style('fullcalendar-css', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css');
        wp_enqueue_script('fullcalendar-js', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.js', [], null, true);
        wp_enqueue_script('ap-event-calendar', plugin_dir_url(__FILE__) . 'assets/js/ap-event-calendar.js', ['fullcalendar-js', 'jquery'], '1.0', true);
        wp_localize_script('ap-event-calendar', 'APCalendar', [
            'events' => ap_get_events_for_calendar(),
        ]);
    }
}
add_action('wp_enqueue_scripts', 'ap_enqueue_event_calendar_assets');

function ap_get_events_for_calendar() {
    $lat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
    $lng = isset($_GET['lng']) ? floatval($_GET['lng']) : null;

    $meta_query = [
        ['key' => 'event_start_date', 'compare' => 'EXISTS'],
    ];

    if ($lat && $lng) {
        $meta_query[] = [
            'key'     => 'event_lat',
            'value'   => [ $lat - 0.5, $lat + 0.5 ],
            'compare' => 'BETWEEN',
            'type'    => 'DECIMAL(10,6)',
        ];
        $meta_query[] = [
            'key'     => 'event_lng',
            'value'   => [ $lng - 0.5, $lng + 0.5 ],
            'compare' => 'BETWEEN',
            'type'    => 'DECIMAL(10,6)',
        ];
    }

    $query = new WP_Query([
        'post_type'      => 'artpulse_event',
        'post_status'    => 'publish',
        'posts_per_page' => 100,
        'meta_query'     => $meta_query,
    ]);
    $user_id   = get_current_user_id();
    $favorited = $user_id ? (array) get_user_meta($user_id, 'ap_favorite_events', true) : [];
    $rsvpd     = $user_id ? (array) get_user_meta($user_id, 'ap_rsvp_events', true) : [];

    $events = [];
    while ($query->have_posts()) {
        $query->the_post();
        $event_id = get_the_ID();
        $start    = get_post_meta($event_id, 'event_start_date', true);
        $end      = get_post_meta($event_id, 'event_end_date', true);
        $venue    = get_post_meta($event_id, 'venue_name', true);
        $address  = get_post_meta($event_id, 'event_street_address', true);

        $is_fav  = in_array($event_id, $favorited, true);
        $is_rsvp = in_array($event_id, $rsvpd, true);

        $class = [];
        if ($is_fav) {
            $class[] = 'event-favorited';
        }
        if ($is_rsvp) {
            $class[] = 'event-rsvpd';
        }

        $events[] = [
            'id'    => $event_id,
            'title' => get_the_title(),
            'start' => $start,
            'end'   => $end,
            'url'   => get_permalink(),
            'classNames' => $class,
            'extendedProps' => [
                'venue'     => $venue,
                'address'   => $address,
                'favorited' => $is_fav,
                'rsvpd'     => $is_rsvp,
            ],
        ];
    }
    wp_reset_postdata();
    return $events;
}

function ap_get_event_card(int $event_id): string {
    $path = locate_template('templates/event-card.php');
    if (!$path) {
        $path = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'templates/event-card.php';
    }
    if (!file_exists($path)) {
        return '';
    }
    ob_start();
    include $path;
    return ob_get_clean();
}

function ap_get_collection_card(int $collection_id): string {
    $path = locate_template('templates/collection-card.php');
    if (!$path) {
        $path = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'templates/collection-card.php';
    }
    if (!file_exists($path)) {
        return '';
    }
    ob_start();
    include $path;
    return ob_get_clean();
}

function ap_get_events_for_map() {
    $query = new WP_Query([
        'post_type'      => 'artpulse_event',
        'post_status'    => 'publish',
        'posts_per_page' => 100,
        'meta_query'     => [
            ['key' => 'event_lat', 'compare' => 'EXISTS'],
            ['key' => 'event_lng', 'compare' => 'EXISTS'],
        ],
    ]);
    $events = [];
    while ($query->have_posts()) {
        $query->the_post();
        $lat = get_post_meta(get_the_ID(), 'event_lat', true);
        $lng = get_post_meta(get_the_ID(), 'event_lng', true);
        if ($lat === '' || $lng === '') {
            continue;
        }
        $events[] = [
            'id'    => get_the_ID(),
            'title' => get_the_title(),
            'lat'   => (float) $lat,
            'lng'   => (float) $lng,
            'url'   => get_permalink(),
        ];
    }
    wp_reset_postdata();
    return $events;
}

// === UI Toggle Demo ===
require_once plugin_dir_path(__FILE__) . 'includes/helpers.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/post-status-hooks.php';

add_action('wp_enqueue_scripts', function () {
    $ui_mode = ap_get_ui_mode();

    if ($ui_mode === 'react') {
        wp_enqueue_script('ap-react', plugin_dir_url(__FILE__) . 'assets/dist/react-app.js', [], null, true);
    }
});

add_shortcode('ap_render_ui', function () {
    ob_start();
    $ui_mode = ap_get_ui_mode();
    $template = $ui_mode === 'react' ? 'form-react.php' : 'form-tailwind.php';
    include plugin_dir_path(__FILE__) . "templates/{$template}";
    return ob_get_clean();
});

add_action('rest_api_init', function() {
    register_rest_route('artpulse/v1', '/event/(?P<id>\d+)/attendees', [
        'methods'  => 'GET',
        'callback' => function($request) {
            $event_id = $request['id'];
            // Replace this with your actual attendee query logic:
            if (!get_post($event_id)) {
                return new WP_Error('event_not_found', 'Event not found', array('status' => 404));
            }
            // Example: get attendees from meta, or whatever storage
            $attendees = get_post_meta($event_id, '_attendees', true);
            $attendees = is_array($attendees) ? $attendees : [];
            return rest_ensure_response($attendees);
        },
        'permission_callback' => '__return_true'
    ]);
});


add_action('wp_footer', function () {
    if (!current_user_can('manage_options')) {
        return;
    }
    echo '<div style="padding:1em;"><strong>UI Mode:</strong>
        <a href="?ui_mode=tailwind">Tailwind</a> |
        <a href="?ui_mode=react">React</a></div>';
});

// Expose event comments via REST
add_action('rest_api_init', function () {
    register_rest_route('artpulse/v1', '/event/(?P<id>\\d+)/comments', [
        'methods'  => 'GET',
        'callback' => function ($request) {
            $event_id = $request['id'];
            $args     = [
                'post_id' => $event_id,
                'status'  => 'approve',
            ];
            $comments = get_comments($args);

            $data = array_map(function ($c) {
                return [
                    'id'      => $c->comment_ID,
                    'author'  => $c->comment_author,
                    'content' => $c->comment_content,
                    'date'    => $c->comment_date,
                ];
            }, $comments);

            return rest_ensure_response($data);
        },
        'permission_callback' => '__return_true',
    ]);
});

// Force plugin template for single artpulse_event posts
add_filter('template_include', function ($template) {
    if (is_singular('artpulse_event')) {
        $custom_template = plugin_dir_path(__FILE__) . 'templates/salient/single-artpulse_event.php';
        if (file_exists($custom_template)) {
            error_log('✅ Plugin forcing use of single-artpulse_event.php');
            return $custom_template;
        }
    }
    return $template;
}, 999);


