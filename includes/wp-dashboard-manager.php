<?php
/**
 * Register custom dashboard widgets using WordPress's meta box system.
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register three sample widgets on the main dashboard screen.
 */
function artpulse_add_wp_widget(string $id, string $title, callable $cb): void {
    static $ids    = [];
    static $labels = [];

    $id = sanitize_key($id);
    if (!$id) {
        return;
    }

    global $wp_meta_boxes;
    $exists = false;
    if (isset($wp_meta_boxes['dashboard'])) {
        foreach ($wp_meta_boxes['dashboard'] as $ctx) {
            foreach ($ctx as $priority) {
                if (isset($priority[$id])) {
                    $exists = true;
                    break 2;
                }
            }
        }
    }

    if ($exists || in_array($id, $ids, true)) {
        trigger_error('Duplicate or invalid dashboard widget ID: ' . $id, E_USER_WARNING);
        return;
    }

    // Allow basic HTML in the widget title so icons can be included.
    $title = trim(wp_kses_post($title));
    if ($title === '' || in_array($title, $labels, true)) {
        trigger_error('Dashboard widget missing or duplicate title for ID: ' . $id, E_USER_WARNING);
        return;
    }

    wp_add_dashboard_widget($id, $title, $cb);
    $ids[]    = $id;
    $labels[] = $title;
}

function artpulse_wp_register_widgets() {
    artpulse_add_wp_widget(
        'artpulse_widget_overview',
        artpulse_dashicon('admin-home', ['style' => 'margin-right:6px;']) . __( 'Site Overview', 'artpulse' ),
        'artpulse_widget_overview_render'
    );

    artpulse_add_wp_widget(
        'artpulse_widget_events',
        artpulse_dashicon('calendar-alt', ['style' => 'margin-right:6px;']) . __( 'Upcoming Events', 'artpulse' ),
        'artpulse_widget_events_render'
    );

    artpulse_add_wp_widget(
        'artpulse_widget_tags',
        artpulse_dashicon('tag', ['style' => 'margin-right:6px;']) . __( 'Trending Tags', 'artpulse' ),
        'artpulse_widget_tags_render'
    );

    // Move the trending tags widget to the sidebar column by default.
    global $wp_meta_boxes;
    if (isset($wp_meta_boxes['dashboard']['normal']['core']['artpulse_widget_tags'])) {
        $widget = $wp_meta_boxes['dashboard']['normal']['core']['artpulse_widget_tags'];
        unset($wp_meta_boxes['dashboard']['normal']['core']['artpulse_widget_tags']);
        $wp_meta_boxes['dashboard']['side']['core']['artpulse_widget_tags'] = $widget;
    }
}
add_action('wp_dashboard_setup', 'artpulse_wp_register_widgets');

function artpulse_widget_overview_render() {
    echo '<p>' . esc_html__( 'Welcome to ArtPulse! Here is a quick overview of your site activity.', 'artpulse' ) . '</p>';
    echo '<ul><li>' . esc_html__( '0 new comments', 'artpulse' ) . '</li><li>' . esc_html__( '0 new likes', 'artpulse' ) . '</li></ul>';
}

function artpulse_widget_events_render() {
    echo '<p>' . esc_html__( 'No upcoming events scheduled.', 'artpulse' ) . '</p>';
    echo '<p><a href="#" class="button">' . esc_html__( 'Create Event', 'artpulse' ) . '</a></p>';
}

function artpulse_widget_tags_render() {
    global $wpdb;

    $cutoff = gmdate('Y-m-d H:i:s', strtotime('-30 days', current_time('timestamp')));
    $sql    = $wpdb->prepare(
        "SELECT t.term_id, t.name, COUNT(tr.object_id) as cnt
         FROM {$wpdb->terms} t
         JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
         JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
         JOIN {$wpdb->posts} p ON tr.object_id = p.ID
         WHERE tt.taxonomy = %s
           AND p.post_status = 'publish'
           AND p.post_date >= %s
         GROUP BY t.term_id
         ORDER BY cnt DESC
         LIMIT 5",
        'post_tag',
        $cutoff
    );

    $rows = $wpdb->get_results($sql);

    if (!$rows) {
        echo '<p>' . esc_html__( 'No trending tags found.', 'artpulse' ) . '</p>';
        return;
    }

    echo '<ul>';
    foreach ($rows as $tag) {
        $link = get_tag_link($tag->term_id);
        echo '<li><a href="' . esc_url($link) . '">#' . esc_html($tag->name) . '</a></li>';
    }
    echo '</ul>';
}

/**
 * Enqueue simple layout CSS for the dashboard widgets.
 */
function artpulse_wp_dashboard_styles($hook) {
    if ($hook !== 'index.php') {
        return;
    }
    wp_enqueue_style(
        'artpulse-wp-dashboard',
        plugins_url('../assets/css/wp-dashboard-layout.css', __FILE__),
        [],
        '1.0'
    );
}
add_action('admin_enqueue_scripts', 'artpulse_wp_dashboard_styles');
