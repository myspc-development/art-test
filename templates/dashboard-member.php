<?php
use ArtPulse\Admin\DashboardWidgetTools;

get_header();
?>

<div class="ap-dashboard-wrap member-dashboard">
  <h2>📋 Member Dashboard</h2>
  <form method="post" style="margin-bottom:1em;">
    <?php wp_nonce_field('ap_reset_user_layout'); ?>
    <input type="hidden" name="reset_user_layout" value="1" />
    <button class="button">♻ Reset My Dashboard</button>
  </form>
  <div class="ap-dashboard-columns">
    <?php DashboardWidgetTools::render_user_dashboard(get_current_user_id()); ?>
  </div>
</div>

<?php get_footer(); ?>
