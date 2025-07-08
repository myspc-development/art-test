<?php
use ArtPulse\Admin\DashboardWidgetTools;

get_header();
?>

<div class="ap-dashboard-wrap member-dashboard">
  <h2>📋 Member Dashboard</h2>
  <?php DashboardWidgetTools::render_user_dashboard(get_current_user_id()); ?>
</div>

<?php get_footer(); ?>
