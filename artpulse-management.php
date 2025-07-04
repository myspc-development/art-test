<?php
/**
 * Plugin Name:     ArtPulse Management
 * Description:     Management plugin for ArtPulse.
 * Version:         1.3.9
 * Author:          craig
 * Text Domain:     artpulse
 * License:         GPL2
 */

use ArtPulse\Core\Plugin;
use ArtPulse\Core\WooCommerceIntegration;
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

// 🔧 Boot the main plugin class (responsible for registering menus, settings, CPTs, etc.)
$main = new Plugin();
// Instantiate WooCommerce integration (if needed for runtime)
$plugin = new WooCommerceIntegration();

// ✅ Hook for activation
register_activation_hook(__FILE__, function () {
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    artpulse_create_custom_table();
    \ArtPulse\Core\FeedbackManager::install_table();
    Activator::activate(); // WooCommerceIntegration has no activate() method
});



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

    $src_files = [
        plugin_dir_path(__FILE__) . 'templates/salient/content‐artpulse_event.php',
        plugin_dir_path(__FILE__) . 'templates/salient/archive-artpulse_event.php',
    ];
    $dest_dir = trailingslashit(get_stylesheet_directory()) . 'templates/salient';
    wp_mkdir_p($dest_dir);

    foreach ($src_files as $src) {
        copy($src, $dest_dir . '/' . basename($src));
    }

    add_action('admin_notices', function () {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Templates copied to child theme.', 'artpulse') . '</p></div>';
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

function ap_enqueue_dashboard_styles() {
    wp_enqueue_style(
        'ap-complete-dashboard-style',
        plugin_dir_url(__FILE__) . 'assets/css/ap-complete-dashboard-frontend.css',
        [],
        filemtime(plugin_dir_path(__FILE__) . 'assets/css/ap-complete-dashboard-frontend.css')
    );
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

// Enqueue the full SortableJS library on dashboard pages.
add_action('wp_enqueue_scripts', function () {
    if (is_page('dashboard') || is_page('organization-dashboard')) {
        wp_enqueue_script(
            'sortablejs',
            'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js',
            [],
            '1.15.0',
            true
        );
    }
});

add_action('wp_ajax_ap_toggle_favorite', function() {
    if (!is_user_logged_in() || !isset($_POST['post_id'])) {
        wp_send_json_error(['error' => 'Unauthorized']);
    }

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ap_toggle_favorite_nonce')) {
        wp_send_json_error(['error' => 'Invalid nonce']);
    }

    $user_id = get_current_user_id();
    $post_id = intval($_POST['post_id']);
    $type = get_post_type($post_id);
    $meta_key = ($type === 'artpulse_event') ? 'ap_favorite_events' : 'ap_favorite_artworks';
    $favs = get_user_meta($user_id, $meta_key, true) ?: [];
    $added = false;

    $trend = get_post_meta($post_id, 'ap_favorite_trend', true) ?: [];
    $today = date('Y-m-d');

    if (in_array($post_id, $favs)) {
        $favs = array_diff($favs, [$post_id]);
        $fav_count = max(0, intval(get_post_meta($post_id, 'ap_favorite_count', true)) - 1);
        $trend[$today] = max(0, ($trend[$today] ?? 1) - 1);
    } else {
        $favs[] = $post_id;
        $added = true;
        $fav_count = intval(get_post_meta($post_id, 'ap_favorite_count', true)) + 1;
        $trend[$today] = ($trend[$today] ?? 0) + 1;
    }

    update_user_meta($user_id, $meta_key, array_values($favs));
    update_post_meta($post_id, 'ap_favorite_count', $fav_count);
    update_post_meta($post_id, 'ap_favorite_trend', $trend);

    wp_send_json_success([
        'added' => $added,
        'count' => $fav_count,
    ]);
});

function ap_user_has_favorited($user_id, $post_id) {
    $meta_key = (get_post_type($post_id) == 'artpulse_event') ? 'ap_favorite_events' : 'ap_favorite_artworks';
    $favs = get_user_meta($user_id, $meta_key, true) ?: [];
    return in_array($post_id, $favs);
}

function ap_render_favorite_portfolio() {
    if (!is_user_logged_in()) {
        return '<p>' . __('Please log in to view your favorites.', 'artpulse') . '</p>';
    }
    $user_id = get_current_user_id();
    $fav_events = get_user_meta($user_id, 'ap_favorite_events', true) ?: [];
    $fav_artworks = get_user_meta($user_id, 'ap_favorite_artworks', true) ?: [];
    $favorite_ids = array_merge($fav_events, $fav_artworks);

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
                        <?php echo \ArtPulse\Frontend\ap_render_favorite_button( get_the_ID() ); ?>
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
    $query = new WP_Query([
        'post_type'      => 'artpulse_event',
        'post_status'    => 'publish',
        'posts_per_page' => 100,
        'meta_query'     => [
            ['key' => 'event_start_date', 'compare' => 'EXISTS'],
        ],
    ]);
    $events = [];
    while ($query->have_posts()) {
        $query->the_post();
        $start = get_post_meta(get_the_ID(), 'event_start_date', true);
        $end   = get_post_meta(get_the_ID(), 'event_end_date', true);
        $venue = get_post_meta(get_the_ID(), 'venue_name', true);
        $address = get_post_meta(get_the_ID(), 'event_street_address', true);
        $events[] = [
            'id'    => get_the_ID(),
            'title' => get_the_title(),
            'start' => $start,
            'end'   => $end,
            'url'   => get_permalink(),
            'extendedProps' => [
                'venue'   => $venue,
                'address' => $address,
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
        $custom_template = plugin_dir_path(__FILE__) . 'templates/single-artpulse_event.php';
        if (file_exists($custom_template)) {
            error_log('✅ Plugin forcing use of single-artpulse_event.php');
            return $custom_template;
        }
    }
    return $template;
}, 999);


