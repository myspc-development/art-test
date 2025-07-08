<?php
use ArtPulse\Admin\DashboardWidgetTools;

if (!current_user_can('artist') && !current_user_can('manage_options')) {
    wp_die('Access denied');
}

get_header();
?>

<div class="ap-dashboard-wrap artist-dashboard">
  <h2>🎨 Artist Dashboard</h2>
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
