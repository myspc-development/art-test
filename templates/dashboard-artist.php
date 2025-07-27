<?php
$user_role = 'artist';

ap_safe_include('templates/partials/dashboard-nav.php', plugin_dir_path(__FILE__) . 'partials/dashboard-nav.php');
ap_safe_include('templates/partials/dashboard-role-tabs.php', plugin_dir_path(__FILE__) . 'partials/dashboard-role-tabs.php');

add_action('wp_enqueue_scripts', function () use ($user_role) {
    if (ap_user_can_edit_layout($user_role)) {
        wp_enqueue_script('sortablejs', plugin_dir_url(__FILE__) . '../assets/libs/sortablejs/Sortable.min.js', [], '1.15.0', true);
        wp_enqueue_script('user-dashboard-layout', plugin_dir_url(__FILE__) . './assets/js/user-dashboard-layout.js', ['sortablejs'], null, true);
        wp_localize_script('user-dashboard-layout', 'APLayout', [
            'nonce'    => wp_create_nonce('ap_save_user_layout'),
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
        wp_enqueue_script('dark-mode-toggle', plugin_dir_url(__FILE__) . '../assets/js/dark-mode-toggle.js', [], null, true);
    }
});
?>
<div class="ap-dashboard-wrap <?= esc_attr($user_role) ?>-dashboard ap-role-layout" data-role="artist">
<?php
$role  = \ArtPulse\Core\DashboardController::get_role(get_current_user_id());
$title = match ($role) {
    'member'       => '🎨 Welcome to Your ArtPulse Dashboard',
    'artist'       => '🎭 Artist Studio Dashboard',
    'organization' => '🏛️ Organization Control Panel',
    default        => '📊 Dashboard',
};
?>
  <div class="ap-dashboard-title">
    <h1><?= esc_html($title); ?></h1>
  </div>
  <?php
  $user_id = get_current_user_id();
  $layout = \ArtPulse\Core\DashboardController::get_user_dashboard_layout($user_id);

  ap_safe_include(
      'templates/partials/dashboard-generic.php',
      plugin_dir_path(__FILE__) . 'partials/dashboard-generic.php',
      ['layout' => $layout]
  );
  ?>
  <div class="ap-quickstart-wrapper">
    <?php
    ap_safe_include('templates/partials/quickstart-artist-guide.php', plugin_dir_path(__FILE__) . 'partials/quickstart-artist-guide.php');
    ?>
  </div>
</div>
