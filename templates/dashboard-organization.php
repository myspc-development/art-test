<?php
use ArtPulse\Admin\DashboardWidgetTools;

get_header();
?>

<div class="ap-dashboard-wrap organization-dashboard">
  <h2>🏢 Organization Dashboard</h2>
  <div class="ap-dashboard-columns">
    <?php DashboardWidgetTools::render_user_dashboard(get_current_user_id()); ?>
  </div>
</div>

<?php get_footer(); ?>
