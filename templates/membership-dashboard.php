<?php
$user_role = 'member';
$template = locate_template('templates/partials/dashboard-nav.php');
if (!$template) {
    $template = plugin_dir_path(__FILE__) . 'partials/dashboard-nav.php';
}
if ($template && file_exists($template)) {
    include $template;
} else {
    error_log('ArtPulse: Missing template → dashboard-nav.php');
}
get_header();
?>
<div class="ap-dashboard-wrap <?php echo esc_attr($user_role); ?>-dashboard">
  <h2><?php echo esc_html(ucfirst($user_role)); ?> Dashboard</h2>
  <?php
  $template = locate_template('templates/partials/dashboard-generic.php');
  if (!$template) {
      $template = plugin_dir_path(__FILE__) . 'partials/dashboard-generic.php';
  }
  if ($template && file_exists($template)) {
      include $template;
  } else {
      error_log('ArtPulse: Missing template → dashboard-generic.php');
  }
  ?>
</div>
<?php get_footer(); ?>
