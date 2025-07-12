<?php
$user_role = 'member';
include locate_template('templates/partials/dashboard-nav.php');
get_header();
?>
<div class="ap-dashboard-wrap <?php echo esc_attr($user_role); ?>-dashboard">
  <h2><?php echo esc_html(ucfirst($user_role)); ?> Dashboard</h2>
  <?php include locate_template('templates/partials/dashboard-generic.php'); ?>
</div>
<?php get_footer(); ?>
