<?php
$user_role = 'organization';

$template = locate_template('templates/partials/dashboard-nav.php');
if (!$template) {
    $template = plugin_dir_path(__FILE__) . 'partials/dashboard-nav.php';
}
if ($template && file_exists($template)) {
    include $template;
} else {
    error_log('ArtPulse: Missing template → dashboard-nav.php');
}

add_action('wp_enqueue_scripts', function () use ($user_role) {
    if (ap_user_can_edit_layout($user_role)) {
        wp_enqueue_script("{$user_role}-dashboard-js", plugin_dir_url(__FILE__) . "../assets/js/{$user_role}-dashboard.js", ['jquery-ui-sortable', 'dark-mode-toggle'], null, true);
        wp_localize_script("{$user_role}-dashboard-js", 'apDashboard', [
            'nonce'    => wp_create_nonce('ap_dashboard_nonce'),
        ]);
        wp_enqueue_script('dark-mode-toggle', plugin_dir_url(__FILE__) . '../assets/js/dark-mode-toggle.js', [], null, true);
        wp_enqueue_style('dashboard-style', plugin_dir_url(__FILE__) . '../assets/css/dashboard-widget.css');
    }
});

?>
<div class="ap-dashboard-wrap <?= esc_attr($user_role) ?>-dashboard">
  <h2><?= ucfirst($user_role) ?> Dashboard</h2>
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
  <div class="ap-quickstart-wrapper">
    <?php
    $template = locate_template('templates/partials/quickstart-admin-guide.php');
    if (!$template) {
        $template = plugin_dir_path(__FILE__) . 'partials/quickstart-admin-guide.php';
    }
    if ($template && file_exists($template)) {
        include $template;
    } else {
        error_log('ArtPulse: Missing template → quickstart-admin-guide.php');
    }
    ?>
  </div>
</div>


