<?php
$user_role = 'member';
ap_safe_include('templates/partials/dashboard-nav.php', plugin_dir_path(__FILE__) . 'partials/dashboard-nav.php');
get_header();
?>
<div class="ap-dashboard-wrap <?php echo esc_attr($user_role); ?>-dashboard">
  <h2 class="ap-card__title"><?php echo esc_html(ucfirst($user_role)); ?> Dashboard</h2>
  <?php
  ap_safe_include('templates/partials/dashboard-generic.php', plugin_dir_path(__FILE__) . 'partials/dashboard-generic.php');
  ?>
</div>
<?php get_footer(); ?>
