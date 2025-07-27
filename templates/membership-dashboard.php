<?php
$user_role = 'member';
ap_safe_include('templates/partials/dashboard-nav.php', plugin_dir_path(__FILE__) . 'partials/dashboard-nav.php');
get_header();
?>
<div class="ap-dashboard-wrap <?php echo esc_attr($user_role); ?>-dashboard">
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
  ap_safe_include('templates/partials/dashboard-generic.php', plugin_dir_path(__FILE__) . 'partials/dashboard-generic.php');
  ?>
</div>
<?php get_footer(); ?>
