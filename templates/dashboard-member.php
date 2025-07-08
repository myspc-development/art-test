<?php
use ArtPulse\Admin\DashboardWidgetTools;

add_action('wp_enqueue_scripts', function () {
    if (is_page('dashboard-member')) {
        wp_enqueue_script('sortablejs', plugin_dir_url(__FILE__) . '../assets/js/Sortable.min.js', [], '1.14', true);
        wp_enqueue_script('member-dashboard-js', plugin_dir_url(__FILE__) . '../assets/js/member-dashboard.js', ['sortablejs'], null, true);
        wp_enqueue_style('dashboard-style', plugin_dir_url(__FILE__) . '../assets/css/dashboard-widget.css');
    }
});

if (!current_user_can('member') && !current_user_can('manage_options')) {
    wp_die('Access denied');
}

get_header();
?>

<div class="ap-dashboard-wrap member-dashboard">
  <h2>📋 Member Dashboard</h2>
  <form method="post" style="margin-bottom:1em;">
    <?php wp_nonce_field('ap_reset_user_layout'); ?>
    <input type="hidden" name="reset_user_layout" value="1" />
    <button class="button">♻ Reset My Dashboard</button>
  </form>
  <div id="ap-user-dashboard" class="ap-dashboard-columns">
    <?php DashboardWidgetTools::render_user_dashboard(get_current_user_id()); ?>
  </div>
</div>

<?php get_footer(); ?>
