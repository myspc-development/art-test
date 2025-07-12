<?php
use ArtPulse\Admin\DashboardWidgetTools;

add_action('wp_enqueue_scripts', function () {
    if (is_page('dashboard-member')) {
        wp_enqueue_script('sortablejs', plugin_dir_url(__FILE__) . '../assets/js/Sortable.min.js', [], '1.14', true);
        wp_enqueue_script('member-dashboard-js', plugin_dir_url(__FILE__) . '../assets/js/member-dashboard.js', ['sortablejs'], null, true);
        wp_enqueue_style('dashboard-style', plugin_dir_url(__FILE__) . '../assets/css/dashboard-widget.css');
    }
});

if (!ap_user_can_edit_layout('member')) {
    wp_die('Access denied');
}

get_header();

$dashboard_class = 'member-dashboard';
$dashboard_title = '📋 ' . __('Member Dashboard', 'artpulse');

include __DIR__ . '/partials/dashboard-wrapper-start.php';
include __DIR__ . '/partials/dashboard-nav.php';

$stats  = [];
$events = [];
include __DIR__ . '/partials/dashboard-stats.php';
include __DIR__ . '/partials/dashboard-events.php';
?>
<div id="ap-user-dashboard" class="ap-dashboard-columns">
  <?php DashboardWidgetTools::render_user_dashboard(get_current_user_id()); ?>
</div>
<?php
include __DIR__ . '/partials/dashboard-wrapper-end.php';
get_footer();
?>
