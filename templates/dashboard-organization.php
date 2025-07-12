<?php
use ArtPulse\Admin\DashboardWidgetTools;

if (!ap_user_can_edit_layout('organization')) {
    wp_die('Access denied');
}

get_header();

$dashboard_class = 'organization-dashboard';
$dashboard_title = '🏢 ' . __('Organization Dashboard', 'artpulse');

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
